<?php
namespace Poirot\PathUri\Http;

use Poirot\PathUri\Interfaces\iDataQueryParams;
use Poirot\Std\Interfaces\Pact\ipConfigurable;
use Poirot\Std\Struct\DataEntity;

class DataQueryParams 
    extends DataEntity
    implements iDataQueryParams
{
    /**
     * Represent query string from attributes
     *
     * @return string
     */
    function toString()
    {
        $arr = \Poirot\Std\cast($this)->toArray();
        return http_build_query($arr);
    }

    /**
     * Build Object With Provided Options
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @throws \Exception
     * @return $this
     */
    function with(array $options, $throwException = false)
    {
        $this->import($options);
        return $this;
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     *
     * @param array|mixed $optionsResource
     * @param array $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        if (!static::isConfigurableWith($optionsResource))
            throw new \InvalidArgumentException(sprintf(
                'Resource must be an array, Traversable or string, given: (%s).'
                , \Poirot\Std\flatten($optionsResource)
            ));


        if (is_string($optionsResource))
            parse_str($optionsResource, $optionsResource);

        return $optionsResource;
    }

    /**
     * Is Configurable With Given Resource
     *
     * @param mixed $optionsResource
     *
     * @return boolean
     */
    static function isConfigurableWith($optionsResource)
    {
        return $optionsResource instanceof \Traversable || is_array($optionsResource) || is_string($optionsResource);
    }
}
