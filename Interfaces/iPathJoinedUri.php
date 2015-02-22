<?php
namespace Poirot\PathUri\Interfaces;

interface iPathJoinedUri extends iPathAbstractUri
{
    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setSeparator($sep);

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator();

    /**
     * Append Path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function append($pathUri);

    /**
     * Prepend Path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri);

    /**
     * Mask Given PathUri with Current Path
     * And Return New Object Of Difference
     *
     * /var/www/html <=> /var/www/ ===> /html
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return iPathAbstractUri
     */
    function mask($pathUri);
}
