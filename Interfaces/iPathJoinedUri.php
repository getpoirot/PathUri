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
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function append($pathUri);

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri);

    /**
     * Mask Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /html
     *
     * - manipulate current path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function mask($pathUri);
}
