<?php
namespace Poirot\UriPath;

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
     * Build Object From String
     *
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
     * @param array $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromArray(array $path);

    /**
     * Build Object From PathUri
     *
     * - it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iUriPath*/ $path);

    /**
     * Get PathUri Object As String
     *
     * @return string
     */
    function toString();

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
