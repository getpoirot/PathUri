<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iPathUri;
use Poirot\PathUri\Interfaces\iPathJoinedUri;

/**
 * note: string paths usually must be normalized from
 *       the class that used this
 */
class PathJoinUri extends AbstractPathUri
    implements iPathJoinedUri
{
    // From SetterTrait ... {
    //   used on fromArray, it will first set Separator

    /**
     * @var array List Setters By Priority
     * [
     *  'service_config',
     *  'listeners',
     *  // ...
     * ]
     *
     * application calls setter methods from top ...
     *
     */
    protected $__setup_array_priority = [
        'separator',
    ];
    // ... }

    /**
     * Empty Array Path Means We Have No Path
     *
     * @var array
     */
    protected $_path = [];

    protected $separator = '/';

    /**
     * Build Object From String
     *
     * - parse string to associateArray setter
     * - return value of this method must can be
     *   used as an argument for fromArray
     *
     * @param string $pathStr
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    function parse($pathStr)
    {
        if (!is_string($pathStr))
            throw new \InvalidArgumentException(sprintf(
                'PathStr must be string but "%s" given.'
                , is_object($pathStr) ? get_class($pathStr) : gettype($pathStr)
            ));

        $DS = $this->getSeparator();

        // remove last trailing "/"
        $pathStr = Util::normalizeUnixPath($pathStr, $DS);
        if ($pathStr === $this->getSeparator())
            // in case of "/", explode create unwanted ['', '']
            // so i`ve check path string for decision
            $path = [ $DS, ];
        elseif ($pathStr === '')
            // Current Directory
            $path = [];
        else {
            $path = $this->__normalize(explode($DS, $pathStr));
            if (isset($path[0]) && $path[0] == '')
                // explode affect on absolute addresses
                // start with separator. exp. "/var/www/"
                $path[0] = $DS;
        }

        return [
            'path'      => $path,
            'separator' => $DS,
        ];
    }

    /**
     * Set Path
     *
     * !! Helper For SetterBuilder
     *
     */
    protected function setPath($arrPath)
    {
        if (is_string($arrPath))
            $arrPath = $this->parse($arrPath)['path'];

        if ($arrPath === null)
            $arrPath = [];

        if (!is_array($arrPath))
            throw new \InvalidArgumentException(sprintf(
                'Path must be a string, null, or array, but given "%s".'
                , is_object($arrPath) ? get_class($arrPath) : gettype($arrPath)
            ));

        // the associate array is useless
        $this->_path = array_values($arrPath);
    }

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        $p = $this->_path;

        reset($p);
        $fi = current($p);

        return $fi === $this->getSeparator() || substr($fi, -1) === ':';
    }

    /**
     * Get Array In Form Of AssocArray
     *
     * return [
     *  'path'      => ['', 'absolute', 'path'],
     *  'separator' => '/',
     * ]
     *
     * @return array
     */
    function toArray()
    {
        return [
            'path'      => $this->_path,
            'separator' => $this->getSeparator()
        ];
    }

    /**
     * Get Assembled Path As String
     *
     * - the path must normalized before output
     *
     * @return string
     */
    function toString()
    {
        $path   = $this->_path;
        if ($path == [])
            return '';

        $DS = $this->getSeparator();

        if ($path == [$DS])
            // its home, implode not working for on element
            return $DS;

        // add empty slashes after all
        // that implode work properly for
        // paths with one member
        // its removed by normalize at last
        $path[] = '';
        $return = implode( $this->getSeparator(), $this->_path );

        return Util::normalizeUnixPath($return, $this->getSeparator());
    }

    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setSeparator($sep)
    {
        $this->separator = (string) $sep;

        return $this;
    }

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        $paths = $this->__normalize($this->_path);
        $this->setPath($paths);

        return $this;
    }

    /**
     * Normalize Array Path
     *
     * @param array $paths
     *
     * @return array
     */
    protected function __normalize(array $paths)
    {
        if ($paths == [])
            return $paths;

        // Cleanup empty directories ".", "//":
        reset($paths); $i = 0; $indexes = [];
        while(($val = current($paths)) !== false) {
            if (($val == $this->getSeparator() || $val === '' || $val === '.') && $i > 0)
                $indexes[] = key($paths);

            $i++;
            next($paths);
        }

        foreach($indexes as $i)
            unset($paths[$i]);


        // Normalize go up to parent "..":
        reset($paths); $prevIndex = null;
        while(in_array('..', $paths, true))
        {
            $currIndex = key($paths);
            $currItem  = current($paths);

            if ($currItem == '..')
            {
                if ($prevIndex !== null
                    // we don't want to go back further than home
                    && $paths[$prevIndex] !== ''
                ) {
                    unset($paths[$prevIndex]);
                }

                unset($paths[$currIndex]);

                $prevIndex = null;
                reset($paths);
                continue;
            }

            $prevIndex = $currIndex;
            next($paths);
        }

        return $paths;
    }

    /**
     * Append Path
     *
     * - manipulate current path
     *
     * @param iPathUri $pathUri
     *
     * @return $this
     */
    function append($pathUri)
    {
        /** @var iPathUri $pathUri */
        $appendPath = $pathUri->toArray()['path'];
        $appendPath = array_filter($appendPath, function($p) {
            // Remove all ['',] from path
            // on appended path we don't want any absolute sign in
            // array list
            return $p !== $this->getSeparator();
        });

        $finalPath = array_merge($this->_path, $appendPath);

        $this->setPath($finalPath);

        return $this;
    }

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iPathUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri)
    {
        /** @var iPathUri $pathUri */
        $finalPath = array_merge($pathUri->toArray()['path'], $this->_path);
        $this->setPath($finalPath);

        return $this;
    }

    /**
     * Mask Given PathUri with Current Path
     *
     * toggle:
     * /var/www/html <=> /var/www/     ===> /html
     *
     * toggle false:
     * /var/www/     <=> /var/www/html ===> ''
     *
     * - manipulate current path
     *
     * @param iPathJoinedUri $pathUri
     * @param bool           $toggle  with toggle always bigger path
     *                                compared to little one
     *
     * @return $this
     */
    function mask($pathUri, $toggle = true)
    {
        $muchLength = $this->_path;
        $less       = $pathUri->toArray()['path'];

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less and $less = $this->_path ) : null;
            ;

        $masked = $muchLength;
        foreach($muchLength as $i => $v) {
            if (!isset($less[$i]) || $v != $less[$i])
                break;

            unset($masked[$i]);
        }

        $this->setPath($masked);

        return $this;
    }

    /**
     * Joint Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /html
     *
     * - manipulate current path
     *
     * @param iPathJoinedUri $pathUri
     *
     * @param bool $toggle
     * @return $this
     */
    function joint($pathUri, $toggle = true)
    {
        $muchLength = $this->_path;
        $less       = $pathUri->toArray()['path'];

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less and $less = $this->_path ) : null;
        ;

        $similar = []; // empty path
        foreach($muchLength as $i => $v) {
            if (!array_key_exists($i, $less) || $v != $less[$i])
                break;

            $similar[] = $v;
        }

        $this->setPath($similar);

        return $this;
    }

    /**
     * Get Uri Depth
     *
     * note: in case of /var/www/html
     *       0:/, 1:var, 2:www ...
     *       depth is 3
     *
     * @return int
     */
    function getDepth()
    {
        return count($this->_path);
    }

    /**
     * Split Path And Update Object To New Path
     *
     * /var/www/html
     * split(-1) => "/var/www"
     * split(0)  => "/..."
     * split(1)  => "var/www/html"
     *
     * @param int      $start start index position
     * @param null|int $end   end index position
     *
     * @return $this
     */
    function split($start, $end = null)
    {
        $ln = ($end === null) ? count($this->_path)
            : $end - $start;
        $path = array_slice($this->_path, $start, $ln);
        $this->_path = $path;

        return $this;
    }
}
