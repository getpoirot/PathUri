<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Http\DataQueryParams;
use Poirot\PathUri\Interfaces\iUriHttp;
use Poirot\PathUri\Interfaces\iDataQueryParams;

/*
 * Path:
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
 */

/**
 * TODO What if we want to render a part of Uri;
 *   seems not bad to separate path() method that fully return UriPathName and so on
 * 
 *   $reqUrl->setScheme(null)->setHost(null)->setUserInfo(null)->setPort(null);
 *   $target = $reqUrl->toString();
 *   
 *   $reqUrl->path()->toString()
 */

class UriHttp
    extends UriPathName
    implements iUriHttp
{
    static protected $SCHEME = array(
        'http'  => 80,
        'https' => 443,
    );

    /*
        URI parts:
    */
    protected $scheme;
    protected $userInfo;
    protected $host;
    protected $port;
    protected $query;
    protected $fragment;


    /**
     * Parse path string to parts in associateArray
     *
     * @param string $stringPath
     *
     * @return mixed
     */
    protected function doParseFromString($stringPath)
    {
        $parsed = array(
            'scheme'    => '',
            'user_info' => '',
            'host'      => '',
            'port'      => '',
            'path'      => '',
            'query'     => '',
            'fragment'  => '',
        );

        $stringPath = str_replace('\\', '/', $stringPath);

        # userInfo part:
        // TODO it can be given from parse_url directly
        if (preg_match('/\/(?P<user_info>([\w]+[:]*[\w])+)@(\w+)/', $stringPath, $match)) {
            $parsed['user_info'] = $match['user_info'];
            // then remove
            $stringPath = str_replace($parsed['user_info'].'@', '', $stringPath);
        }

        $purl = parse_url($stringPath);
        if ($purl === false)
            throw new \InvalidArgumentException(sprintf(
                'The source URI string seems invalid; given: "%s".'
                , $stringPath
            ));

        $parsed = array_merge($parsed, $purl);

        # filter parts
        array_walk($parsed, function(&$item, $key) {
            $method = '_filter'.\Poirot\Std\cast((string)$key)->camelCase();
            if (method_exists($this, $method))
                $item = call_user_func(array($this, $method), $item);
        });

        $parsed = array_merge($parsed, parent::doParseFromString($parsed['path']));
        return $parsed;
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
        $parse = array(
            'scheme'    => $this->getScheme(),
            'user_info' => $this->getUserInfo(),
            'host'      => $this->getHost(),
            'port'      => $this->getPort(),
            'path'      => $this->getPath(),
            'query'     => $this->getQuery()->toString(),
            'fragment'  => $this->getFragment(),
            
            'authority' => $this->getAuthority(),
        );

        return $parse;
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

        $uri .= $this->getAuthority();
        $uri .= parent::toString();

        if ($this->getHost() && (!$this->getQuery()->isEmpty() || $this->getFragment()))
            $uri .= '/';

        $replace = function ($match) { return rawurlencode($match[0]); };
        if (\Poirot\Std\cast($this->getQuery())->toArray()) {
            $regex   = '/(?:[^' .'a-zA-Z0-9_\-\.~' .'!\$&\'\(\)\*\+,;=' .'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/';
            $uri .= "?" . preg_replace_callback($regex, $replace, $this->getQuery()->toString());
        }

        if ($this->getFragment()) {
            $regex   = '/(?:[^' .'a-zA-Z0-9_\-\.~' .'!\$&\'\(\)\*\+,;=' .'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/';
            $uri .= "#" . preg_replace_callback($regex, $replace, $this->getFragment());
        }

        return $uri;
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
        $scheme = $this->_filterScheme((string) $scheme);

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
        $this->userInfo = (string) $userInfo;
        return $this;
    }

    /**
     * Retrieve the userInfo component of the URI
     * (usually user:password)
     *
     * The info syntax of the URI is:
     * [user-info@]host
     *
     * @return string|null
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
        $this->host = $this->_filterHost((string) $host);
        return $this;
    }

    /**
     * Get the URI host
     *
     * - The value returned MUST be normalized to lowercase
     *
     * @return string|null
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Set the URI host port
     *
     * @param int|null $port
     *
     * @return $this
     */
    function setPort($port)
    {
        if (empty($port) && $port !== 0)
            $port = null;
        elseif ($port < 1 || $port > 65535)
            throw new \InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));


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
     * @param bool $preserve Need Port Number Anyway
     *
     * @return null|int
     */
    function getPort($preserve = false)
    {
        $scheme = $this->getScheme();
        if (!$scheme)
            return $this->port;

        if ($this->port === null && $preserve)
            if (array_key_exists($scheme, self::$SCHEME))
                return self::$SCHEME[$scheme];

        return ($this->port)
            ? (
                (
                    array_key_exists($scheme, self::$SCHEME)
                    && $this->port === self::$SCHEME[$scheme]
                    && $preserve === false
                ) ? null : $this->port
            )
            : null;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    function getAuthority()
    {
        $uri = null;
        if ($this->getHost() !== null) {
            if ($this->getUserInfo())
                $uri .= $this->getUserInfo() . '@';

            $uri .= $this->getHost();
            if ($this->getPort())
                $uri .= ':' . $this->getPort();
        }
        
        return $uri;
    }
    
    /**
     * Set the query
     *
     * $resource when using as string
     * first=value&arr[]=foo+bar&arr[]=baz
     *
     * @param string|array|\Traversable $query
     *
     * @return $this
     */
    function setQuery($query)
    {
        $q = $this->getQuery();
        $q->with($q::parseWith($query));
        return $this;
    }

    /**
     * Get the URI query
     *
     * - entity setFrom query string,
     * - later: set query string as resource on entity object
     *
     * @return iDataQueryParams
     */
    function getQuery()
    {
        if (!$this->query)
            $this->query = new DataQueryParams;

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
        $this->fragment = (string) $fragment;
        return $this;
    }

    /**
     * Get the URI fragment
     *
     * @return string|null
     */
    function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        return ($this->getScheme() !== null);
    }
    

    // ..

    /**
     * Filter Scheme
     * @param string $scheme
     * @return string
     */
    function _filterScheme($scheme)
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);
        return $scheme;
    }

    /**
     * Filter Host
     * @param string $host
     * @return string
     */
    function _filterHost($host)
    {
        $host = strtolower($host);
        return $host;
    }


    function __clone()
    {
        (!$this->query) ?: $this->query = clone $this->query;
    }
}
