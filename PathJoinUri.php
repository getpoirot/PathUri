<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iPathAbstractUri;
use Poirot\PathUri\Interfaces\iPathJoinedUri;

/**
 * note: string paths usually must be normalized from
 *       the class that used this
 */
class PathJoinUri extends PathAbstractUri
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
    protected $path = [];

    protected $separator = '/';

    /**
     * Set Path
     *
     * - Null Or Empty Array Means We Have No Path
     *
     * note: in case of string path using separator
     *       to explode and build an array
     *
     * @param array|string|null $arrPath
     *
     * @return $this
     */
    function setPath($arrPath)
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
        $this->path = array_values($arrPath);

        return $this;
    }

    /**
     * Get Path
     *
     * @return array
     */
    function getPath()
    {
        return $this->path;
    }

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

        // remove last trailing "/"
        $pathStr = Util::normalizeUnixPath($pathStr, $this->getSeparator());
        if ($pathStr === $this->getSeparator())
            // in case of "/", explode create unwanted ['', '']
            $path = [''];
        elseif ($pathStr === '')
            $path = [];
        else
            $path = explode($this->getSeparator(), $pathStr);

        return [
            'path'      => $path,
            'separator' => $this->getSeparator(),
        ];
    }

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        $p = $this->getPath();

        reset($p);
        $fi = current($p);

        return $fi === '' || substr($fi, -1) === ':';
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
            'path'      => $this->getPath(),
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
        $return = implode( $this->getSeparator(), $this->getPath() );

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
        $paths = $this->getPath();
        if (!$paths)
            return $this;

        /*$paths = array_filter($paths, function($p) {
            if (strpos($p, ':') !== false)
                throw new \InvalidArgumentException('Invalid path character ":"');

            return $p !== '' && $p !== '.';
        });*/

        reset($paths); $prevIndex = null;
        while(in_array('..', $paths, true))
        {
            $currIndex = key($paths);
            $currItem  = current($paths);

            if ($currItem == '..') {
                if ($prevIndex !== null) {
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

        $this->setPath($paths);

        return $this;
    }

    /**
     * Append Path
     *
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function append($pathUri)
    {
        /** @var iPathAbstractUri $pathUri */
        $appendPath = $pathUri->getPath();
        $appendPath = array_filter($appendPath, function($p) {
            // Remove all ['', ..] from path
            // on appended path we don't want any absolute sign in
            // array list
            return $p !== '';
        });

        $finalPath = array_merge($this->getPath(), $appendPath);

        $this->setPath($finalPath);

        return $this;
    }

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri)
    {
        /** @var iPathAbstractUri $pathUri */
        $finalPath = array_merge($pathUri->getPath(), $this->getPath());
        $this->setPath($finalPath);

        return $this;
    }

    /**
     * Mask Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /html
     *
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function mask($pathUri)
    {
         (count($pathUri->getPath()) >= count($this->getPath()))
            ? ( $muchLength = $pathUri->getPath() and $less = $this->getPath() )
            : ( $muchLength = $this->getPath()    and $less = $pathUri->getPath() )
         ;

        print(array_intersect($muchLength, $less));

        return $this;
    }
}
 