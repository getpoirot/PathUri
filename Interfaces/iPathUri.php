<?php
namespace Poirot\PathUri\Interfaces;

interface iPathUri
{
    /**
     * Create a new URI object
     *
     * @param  iPathUri|string|array $uri
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($uri = null);

    /**
     * Set Base Path
     *
     * @param array|string $path
     *
     * @return $this
     */
    function setBasepath($path);

    /**
     * Get Base Path
     *
     * @return array
     */
    function getBasepath();

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
    function fromString($pathUri);

    /**
     * Build Object From Array
     *
     * - reset object current parts
     *
     * @param array $arrPath
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromArray(array $arrPath);

    /**
     * Build Object From PathUri
     *
     * - reset object current parts
     *
     * note: it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iUriPath*/ $path);

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset();

    /**
     * Set Allow Override Basepath
     *
     * - this will used on method:
     *   @see getRelativePathname
     *
     *
     * @param boolean $flag
     *
     * @return $this
     */
    function setOverrideBasepath($flag);

    /**
     * Has Override Basepath?
     *
     * @return boolean
     */
    function hasOverrideBasepath();

    /**
     * Get Real Path File Name With Basepath included
     *
     * - it will manipulate basepath
     *   for exp. in case of [/base]/../
     *   it remain /
     *
     * - with overrideBasepath flag
     *   if basepath was set we can't go
     *   further back in basepath.
     *   [/base]/../directory for second part
     *   will always return /
     *
     * @return string
     */
    function getRealPathname();

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
    function getRelativePathname();

    /**
     * Join Path
     *
     * @param array $path
     *
     * @return string
     */
    function joinPath(array $path);

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
    function toArray();
}
