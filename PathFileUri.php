<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iPathFileUri;

class PathFileUri extends AbstractPathUri
    implements iPathFileUri
{
    protected $basepath = [];
    protected $path     = [];
    protected $basename;
    protected $extension;

    /**
     * @var bool
     */
    protected $overrideBasepath = false;

    /**
     * Set Base Path
     *
     * @param array|string $path
     *
     * @throws \Exception
     * @return $this
     */
    function setBasepath($path)
    {
        if (is_string($path))
            // Set Path From String
            $path = (new PathFileUri)
                ->setPath($path)
                ->getPath();

        if (!is_array($path))
            throw new \Exception('Path must be a string or array.');

        $this->basepath = $path;

        return $this;
    }

    /**
     * Get Base Path
     *
     * @return array
     */
    function getBasepath()
    {
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
    function setOverrideBasepath($flag)
    {
        $this->overrideBasepath = (boolean) $flag;

        return $this;
    }

    /**
     * Has Override Basepath?
     *
     * @return boolean
     */
    function hasOverrideBasepath()
    {
        return $this->overrideBasepath;
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
     * Set Path
     *
     * - path array in form of ['path', 'to', 'dir']
     *
     * @param array|string $path Path To File/Folder
     *
     * @throws \Exception
     * @return $this
     */
    function setPath($path)
    {
        if ($path == null) {
            // AbstractPathUri::reset
            $this->path = [];
            return $this;
        }

        if (is_string($path)) {
            $path = $this->normalizePathStr($path);
            $path = explode(self::DS, $path);
        }

        if (!is_array($path))
            throw new \Exception(sprintf(
                'Path Must Be Array Or String, "%s" given.'
                , is_object($path) ? get_class($path) : gettype($path)
            ));


        // :
        $parts = $this->normalizePathArr($path);
        $this->path = $parts;

        return $this;
    }

    /**
     * Gets the path without filename
     *
     * - return in form of ['path', 'to', 'dir']
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
     * - reset object current parts
     * - parse string and build object
     *
     * @param string $pathUri
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromString($pathUri)
    {
        $path = [];
        if (is_string($pathUri))
            $path = $this->getPathInfo($pathUri);

        $this->fromArray($path);

        return $this;
    }

    /**
     * Get PathUri Object As String
     *
     * @return string
     */
    function getRealPathname()
    {
        $path = $this->joinPath($this->getPath());

        // Also sequences slashes removed by normalize
        $realPathname = $this->normalizePathStr($path.self::DS.$this->getFilename());

        return $realPathname;
    }

    /**
     * Get Relative Path To Basepath
     *
     * - with overrideBasepath flag
     *   if basepath was set we can't go
     *   further back in basepath.
     *   [/base]/../directory for second part
     *   will always return /
     *
     * @return string
     */
    function getRelativePathname()
    {
        // TODO: Implement getRelativePathname() method.
    }

    /**
     * Join Path
     *
     * @param array $path
     *
     * @return string
     */
    function joinPath(array $path)
    {
        $path = $this->normalizePathArr($path);

        return implode(self::DS, $path);
    }

    /**
     * Get Array In Form Of PathInfo
     *
     * return [
     *  'path'      => ['path', 'to', 'dir'],
     *  'impath'    => 'path/to/dir',
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
            'path'      => $this->getPath(),
            'basename'  => $this->getBasename(),
            'extension' => $this->getExtension(),
            'filename'  => $this->getFilename(),
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

    /**
     * Extract Path Info
     *
     * @param string $path
     *
     * @return array
     */
    protected function getPathInfo($path)
    {
        $path = $this->normalizePathStr($path);

        $ret  = [];

        $exPath = explode(self::DS, $path);
        if (end($exPath) == '..') {
            // we have not filename
            $ret['path'] = $path;

            return $ret;
        }

        $m    = pathinfo($path);
        (!isset($m['dirname']))   ?: $ret['path']      = $m['dirname'];  // For file with name.ext
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

        return $ret;
    }

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
        if ($path == '')
            return $path;

        // convert paths to portables one
        $path = str_replace('\\', self::DS, $path);

        // remove sequences of slashes
        $path = preg_replace('#'.self::DS.'{2,}#', self::DS, $path);

        //remove trailing slash
        if ($stripTrailingSlash
            && strlen($path) > 1
            && substr($path, -1, 1) === self::DS
        )
            $path = substr($path, 0, -1);

        return $path;
    }
}
