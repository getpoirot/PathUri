<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iPathFileUri;
use Poirot\PathUri\Interfaces\iPathJoinedUri;

class PathFileUri extends PathAbstractUri
    implements iPathFileUri
{
    protected $pathSep = '/';

    /**
     * @var iPathJoinedUri
     */
    protected $basepath;
    /**
     * @var iPathJoinedUri
     */
    protected $filepath;
    protected $basename;
    protected $extension;

    protected $pathMode = self::PATH_AS_ABSOLUTE;

    protected $allowOverrideBase = true;

    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setPathSeparator($sep)
    {
        $this->pathSep = (string) $sep;

        return $this;
    }

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getPathSeparator()
    {
        return $this->pathSep;
    }

    /**
     * Build Object From String
     *
     * - parse string to associateArray setter
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

        $path = $this->normalizePathStr($pathStr);

        // check the given path has file info .. {
        $pathJoin = new PathJoinUri([
            'path'      => $path,
            'separator' => $this->getPathSeparator(),
        ]);

        $tmpPath = $pathJoin->toArray()['path'];
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

        if ($ret['path'] === '.' || $ret['path'] === '')
            unset($ret['path']);
        else
            // build pathJoin object
            $ret['path'] = $pathJoin->fromArray([
                'path' => $ret['path']
            ]);

        return $ret;
    }

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        // TODO: Implement isAbsolute() method.
    }

    /**
     * Get Array In Form Of PathInfo
     *
     * return [
     *  'basepath'  => iPathJoinedUri,
     *  'filepath'  => iPathJoinedUri,
     *  'basename'  => 'name_with', # without extension
     *  'extension' => 'ext',
     *  'filename'  => 'name_with.ext',
     * ]
     *
     * @return array
     */
    function toArray()
    {
        return [
            'basepath'  => $this->getBasepath(),
            'filepath'  => $this->getFilepath(),
            'basename'  => $this->getBasename(),
            'extension' => $this->getExtension(),
            'filename'  => $this->getFilename(),
        ];
    }

    /**
     * Get Assembled Path As String
     *
     * @return string
     */
    function toString()
    {
        // TODO: Implement toString() method.
        $base = $this->joinPath($this->getBasepath());
        $path = $this->joinPath($this->getPath());

        // Also sequences slashes removed by normalize
        $realPathname = $this->normalizePathStr(
            $base.
            $path.self::DS.
            $this->getFilename()
        );

        return $realPathname;
    }

    /**
     * Set Base Path
     *
     * @param iPathJoinedUri $pathUri
     *
     * @return $this
     */
    function setBasepath(iPathJoinedUri $pathUri)
    {
        $this->basepath = $pathUri;

        return $this;
    }

    /**
     * Get Base Path
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iPathJoinedUri
     */
    function getBasepath()
    {
        if (!$this->basepath)
            $this->basepath = new PathJoinUri(['path' => '']);

        $this->basepath->setSeparator($this->getPathSeparator());

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
    function allowOverrideBasepath($flag = true)
    {
        $this->allowOverrideBase = (boolean) $flag;

        return $this;
    }

    /**
     * Has Override Basepath?
     *
     * @return boolean
     */
    function hasOverrideBasepath()
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
     * @param iPathJoinedUri $pathUri
     *
     * @return $this
     */
    function setFilepath(iPathJoinedUri $pathUri)
    {
        $this->filepath = $pathUri;

        return $this;
    }

    /**
     * Gets the path without filename
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iPathJoinedUri
     */
    function getFilepath()
    {
        if (!$this->filepath)
            $this->filepath = new PathJoinUri(['path' => '']);

        $this->filepath->setSeparator($this->getPathSeparator());

        return $this->filepath;
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
     * @return self::PATH_AS_RELATIVE | self::PATH_AS_ABSOLUTE
     */
    function getPathStrMode()
    {
        return $this->pathMode;
    }

    // In Methods Usage:

    /**
     * Fix common problems with a file path
     *
     * @param string $path
     * @param bool   $stripTrailingSlash
     *
     * @return string
     */
    protected function normalizePathStr($path, $stripTrailingSlash = true)
    {
        $separator = $this->getFilepath()->getSeparator();

        // convert paths to portables one
        $path = Util::normalizeUnixPath(
            str_replace('\\', $separator, $path)
            , $separator
            , $stripTrailingSlash
        );

        return $path;
    }

    /**
     * Normalizes that parent directory references and removes redundant ones.
     *
     * @param string[] $paths List of parts in the the path
     *
     * @return string[] Normalized list of paths
     */
    protected function normalizePathArr(array $paths)
    {
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

        return $paths;
    }
}
