<?php
namespace Poirot\PathUri\Http;

use Poirot\PathUri\Interfaces\iDataQueryParams;
use Poirot\Std\Struct\DataEntity;

class DataQueryParams 
    extends DataEntity
    implements iDataQueryParams
{
    /**
     * @override
     *
     * Set Properties
     *
     * $resource when using as string
     * first=value&arr[]=foo+bar&arr[]=baz
     *
     * ! you can implement this method on subclasses
     *
     * @param EntityInterface|string $resource
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    function __setFrom($resource)
    {
        if (is_string($resource))
            parse_str($resource, $resource);

        return parent::__setFrom($resource);
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
