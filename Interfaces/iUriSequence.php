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
 */
interface iUriSequence 
    extends iUriBase
{
    /**
     * Set Path Separator
     *
     * @param string $separator
     *
     * @return $this
     */
    function setSeparator($separator);

    /**
     * Set Uri Path
     *
     * ! null is to reset object and mean no path
     *
     * @param array|null $path
     *
     * @throws \InvalidArgumentException Uri not valid
     * @return $this
     */
    function setPath($path = null);

    /**
     * Get Uri Path
     *
     * ['/', 'var', 'www', 'html']
     *
     * @return array
     */
    function getPath();

    
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
     * Split Path
     *
     * - return new pathUri instance with split
     *
     * /var/www/html
     * split(0)     => "/var/www/html"
     * split(1)     => "var/www/html"
     * split(0, 2)  => "/var"
     * split(0, -1) => "/var/www"
     *
     * @param int      $start
     * @param null|int $length
     *
     * @return iUriSequence
     */
    function split($start, $length = null);

    /**
     * Append Path
     *
     * - manipulate current path
     *
     * @param iUriSequence $appendUri
     *
     * @return $this
     */
    function append(iUriSequence $appendUri);

    /**
     * Prepend Path
     *
     * - manipulate current path
     *
     * @param iUriSequence $prependUri
     *
     * @return $this
     */
    function prepend(iUriSequence $prependUri);

    /**
     * Merge paths
     *
     * .     <=> /bar ----> /bar
     * /foo  <=> /bar ----> /bar
     * /foo  <=> bar  ----> /bar
     * /foo/ <=> bar  ----> /foo/bar
     *
     * @param iUriSequence $mergeUri
     *
     * @return iUriSequence
     */
    function merge(iUriSequence $mergeUri);

    /**
     * Mask Given PathUri with Current Path
     *
     * toggle:
     * /var/www/html <=> /var/www/     ===> html
     * /uri          <=> contact       ===> /uri
     * /uri          <=> /contact      ===> uri
     * /uri/path     <=> /contact      ===> uri/path
     * /uri/         <=> /uri/contact  ===> (empty)
     * /uri/         <=> /uri/contact/ ===> contact/
     *
     * toggle false:
     * /var/www/     <=> /var/www/html ===> ''
     *
     * @param iUriSequence $pathUri
     * @param bool           $toggle  with toggle always bigger path
     *                                compared to little one
     *
     * @return iUriSequence
     */
    function mask(iUriSequence $pathUri, $toggle = true);

    /**
     * Joint Given PathUri with Current Path
     *
     * /var/www/html <=> /var/www/ ===> /var/www
     *
     * @param iUriSequence $pathUri
     *
     * @return iUriSequence
     */
    function joint(iUriSequence $pathUri);
}
