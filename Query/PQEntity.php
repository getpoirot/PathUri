<?php
namespace Poirot\PathUri\Query;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\iPoirotEntity;
use Poirot\PathUri\Interfaces\iPQueryEntity;

class PQEntity extends Entity
    implements iPQueryEntity
{
    /**
     * @override
     *
     * @param string|array|iPoirotEntity $resource
     *
     * @return iPoirotEntity
     */
    function __setFrom($resource)
    {
        $this->__validateProps($resource);

        if (is_string($resource))
            parse_str($resource, $resource);

        return $resource;
    }

    protected function __validateProps($props)
    {
        if (!is_array($props) && !is_string($props) && !$props instanceof iPoirotEntity)
            throw new \Exception(sprintf(
                'Properties must instance of Entity or Array or string but "%s" given.'
                , is_object($props) ? get_class($props) : gettype($props)
            ));
    }

    /**
     * Represent query string from attributes
     *
     * @return string
     */
    function toString()
    {
        return http_build_query($this->getAs(new Entity));
    }
}
