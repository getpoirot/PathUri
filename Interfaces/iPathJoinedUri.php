<?php
namespace Poirot\PathUri\Interfaces;

interface iPathJoinedUri extends iPathUri
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
     * @param iPathUri $pathUri
     *
     * @return $this
     */
    function append($pathUri);

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iPathUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri);

    /**
     * Mask Given PathUri with Current Path
     *
     * toggle:
     * /var/www/html <=> /var/www/     ===> /html
     *
     * toggle false:
     * /var/www/     <=> var/www/html ===> ''
     *
     * - manipulate current path
     *
     * @param iPathJoinedUri $pathUri
     * @param bool           $toggle  with toggle always bigger path
     *                                compared to little one
     *
     * @return $this
     */
    function mask($pathUri, $toggle = true);

    /**
     * Joint Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /var/www
     *
     * - manipulate current path
     *
     * @param iPathJoinedUri $pathUri
     *
     * @param bool $toggle
     * @return $this
     */
    function joint($pathUri, $toggle = true);
}
