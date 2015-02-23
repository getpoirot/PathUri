<?php
namespace Poirot\PathUri\Interfaces;

/**
 * When you need to know path separator everywhere
 * on this class, you have to catch it from getPath()
 * of this class
 *
 */
interface iPathFileUri extends iPathAbstractUri
{
    const PATH_AS_ABSOLUTE = 'display.absolute.include.basepath';
    const PATH_AS_RELATIVE = 'display.relative.without.basepath';

    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setPathSeparator($sep);

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getPathSeparator();

    /**
     * Set Base Path
     *
     * - with setting basepath value
     *   the path mode changed to AS_ABSOLUTE
     *   and it can be changed by setPathStrMode
     *   later
     *
     * @param iPathJoinedUri|string $pathUri
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setBasepath($pathUri);

    /**
     * Get Base Path
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iPathJoinedUri
     */
    function getBasepath();

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
    function allowOverrideBasepath($flag = true);

    /**
     * Has Override Basepath?
     *
     * @return boolean
     */
    function hasOverrideBasepath();


    // File/Dir Methods:

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
    function setBasename($name);

    /**
     * Gets the file name of the file
     *
     * - Without extension on files
     *
     * @return string
     */
    function getBasename();

    /**
     * Set the file extension
     *
     * ! throw exception if file is lock
     *
     * @param string|null $ext File Extension
     *
     * @return $this
     */
    function setExtension($ext);

    /**
     * Gets the file extension
     *
     * @return string
     */
    function getExtension();

    /**
     * Set Path To File/Directory
     *
     * @param iPathJoinedUri|string $pathUri
     *
     * @return $this
     */
    function setFilepath($pathUri);

    /**
     * Gets the path without filename
     *
     * - override path separator from this class
     * - create new empty path instance if not set
     *
     * @return iPathJoinedUri
     */
    function getFilepath();

    // Helpers:

    /**
     * Get Filename Include File Extension
     *
     * ! It's a combination of basename+'.'.extension
     *   combined with a dot
     *
     * @return string
     */
    function getFilename();

    /**
     * Set Display Full Path Mode
     *
     * @param self::PATH_AS_ABSOLUTE
     *       |self::PATH_AS_RELATIVE $mode
     *
     * @return $this
     */
    function setPathStrMode($mode);

    /**
     * Get Display Path Mode
     *
     * - used by toString method
     *
     * @return self::PATH_AS_RELATIVE | self::PATH_AS_ABSOLUTE
     */
    function getPathStrMode();
}
