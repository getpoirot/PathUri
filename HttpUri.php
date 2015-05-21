<?php
namespace Poirot\PathUri;

use Poirot\Core\Interfaces\iPoirotEntity;
use Poirot\PathUri\Interfaces\iBasePathUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;

class HttpUri extends AbstractPathUri
    implements iHttpUri
{
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';

    /*
        URI parts:
    */
    protected $scheme;
    protected $userInfo;
    protected $host;
    protected $port;
    protected $fragment;

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
    function parse($pathStr)
    {
        if (!is_string($pathStr))
            throw new \InvalidArgumentException(sprintf(
                'PathStr must be string but "%s" given.'
                , is_object($pathStr) ? get_class($pathStr) : gettype($pathStr)
            ));

        $return = [];

        // userInfo part:
        if (preg_match('/\/(?P<user_info>([\w]+[:]*[\w])+)@(\w+)/', $pathStr, $match)) {
            $return['user_info'] = $match['user_info'];
            $pathStr = str_replace($return['user_info'].'@', '', $pathStr);
        }

        $parse  = parse_url($pathStr);
        $return = array_merge($parse, $return);

        return $return;
    }

    /**
     * Set the URI scheme
     *
     * @param string $scheme
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setScheme($scheme)
    {
        if(!defined(get_class($this).'::SCHEME_'.strtoupper($scheme)))
            throw new \InvalidArgumentException(sprintf(
                'Scheme "%s" not defined.'
                , $scheme
            ));

        $this->scheme = strtolower((string) $scheme);

        return $this;
    }

    /**
     * Get the scheme
     *
     * - the value returned MUST be normalized to lowercase
     * - the trailing ":" character is not part of the scheme and MUST NOT be
     *   added.
     *
     * @return string|false
     */
    function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the URI User-info part (usually user:password)
     *
     * @param  string $userInfo
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    /**
     * Retrieve the userInfo component of the URI
     * (usually user:password)
     *
     * The info syntax of the URI is:
     * [user-info@]host
     *
     * @return string|false The URI user information, in "username[:password]" format
     */
    function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Set the URI host
     *
     * Note that the generic syntax for URIs allows using host names which
     * are not necessarily IPv4 addresses or valid DNS host names. For example,
     * IPv6 addresses are allowed as well, and also an "registered name"
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
    function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the URI host
     *
     * - The value returned MUST be normalized to lowercase
     *
     * @return string|false
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Set the URI host port
     *
     * @param int $port
     *
     * @return $this
     */
    function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

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
     * @return int|false
     */
    function getPort()
    {
        return $this->port;
    }

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
    function setPath($path)
    {
        // TODO: Implement setPath() method.
    }

    /**
     * Get the URI path
     *
     * @return iSeqPathUri
     */
    function getPath()
    {
        // TODO: Implement getPath() method.
    }

    /**
     * Set the query
     *
     * @param string|array|iPoirotEntity $query
     *
     * @return $this
     */
    function setQuery($query)
    {
        // TODO: Implement setQuery() method.
    }

    /**
     * Get the URI query
     *
     * - entity setFrom query string,
     * - later: set query string as resource on entity object
     *
     * @return iPoirotEntity
     */
    function getQuery()
    {
        // TODO: Implement getQuery() method.
    }

    /**
     * Set the URI fragment
     *
     * @param string $fragment
     *
     * @return $this
     */
    function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Get the URI fragment
     *
     * @return string|false
     */
    function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Create a new URI object
     *
     * @param  iBasePathUri|string|array $pathUri
     *
     * @throws \InvalidArgumentException
     */
    function __construct($pathUri = null)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Build Object From Array
     *
     * @param array $arrPath
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromArray(array $arrPath)
    {
        // TODO: Implement fromArray() method.
    }

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
    function fromPathUri(/*iPathUri*/
        $path)
    {
        // TODO: Implement fromPathUri() method.
    }

    /**
     * Is Absolute Path?
     *
     * - in most cases substr[0]-1 == ":" mean we have on absolute path
     *
     * @return boolean
     */
    function isAbsolute()
    {
        return ($this->getScheme() !== null);
    }

    /**
     * Get Uri Depth
     *
     * note: in case of /var/www/html
     *       0:/, 1:var, 2:www ...
     *       depth is 3
     *
     * @return int
     */
    function getDepth()
    {
        // TODO: Implement getDepth() method.
    }

    /**
     * Split Path And Update Object To New Path
     *
     * /var/www/html
     * split(-1) => "/var/www"
     * split(0)  => "/"
     * split(1)  => "var/www/html"
     *
     * @param int $start
     * @param null|int $end
     *
     * @return $this
     */
    function split($start, $end = null)
    {
        // TODO: Implement split() method.
    }

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset()
    {
        // TODO: Implement reset() method.
    }

    /**
     * Get Array In Form Of AssocArray
     *
     * note: this array can be used as input for fromArray
     *
     * @return array
     */
    function toArray()
    {
        // TODO: Implement toArray() method.
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        // TODO: Implement normalize() method.
    }

    /**
     * Get Assembled Path As String
     *
     * - don`t call normalize path inside this method
     *   normalizing does happen by case
     *
     * @return string
     */
    function toString()
    {
        $uri = '';

        if ($this->getScheme())
            $uri .= $this->getScheme() . ':';

        if ($this->getHost() !== null) {
            $uri .= '//';
            if ($this->getUserInfo())
                $uri .= $this->getUserInfo() . '@';

            $uri .= $this->getHost();
            if ($this->getPort())
                $uri .= ':' . $this->getPort();
        }

        /*
        if ($this->getPath()) {
            $uri .= static::encodePath($this->getPath()->toString());
        } elseif ($this->host && ($this->query || $this->fragment)) {
            $uri .= '/';
        }

        if ($this->query) {
            $uri .= "?" . static::encodeQueryFragment($this->query);
        }

        if ($this->fragment) {
            $uri .= "#" . static::encodeQueryFragment($this->fragment);
        }
        */

        return $uri;
    }
}
