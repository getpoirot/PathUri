<?php
namespace Poirot\PathUri\Interfaces;

interface iPathUri
{
    /**
     * Create a new URI object
     *
     * @param  iPathUri|string|array $pathUri
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($pathUri = null);

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
    function parse($pathStr);

    /**
     * Build Object From Array
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
     * note: it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iPathAbstractUri*/ $path);

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute();

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset();

    /**
     * Get Array In Form Of AssocArray
     *
     * return [
     *  'path'      => iPathJoinedUri,
     *  'basename'  => 'name_with', # without extension
     *  'extension' => 'ext',
     *  'filename'  => 'name_with.ext',
     * ]
     *
     * @return array
     */
    function toArray();

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize();

    /**
     * Get Assembled Path As String
     *
     * - the path must normalized before output
     *
     * @return string
     */
    function toString();
}