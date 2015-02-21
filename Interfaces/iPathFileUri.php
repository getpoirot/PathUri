<?php
namespace Poirot\PathUri\Interfaces;

interface iPathFileUri extends iPathUri
{
    const DS = DIRECTORY_SEPARATOR;

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
     * Get Filename Include File Extension
     *
     * ! It's a combination of basename+'.'.extension
     *   combined with a dot
     *
     * @return string
     */
    function getFilename();

    /**
     * Set Path
     *
     * - in form of ['path', 'to', 'dir']
     *
     * @param array|string $path Path To File/Folder
     *
     * @return $this
     */
    function setPath($path);

    /**
     * Gets the path without filename
     *
     * - return in form of ['path', 'to', 'dir']
     *
     * @return array
     */
    function getPath();
} 