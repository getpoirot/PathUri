<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iUriSequence;
use Traversable;

/*
 
#*
#* array ( 
#*   'path_sequence' => array ( 
#*     0 => '\\', 
#*     1 => 'var', 
#*     2 => 'eee', 
#*    )
#*   , 'separator' => '\\', 
#* )
#*
$options = UriSequence::parseWith(
    '\\var\\eee'
    , ['separator' => '\\'] # override defaults
);

$uri = new UriSequence($options);


*/

class UriSequence
    extends aUri
    implements iUriSequence
{
    /**
     * @var array
     * Empty Array Path Means We Have No Path
     */
    protected $_pathSequence = array();

    protected $separator = '/';

    /** @var \Closure */
    protected $_encodeMethod;
    protected $_isEncodeEnabled = false;

    
    /**
     * Parse path string to parts in associateArray
     *
     * @param string $stringPath
     *
     * @return mixed
     */
    protected function doParseFromString($stringPath)
    {
        $stringPath = (string) $stringPath;

        $DS = $this->getSeparator();

        // NO Normalization on creating paths
        // we want path same as provided til normalize called!
        // all slashes are replaced by back slashes "/"
        $pathStr = str_replace('\\', '/', $stringPath);
        if ($pathStr === '')
            ## Current Directory
            $path = array();
        else {
            // NO Normalization on creating paths
            $path = explode($DS, $pathStr);

            if (count($path) == 2 && $path[0] === $path[1] && $path[0] == '')
                // explode for "/" single slash
                unset($path[1]);

            if (isset($path[0]) && $path[0] == '')
                // explode affect on absolute addresses
                // start with separator. exp. "/var/www/"
                $path[0] = $DS;
        }

        return array(
            'path'      => $path,
            'separator' => $DS,
        );
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
        $fi     = current($p);
        return $this->_isRoot($fi);
    }


    /**
     * Append Path
     *
     * - manipulate current path
     *
     * @param iUriSequence $appendUri
     *
     * @return $this
     */
    function append(iUriSequence $appendUri)
    {
        $appendPath = $appendUri->getPath();
        /*if ($appendUri->isAbsolute())
            $appendPath = $this->_makeNoneAbsolutePathSequence($appendPath);*/

        $finalPath = array_merge($this->getPath(), $appendPath);
        $this->setPath($finalPath);

        return $this;
    }
    
    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iUriSequence $prependUri
     *
     * @return $this
     */
    function prepend(iUriSequence $prependUri)
    {
        $prependPath = $prependUri->getPath();
        if (empty($prependPath))
            return $this;

        $toPath = $this->getPath();
        if ($this->isAbsolute())
            $toPath = $this->_makeNoneAbsolutePathSequence($toPath);

        $finalPath   = array_merge($prependPath, $toPath);

        $this->setPath($finalPath);
        return $this;
    }

    /**
     * Merge paths
     *
     * .     <=> /bar ----> /bar
     * /foo  <=> /bar ----> /bar
     *
     * 
     * /foo  <=> bar  ----> /bar
     * /foo/ <=> bar  ----> /foo/bar
     * foo   <=> bar  ----> bar
     * foo/  <=> bar  ----> foo/bar
     *
     * @param iUriSequence $mergeUri
     *
     * @return iUriSequence
     */
    function merge(iUriSequence $mergeUri)
    {
        $return = clone $this;

        if ($mergeUri->isAbsolute())
            return $return->setPath($mergeUri->getPath());
        
        // 
        
        $return = $return->mask($mergeUri)
            ->split(0, -1)
            ->append($mergeUri)
        ;

        return $return;
    }

    /**
     * Joint Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /var/www
     *
     * @param iUriSequence $pathUri
     *
     * @return iUriSequence
     */
    function joint(iUriSequence $pathUri)
    {
        $muchLength = $this->getPath();
        $less       = $pathUri->getPath();

        if ( count($less) > count($muchLength) ) {
            $muchLength = $less;
            $less = $this->getPath();
        }

        $similar = array(); // empty path
        foreach($muchLength as $i => $v) {
            if (!array_key_exists($i, $less) || $v != $less[$i])
                break;

            $similar[] = $v;
        }

        $path = clone $this;
        $path->setPath($similar);
        return $path;
    }

    /**
     * Mask Given PathUri with Current Path
     *
     * toggle:
     * /var/www/html <=> /var/www/     ===> html
     * /uri          <=> contact       ===> /uri
     * /uri          <=> /contact      ===> uri
     * /uri/path     <=> /contact      ===> uri/path
     * /uri/         <=> /uri/contact  ===> (empty)
     * /uri/         <=> /uri/contact/ ===> contact/
     *
     * toggle false:
     * /var/www/     <=> /var/www/html ===> ''
     *
     * @param iUriSequence $pathUri
     * @param bool           $toggle  with toggle always bigger path
     *                                compared to little one
     *
     * @return iUriSequence
     */
    function mask(iUriSequence $pathUri, $toggle = true)
    {
        # the absolute path when another is not is always masked on
        #- /foo <=> bar ---> /foo
        if (
            ## one is absolute and another is not
            ($this->isAbsolute() || $pathUri->isAbsolute())
            && !($this->isAbsolute() && $pathUri->isAbsolute())
        ) {
            $uri = clone $this;

            if ($pathUri->isAbsolute())
                ### return absolutes one
                $uri->setPath($pathUri->getPath());

            return $uri;
        }


        // ..

        $muchLength = $this->getPath();
        $less       = $pathUri->getPath();

        if ( $toggle && (count($less) > count($muchLength)) ) {
            $muchLength = $less;
            $less = $this->getPath();
        }

        $masked = $muchLength;
        foreach($muchLength as $i => $v) {
            if (!array_key_exists($i, $less) || $v != $less[$i])
                break;

            unset($masked[$i]);
        }

        $uri = clone $this;
        $uri->setPath($masked);
        return $uri;
    }

    /**
     * Split Path
     *
     * - return new pathUri instance with split
     *
     * /var/www/html
     * split(0)     => "/var/www/html"
     * split(1)     => "var/www/html"
     * split(0, 2)  => "/var"
     * split(0, -1) => "/var/www"
     *
     * @param int      $start
     * @param null|int $length
     *
     * @return iUriSequence
     */
    function split($start, $length = null)
    {
        $return = clone $this;
        $path   = array_slice($this->getPath(), $start, $length);
        $return->setPath($path);

        return $return;
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
        return array(
            'path'      => $this->getPath(),
            'separator' => $this->getSeparator()
        );
    }

    /**
     * Get Assembled Path As String
     *
     * - don`t call normalize path inside this method
     *   normalizing does happen when needed by call
     *   ::normalize
     *
     * @return string
     */
    function toString()
    {
        $path   = $this->getPath();
        if (empty($path))
            return '';

        $DS = $this->getSeparator();

        if ($path == array($DS))
            // its home, implode not working for one element
            return $DS;

        if ($this->isAbsolute() && $path[0] == $this->getSeparator() /* unix style */) {
            // in specific case "//df" implode result in "///df"
            unset($path[0]);
            $return = $this->getSeparator().implode( $this->getSeparator(), $path );
        } else {
            $return = implode( $this->getSeparator(), $path );
        }

        return $return;
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        $normalized = array();
        $paths      = $this->getPath();

        if (empty($paths))
            return $normalized;

        // Replace "~" with user's home directory.
        if ('~' === $paths[0]) {
            $home  = explode($this->getSeparator(), str_replace('\\', '/', $this->_getHomeDir()));
            $paths = $paths + $home;
        }

        $isRoot = $this->_isRoot($paths[0]);
        $paths  = array_filter($paths, function($p, $i) use (&$isRoot, $paths) {
            if ( ($isRoot && $i > 0) || $i+1 == count($paths) /* keep last slash remain */) {
                // keep last slash /go/to/path/
                // keep first slash after root if exists
                //   phar://path/to/res
                $isRoot = false;
                return true;
            }

            // Remove all ['',] from path
            // on appended path we don't want any absolute sign in
            // array list
            return ($p !== '' && $p !== '.');
        }, ARRAY_FILTER_USE_BOTH);

        // Collapse ".." with the previous part, if one exists
        // Don't collapse ".." if the previous part is also ".."
        foreach ($paths as $path) {
            if (   '..' === $path && count($normalized) > 0      // keep first segment
                && '..' !== $normalized[count($normalized) - 1]
            ) {
                if (! $this->_isRoot($normalized[count($normalized) - 1]) )
                    array_pop($normalized);

                continue;
            }

            $normalized[] = $path;
        }

        $this->setPath($normalized);
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
        return count($this->getPath());
    }
    
    
    // Options:

    /**
     * Set Path
     *
     * ! null is to reset object and mean no path
     *
     * @param array|null $path
     *
     * @return $this
     */
    function setPath($path = null)
    {
        if ($path === null)
            $path = array();

        // the associate array is useless
        $this->_pathSequence = array_values( (array) $path);
        return $this;
    }

    /**
     * Get Path
     *
     * ['/', 'var', 'www', 'html']
     *
     * @return array
     */
    function getPath()
    {
        return $this->_pathSequence;
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

    
    // ...
    
    protected function _makeNoneAbsolutePathSequence(array $pathSequence)
    {
        reset($pathSequence);
        if ( $this->_isRoot(current($pathSequence)) )
            unset($pathSequence[0]);

        return $pathSequence;
    }

    /**
     * Get User Home Dir Path
     * @return string 
     * @throws \RuntimeException
     */
    protected function _getHomeDir()
    {
        // For UNIX support
        if (getenv('HOME'))
            return getenv('HOME');

        // For >= Windows8 support
        if (getenv('HOMEDRIVE') && getenv('HOMEPATH'))
            return getenv('HOMEDRIVE').getenv('HOMEPATH');
        
        
        throw new \RuntimeException("Can't Achieve Home Directory On Your Environment.");
    }

    protected function _isRoot($fi)
    {
        $result =
            $fi === $this->getSeparator()
            ## c:[/df]
            || substr($fi, -1) === ':'
        ;

        return $result;
    }

    /**
     * Converts string to lower-case
     * @param string $str
     * @return string 
     */
    protected function _toLower($str)
    {
        if (function_exists('mb_strtolower'))
            return mb_strtolower($str, mb_detect_encoding($str));

        return strtolower($str);
    }
}
