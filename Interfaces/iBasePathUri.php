<?php
namespace Poirot\PathUri\Interfaces;

interface iBasePathUri
{
    /**
     * Create a new URI object
     *
     * @param  iBasePathUri|string|array $pathUri
     *
     * @throws \InvalidArgumentException
     */
    function __construct($pathUri = null);

    /**
     * Parse The String Uri To It's Structure
     *
     * - parse string to associateArray,
     *   this array must can be used as an argument
     *   for fromArray method
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
     * - don't reset this object, so values merged with new one
     *
     * note: always the pathUri instance on given argument must
     *       be same as $this object
     *
     * @param iBasePathUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iPathUri*/ $path);

    /**
     * Is Absolute Path?
     *
     * - in most cases substr[0]-1 == ":" mean we have on absolute path
     *
     * @return boolean
     */
    function isAbsolute();

    /**
     * Get Uri Depth
     *
     * note: in case of /var/www/html
     *       0:/, 1:var, 2:www ...
     *       depth is 3
     *
     * @return int
     */
    function getDepth();

    /**
     * Split Path And Update Object To New Path
     *
     * /var/www/html
     * split(-1) => "/var/www"
     * split(0)  => "/"
     * split(1)  => "var/www/html"
     *
     * @param int      $start
     * @param null|int $end
     *
     * @return $this
     */
    function split($start, $end = null);

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset();

    /**
     * Get Array In Form Of AssocArray
     *
     * note: this array can be used as input for fromArray
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
     * - don`t call normalize path inside this method
     *   normalizing does happen by case
     *
     * @return string
     */
    function toString();
}
