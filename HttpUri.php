<?php
namespace Poirot\PathUri;

use Poirot\Core\Interfaces\iPoirotEntity;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iPQueryEntity;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Query\PQEntity;

class HttpUri extends AbstractPathUri
    implements iHttpUri
{
    static $SCHEME = [
        'http'  => 80,
        'https' => 443,
    ];

    /*
        URI parts:
    */
    protected $scheme;
    protected $userInfo;
    protected $host;
    protected $port;
    protected $path;
    protected $query;
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
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator()
    {
        return '/';
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
        $scheme = strtolower($scheme);

        if(!empty($scheme) && !isset(self::$SCHEME[$scheme]))
            throw new \InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)'
                , $scheme
                , implode(', ', array_keys(self::$SCHEME))
            ));

        $this->scheme = $scheme;

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
        $this->host = strtolower((string) $host);

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
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));
        }

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
        $scheme = $this->getScheme();
        if (!$scheme || ($this->port === self::$SCHEME[$scheme]))
            return null;

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
        if (is_string($path))
            $path = new SeqPathJoinUri($path);

        if (!$path instanceof iSeqPathUri)
            throw new \InvalidArgumentException(sprintf(
                'Path must be uri string or instance of iSeqPathUri, "%s" given instead.'
                , is_object($path) ? get_class($path) : gettype($path)
            ));

        $pathStr = $path->toString();

        if (strpos($pathStr, '?') !== false)
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );

        if (strpos($pathStr, '#') !== false)
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );

        $this->path = $path->setSeparator($this->getSeparator());

        return $this;
    }

    /**
     * Get the URI path
     *
     * @return iSeqPathUri
     */
    function getPath()
    {
        return $this->path;
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
        $this->getQuery()->from($query);

        return $this;
    }

    /**
     * Get the URI query
     *
     * - entity setFrom query string,
     * - later: set query string as resource on entity object
     *
     * @return iPQueryEntity
     */
    function getQuery()
    {
        if (!$this->query)
            $this->query = new PQEntity;

        return $this->query;
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
     * Get Array In Form Of AssocArray
     *
     * note: this array can be used as input for fromArray
     *
     * @return array
     */
    function toArray()
    {
        $parse = [
            'scheme'    => $this->getScheme(),
            'user_info' => $this->getUserInfo(),
            'host'      => $this->getHost(),
            'port'      => $this->getPort(),
            'path'      => $this->getPath(),
            'query'     => $this->getQuery(),
            'fragment'  => $this->getFragment(),
        ];

        ## only return values that not null
        return array_filter($parse, function($v) {
            if ($v instanceof iPoirotEntity)
                return !$v->isEmpty();

            return !($v === null);
        });
    }

    /**
     * Normalize Array Path Stored On Class
     *
     * @return $this
     */
    function normalize()
    {
        $this->getPath()->normalize();

        return $this;
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
            $uri .= $this->getScheme() . ':'. '//';

        if ($this->getHost() !== null) {
            if ($this->getUserInfo())
                $uri .= $this->getUserInfo() . '@';

            $uri .= $this->getHost();
            if ($this->getPort())
                $uri .= ':' . $this->getPort();
        }

        $replace = function ($match) {
            return rawurlencode($match[0]);
        };

        if ($this->getPath()) {
            $regex   = '/(?:[^' .'a-zA-Z0-9_\-\.~'. ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/';
            $uri .= preg_replace_callback($regex, $replace, $this->getPath()->toString());
        }
        elseif ($this->getHost() && (!$this->getQuery()->isEmpty() || $this->getFragment()))
            $uri .= '/';

        if ($this->getQuery()->borrow()) {
            $regex   = '/(?:[^' .'a-zA-Z0-9_\-\.~' .'!\$&\'\(\)\*\+,;=' .'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/';
            $uri .= "?" . preg_replace_callback($regex, $replace, $this->getQuery()->toString());
        }

        if ($this->getFragment()) {
            $regex   = '/(?:[^' .'a-zA-Z0-9_\-\.~' .'!\$&\'\(\)\*\+,;=' .'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/';
            $uri .= "#" . preg_replace_callback($regex, $replace, $this->getFragment());
        }

        return $uri;
    }
}
