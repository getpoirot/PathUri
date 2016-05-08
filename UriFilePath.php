<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iUriFilePath;
use Poirot\PathUri\Interfaces\iUriSequence;

class UriFilePath
    extends UriSequence
    implements iUriFilePath
{
    protected $pathSep = '/';

    protected $basename;
    protected $extension;
    
    /** @var iUriSequence */
    protected $basepath;
    /** @var iUriSequence */
    protected $path;
    

    /**
     * always default is relative
     *
     * @see getPathStrMode
     * @see setBasePath
     *
     * @var string
     */
    protected $pathMode = self::PATH_AS_RELATIVE;

    protected $allowOverrideBase = true;

    protected $normalize = false;


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
        $path = _uri::normalizeUnixPath(
            (string) $stringPath
            , $this->getSeparator()
            , false
        );
        
        // check the given path has file info .. {
        $pathJoin = new UriSequence();
        $pathJoin->setSeparator($this->getSeparator());
        $pathJoin->setPathSequence($path);

        $tmpPath = $pathJoin->getPathSequence();
        if (end($tmpPath) == '..') {
            // we have not filename
            $ret['path'] = $pathJoin;

            return $ret;
        }
        // ... }

        $m    = pathinfo($path);
        (!isset($m['dirname']))   ?: $ret['path']      = $m['dirname'];  // For paths that has xxx.xx
        (!isset($m['basename']))  ?: $ret['filename']  = $m['basename']; // <= name.ext
        (!isset($m['filename']))  ?: $ret['basename']  = $m['filename']; // <= name
        (!isset($m['extension'])) ?: $ret['extension'] = $m['extension'];

        if (isset($ret['extension']) && $ret['filename'] === '') {
            // for folders similar to .ssh
            unset($ret['extension']);

            $ret['filename'] = $ret['basename'];
        }

        if (isset($ret['path'])) {
            if ($ret['path'] === '.' || $ret['path'] === '')
                unset($ret['path']);
            else
                // build pathJoin object
                $ret['path'] = $pathJoin->setPathSequence($ret['path']);
        }

        return $ret;
    }


    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        if ($this->getPathStrMode() == self::PATH_AS_ABSOLUTE)
            return true;

        $filePath = clone $this->getPathSequence();
        $path = $filePath->normalize()
            ->getPathSequence();

        return (isset($path[0]) && $path[0] == $this->getSeparator());
    }
    
    
    // Options:

    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setSeparator($sep)
    {
        $this->pathSep = (string) $sep;
        return $this;
    }

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator()
    {
        return $this->pathSep;
    }



    /**
     * Get Array In Form Of PathInfo
     *
     * return [
     *  'basepath'  => iPathJoinedUri,
     *  'path'      => iPathJoinedUri,
     *  'basename'  => 'name_with', # without extension
     *  'extension' => 'ext',
     *  'filename'  => 'name_with.ext',
     * ]
     *
     * @return array
     */
    function toArray()
    {
        return array(
            'basepath'  => $this->getBasePath(),
            'path'      => $this->getPathSequence(),
            'basename'  => $this->getBasename(),
            'extension' => $this->getExtension(),
            'filename'  => $this->getFilename(),
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
        $finalPath = clone $this->getPathSequence();

        if (!$this->allowOverrideBase && $this->normalize)
            // Normalize Filepath before concat them
            // with normalize we have not ../ at begining of uri
            // that append to base path and final normalize
            // not override basepath so.
            $finalPath->normalize();

        if ($this->getPathStrMode() === self::PATH_AS_ABSOLUTE)
            $finalPath = $finalPath->prepend($this->getBasePath());

        if ($this->normalize)
            $finalPath->normalize();

        $finalPath = $finalPath->toString();

        // Also sequences slashes removed by normalize
        $realPathname = _uri::normalizeUnixPath(
            ( ($finalPath) ? ($finalPath.$this->getSeparator()) : '' )
            .$this->getFilename()
            , $this->getSeparator()
            , false
        );
        
        return $realPathname;
    }

    /**
     * Set Base Path
     *
     * - implement null for reset
     *
     * - with setting basepath value
     *   the path mode changed to AS_ABSOLUTE
     *   and it can be changed by setPathStrMode
     *   later
     *
     * @param iUriSequence|string|null $pathUri
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setBasePath($pathUri)
    {
        if ($pathUri == null)
            $pathUri = array();
        elseif(is_string($pathUri))
            $pathUri = _uri::normalizeUnixPath($pathUri);

        if (is_array($pathUri) || is_string($pathUri))
            $pathUri = new UriSequence(array(
                'path'      => $pathUri,
                'separator' => $this->getSeparator()
            ));
        elseif ($pathUri instanceof iUriSequence)
            $pathUri->setSeparator($this->getSeparator());
        else
            throw new \InvalidArgumentException(sprintf(
                'PathUti must be string or instanceof iPathJoinedUri, given: %s'
                , is_object($pathUri) ? get_class($pathUri) : gettype($pathUri)
            ));

        $this->basepath = $pathUri;

        $this->setPathStrMode(self::PATH_AS_ABSOLUTE);

        return $this;
    }

    /**
     * Get Base Path
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iUriSequence
     */
    function getBasePath()
    {
        if (!$this->basepath)
            $this->basepath = new UriSequence(array('path' => ''));

        $this->basepath->setSeparator($this->getSeparator());

        return $this->basepath;
    }

    /**
     * Set Allow Override Basepath
     *
     * - this will used on method:
     * @see getRelativePathname
     *
     *
     * @param boolean $flag
     *
     * @return $this
     */
    function setLeakOverrideBasePath($flag = true)
    {
        $this->allowOverrideBase = (boolean) $flag;

        return $this;
    }

    /**
     * Has Override Basepath?
     *
     * @return boolean
     */
    function isAllowOverrideBasePath()
    {
        return $this->allowOverrideBase;
    }

    /**
     * Set Filename of file or folder
     *
     * ! without extension
     *
     * - /path/to/filename[.ext]
     * - /path/to/folderName/
     *
     * @param string $name Basename
     *
     * @return $this
     */
    function setBasename($name)
    {
        $this->basename = (string) $name;

        return $this;
    }

    /**
     * Gets the file name of the file
     *
     * - Without extension on files
     *
     * @return string
     */
    function getBasename()
    {
        return $this->basename;
    }

    /**
     * Set the file extension
     *
     * ! throw exception if file is lock
     *
     * @param string|null $ext File Extension
     *
     * @return $this
     */
    function setExtension($ext)
    {
        $this->extension = (string) $ext;

        return $this;
    }

    /**
     * Gets the file extension
     *
     * @return string
     */
    function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set Path To File/Directory
     *
     * @param iUriSequence|string $pathUri
     *
     * @return $this
     */
    function setPathSequence($pathUri)
    {
        if ($pathUri == null)
            $pathUri = array();
        elseif(is_string($pathUri))
            $pathUri = _uri::normalizeUnixPath($pathUri);

        if (is_array($pathUri) || is_string($pathUri))
            $pathUri = new UriSequence(array(
                'path'      => $pathUri,
                'separator' => $this->getSeparator()
            ));
        elseif ($pathUri instanceof iUriSequence)
            $pathUri->setSeparator($this->getSeparator());
        else
            throw new \InvalidArgumentException(sprintf(
                'PathUti must be string or instanceof iPathJoinedUri, given: %s'
                , is_object($pathUri)
                    ? get_class($pathUri)
                    : var_export($pathUri, true)
            ));

        $this->path = $pathUri;

        return $this;
    }

    /**
     * Gets the path without filename
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iUriSequence
     */
    function getPathSequence()
    {
        if (!$this->path)
            $this->path = new UriSequence(array('path' => ''));

        $this->path->setSeparator($this->getSeparator());

        return $this->path;
    }

    /**
     * Get Filename Include File Extension
     *
     * ! It's a combination of basename+'.'.extension
     *   combined with a dot
     *
     * @return string
     */
    function getFilename()
    {
        $filename  = $this->getBasename();
        $extension = $this->getExtension();

        return ($extension === '' || $extension === null)
            ? $filename
            : $filename.'.'.$extension;
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        $this->normalize = true;

        return $this;
    }

    /**
     * Set Display Full Path Mode
     *
     * @param self ::PATH_AS_ABSOLUTE
     *       |self::PATH_AS_RELATIVE $mode
     *
     * @return $this
     */
    function setPathStrMode($mode)
    {
        if(!in_array($mode, [self::PATH_AS_ABSOLUTE, self::PATH_AS_RELATIVE]))
            throw new \InvalidArgumentException(sprintf(
                'Invalid Path Display Mode, given "%s".'
                , is_object($mode) ? get_class($mode) : gettype($mode)
            ));

        $this->pathMode = $mode;

        return $this;
    }

    /**
     * Get Display Path Mode
     *
     * - used by toString method
     *
     * @return UriFilePath::PATH_AS_RELATIVE | self::PATH_AS_ABSOLUTE
     */
    function getPathStrMode()
    {
        return $this->pathMode;
    }
}
