<?php
namespace Poirot\PathUri\Interfaces;

use Poirot\Core\Interfaces\iPoirotEntity;

interface iHttpUri extends iBasePathUri
{
    // Parse Getter/Setter Methods:

    /**
     * Set the URI scheme
     *
     * @param string $scheme
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setScheme($scheme);

    /**
     * Get the scheme
     *
     * - the value returned MUST be normalized to lowercase
     * - the trailing ":" character is not part of the scheme and MUST NOT be
     *   added.
     *
     * @return string|null
     */
    function getScheme();

    /**
     * Set the URI User-info part (usually user:password)
     *
     * @param  string $userInfo
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setUserInfo($userInfo);

    /**
     * Retrieve the userInfo component of the URI
     * (usually user:password)
     *
     * The info syntax of the URI is:
     * [user-info@]host
     *
     * @return string|null The URI user information, in "username[:password]" format
     */
    function getUserInfo();

    /**
     * Set the URI host
     *
     * Note that the generic syntax for URIs allows using host names which
     * are not necessarily IPv4 addresses or valid DNS host names. For example,
     * IPv6 addresses are allowed as well, and also an abstract "registered name"
     * which may be any name composed of a valid set of characters, including,
     * for example, tilda (~) and underscore (_) which are not allowed in DNS
     * names.
     *
     * Subclasses of Uri may impose more strict validation of host names - for
     * example the HTTP RFC clearly states that only IPv4 and valid DNS names
     * are allowed in HTTP URIs.
     *
     * @param string $host
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setHost($host);

    /**
     * Get the URI host
     *
     * - The value returned MUST be normalized to lowercase
     *
     * @return string|null
     */
    function getHost();

    /**
     * Set the URI host port
     *
     * @param int $port
     *
     * @return $this
     */
    function setPort($port);

    /**
     * Get the URI host port
     *
     * - If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * - If no port is present, and no scheme is present, this method MUST return
     * a null value
     *
     * - If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null
     *
     * @return int|null
     */
    function getPort();

    /**
     * Set the path
     *
     * - The path can either be 1)empty or 2)absolute (starting with a slash) or
     * 3)rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes
     *
     * - The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @param string|iSeqPathUri $path
     *
     * @return $this
     */
    function setPath($path);

    /**
     * Get the URI path
     *
     * @return iSeqPathUri
     */
    function getPath();

    /**
     * Set the query
     *
     * @param string|array|iPoirotEntity $query
     *
     * @return $this
     */
    function setQuery($query);

    /**
     * Get the URI query
     *
     * - entity setFrom query string,
     * - later: set query string as resource on entity object
     *
     * @return iPQueryEntity
     */
    function getQuery();

    /**
     * Set the URI fragment
     *
     * @param string $fragment
     *
     * @return $this
     */
    function setFragment($fragment);

    /**
     * Get the URI fragment
     *
     * @return string|null
     */
    function getFragment();
}
