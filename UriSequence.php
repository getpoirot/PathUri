<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iUriSequence;

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
    protected $encodeUri;

    
    /**
     * SeqPathJoinUri constructor.
     * @param array $setter
     */
    function __construct(array $setter = null)
    {
        $this->putBuildPriority(array(
            ## first set separator that is necessary for other process 
            'separator'
        ));
        
        parent::__construct($setter);
    }

    /**
     * Parse path string to parts in associateArray
     *
     * @param string $stringPath
     *
     * @return mixed
     */
    function doParseFromString($stringPath)
    {
        $stringPath = (string) $stringPath;

        $DS = $this->getSeparator();

        // NO Normalization on creating paths
        // we want path same as provided til normalize called!
        // all slashes are replaced by back slashes "/"
        $pathStr = $path = str_replace('\\', '/', $stringPath);
        if ($pathStr === '')
            ## Current Directory
            $path = array();
        else {
            // NO Normalization on creating paths
            $path = explode($DS, $pathStr);
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

        if ($appendUri->isAbsolute())
            $appendPath = $this->_makeNoneAbsolutePathSequence($appendPath);

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
        $toPath = $this->getPath();
        if ($this->isAbsolute())
            $toPath = $this->_makeNoneAbsolutePathSequence($toPath);

        $prependPath = $prependUri->getPath();
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
     * /foo  <=> bar  ----> /bar
     * /foo/ <=> bar  ----> /foo/bar
     *
     * @param iUriSequence $pathUri
     *
     * @return iUriSequence
     */
    function merge(iUriSequence $pathUri)
    {
        $return = clone $this;

        if ($pathUri->isAbsolute()) {
            ## /bar
            $return = $return->joint($pathUri)->append($return->mask($pathUri));
        } else {
            ## bar
            $return = $return->mask($pathUri)
                ->split(0, -1)
                ->append($pathUri)
            ;
        }

        return $return;
    }

    /**
     * Mask Given PathUri with Current Path
     *
     * toggle:
     * /var/www/html <=> /var/www/     ===> /html
     * /uri          <=> contact       ===> /uri
     * /uri          <=> /contact      ===> contact
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
        if (
            ($this->isAbsolute() || $pathUri->isAbsolute())
            && !($this->isAbsolute() && $pathUri->isAbsolute())
        )
            ## the absolute path when another is not is always masked on
            ## /foo <=> bar ---> /foo
            return clone (
                ($this->isAbsolute())
                    ? ((/* must not same */ $this->toString() === $pathUri->toString()) ? new UriSequence() : $this)
                    : $pathUri
            );

        // ...

        $muchLength = $this->getPath();
        $less       = $pathUri->getPath();

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less and $less = $this->getPath() ) : null;
            ;

        $masked = $muchLength;
        foreach($muchLength as $i => $v) {
            if (!isset($less[$i]) || $v != $less[$i])
                break;

            unset($masked[$i]);
        }

        $path = clone $this;
        $path->setPath($masked);
        return $path;
    }

    /**
     * Joint Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /var/www
     *
     * @param iUriSequence $pathUri
     *
     * @param bool $toggle
     * @return iUriSequence
     */
    function joint(iUriSequence $pathUri, $toggle = true)
    {
        $muchLength = $this->getPath();
        $less       = $pathUri->getPath();

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less && $less = $this->getPath() ) : null;
        ;

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
     * Split Path
     *
     * - return new pathUri instance with split
     *
     * /var/www/html
     * split(-1) => "/var/www"
     * split(0)  => "/"
     * split(1)  => "var/www/html"
     *
     * @param int      $start
     * @param null|int $length
     *
     * @return iUriSequence
     */
    function split($start, $length = null)
    {
        $self = clone $this;
        $self->normalize();
        
        $return = clone $this;
        $path   = array_slice($self->getPath(), $start, $length);
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
     * - the path must normalized before output
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
        
        $return = call_user_func($this->getEncodeUri(), $return);
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
    function setPath(array $path = null)
    {
        if ($path === null)
            $path = array();

        // the associate array is useless
        $this->_pathSequence = array_values($path);
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

    /**
     * Set Encode Uri
     *
     * @param \Closure $encoder
     *
     * @return $this
     */
    function setEncodeUri(\Closure $encoder)
    {
        $this->encodeUri = $encoder;
        return $this;
    }

    /**
     * Get Encode Uri
     *
     * @return \Closure
     */
    function getEncodeUri()
    {
        if ($this->encodeUri)
            return $this->encodeUri;

        // ..

        $this->encodeUri = function($pathStr) {
            return preg_replace_callback(
                '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
                function (array $matches) {
                    return rawurlencode($matches[0]);
                }
                , $pathStr
            );
        };

        return $this->getEncodeUri();
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
}
