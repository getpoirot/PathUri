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

/**
 * note: string paths usually must be normalized from
 *       the class that used this
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
     * // TODO implement rfc 7230 Section 2.7.3 for empty paths and "/"
     *
     * @param string $stringPath
     *
     * @return mixed
     */
    function doParseFromString($stringPath)
    {
        $stringPath = (string) $stringPath;

        $DS = $this->getSeparator();

        ## don`t remove trailing slash, have useful in paths
        // NO Normalization on creating paths
        // $pathStr = Util::normalizeUnixPath($stringPath, $DS, false);
        $pathStr = $stringPath;
        if ($pathStr === $this->getSeparator())
            ## in case of "/"
            $path = array($DS,);
        elseif ($pathStr === '')
            ## Current Directory
            $path = array();
        else {
            // NO Normalization on creating paths
            // $path = $this->_normalize(explode($DS, $pathStr));
            $path = explode($DS, $pathStr);
            if (isset($path[0]) && $path[0] == '')
                // explode affect on absolute addresses
                // start with separator. exp. "/var/www/"
                $path[0] = $DS;
        }

        return array(
            'path_sequence' => $path,
            'separator'     => $DS,
        );
    }

    
    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        $p = $this->_pathSequence;

        reset($p);
        $fi = current($p);

        $result =
            $fi === $this->getSeparator()
            ## c:[/df]
            || substr($fi, -1) === ':'
        ;

        return $result;
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
        $appendPath = $appendUri->getPathSequence();

        if ($appendUri->isAbsolute())
            $appendPath = $this->_makeNoneAbsolutePathSequence($appendPath);

        $finalPath = array_merge($this->_pathSequence, $appendPath);
        $this->setPathSequence($finalPath);

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
        $toPath = $this->getPathSequence();
        if ($this->isAbsolute())
            $toPath = $this->_makeNoneAbsolutePathSequence($toPath);

        $prependPath = $prependUri->getPathSequence();
        $finalPath   = $this->_makeNoneAbsolutePathSequence(
            array_merge($prependPath, $toPath)
        );

        # make it absolute
        $flagAbsolute = $prependUri->isAbsolute() || $this->isAbsolute();
        (!$flagAbsolute) ?: array_unshift($finalPath, $this->getSeparator());

        $this->setPathSequence($finalPath);
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

        $muchLength = $this->_pathSequence;
        $less       = $pathUri->getPathSequence();

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less and $less = $this->_pathSequence ) : null;
            ;

        $masked = $muchLength;
        foreach($muchLength as $i => $v) {
            if (!isset($less[$i]) || $v != $less[$i])
                break;

            unset($masked[$i]);
        }

        $path = clone $this;
        $path->setPathSequence($masked);
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
        $muchLength = $this->_pathSequence;
        $less       = $pathUri->getPathSequence();

        if ($toggle)
            (count($less) >= count($muchLength))
                ? ( $muchLength = $less && $less = $this->_pathSequence ) : null;
        ;

        $similar = array(); // empty path
        foreach($muchLength as $i => $v) {
            if (!array_key_exists($i, $less) || $v != $less[$i])
                break;

            $similar[] = $v;
        }

        $path = clone $this;
        $path->setPathSequence($similar);
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
        $path   = array_slice($self->getPathSequence(), $start, $length);
        $return->setPathSequence($path);

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
            'path_sequence' => $this->_pathSequence,
            'separator'     => $this->getSeparator()
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
        $path   = $this->_pathSequence;
        if (empty($path))
            return '';

        $DS = $this->getSeparator();

        if ($path == array($DS))
            // its home, implode not working for on element
            return $DS;

        // add empty slashes after all
        // that implode work properly for
        // paths with one member
        $path[] = '';
        $return = implode( $this->getSeparator(), $this->_pathSequence );
        $return = call_user_func($this->getEncodeUri(), $return);

        return UTUri::normalizeUnixPath($return, $this->getSeparator(), false);
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        $paths = $this->_normalize($this->getPathSequence());
        $this->setPathSequence($paths);

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
        return count($this->_pathSequence);
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
    function setPathSequence(array $path = null)
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
    function getPathSequence()
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
        if (current($pathSequence) === $this->getSeparator())
            unset($pathSequence[0]);

        return $pathSequence;
    }
    
    /**
     * Normalize Array Path
     *
     * @param array $paths
     *
     * @return array
     */
    protected function _normalize(array $paths)
    {
        if (empty($paths))
            return $paths;

        /*$appendPath = array_filter($appendPath, function($p) {
            // Remove all ['',] from path
            // on appended path we don't want any absolute sign in
            // array list
            return $p !== $this->getSeparator();
        });*/

        // Cleanup empty directories ".", "//":
        reset($paths); $i = 0; $indexes = array();
        while(($val = current($paths)) !== false) {
            if (
                ($val == $this->getSeparator() || $val === '' || $val === '.')
                && ($i > 0 && ($i < count($paths) -1 || $val === '.') /* get last one slash */)
            )
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
}
