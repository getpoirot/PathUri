<?php
namespace Poirot\PathUri\Interfaces;

use Poirot\Std\Interfaces\Pact\ipConfigurable;

interface iUriBase
    extends ipConfigurable
{
    /**
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator();

    /**
     * Is Absolute Path?
     *
     * - in most cases substr[0]-1 == ":" mean we have on absolute path
     *
     * @return boolean
     */
    function isAbsolute();

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize();

    /**
     * Get Array In Form Of AssocArray
     *
     * - don't make conversion on values,
     *   just separate uri parts as is.
     *   query_params : x=y&z=4
     *   make any conversion on setter methods
     *
     * ! this must contains all data in object even ones
     *   that is null
     *
     * note: this array can be used as input for ::with
     *
     * @return array
     */
    function toArray();

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
