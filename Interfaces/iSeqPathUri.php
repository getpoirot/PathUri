<?php
namespace Poirot\PathUri\Interfaces;

/**
 * This interface used for sequencing paths with separator
 *
 * we have paths in form of arrays and separated with the separator
 * /var/www/html, http://yoursite.com/path/to/ser, send:/again/path/
 *
 * - Normally, the empty path "" and absolute path "/" are considered equal as
 *   defined in RFC 7230 Section 2.7.3.
 *   But this method MUST NOT automatically
 *   do this normalization because in contexts with a trimmed base path, e.g.
 *   the front controller, this difference becomes significant. It's the task
 *   of the user to handle both "" and "/"
 *
 *
 *
 */
interface iSeqPathUri extends iBasePathUri
{

    // Parse Getter/Setter Methods:

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


    // Operation Methods:

    /**
     * Append Path
     *
     * - manipulate current path
     *
     * @param iBasePathUri $pathUri
     *
     * @return $this
     */
    function append($pathUri);

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iBasePathUri $pathUri
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
     * /var/www/     <=> /var/www/html ===> ''
     *
     * - manipulate current path
     *
     * @param iSeqPathUri $pathUri
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
     * @param iSeqPathUri $pathUri
     *
     * @param bool $toggle
     * @return $this
     */
    function joint($pathUri, $toggle = true);
}
