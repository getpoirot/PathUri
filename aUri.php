<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iUriBase;
use Poirot\Std\ConfigurableSetter;

abstract class aUri
    extends ConfigurableSetter
    implements iUriBase
{
    /**
     * Parse path string to parts in associateArray
     * @param string $stringPath
     * @return mixed
     */
    abstract function doParseFromString($stringPath);
    
    /**
     * Build Path From Given Resources
     *
     * [code:]
     *   $uri->with($uri::withOf('this://is/path/to/parse'))
     * [code]
     *
     * @param array|mixed $optionsResource
     * @param array       $_
     *        usually pass as argument into ::with if self instanced
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    final static function parseWith($optionsResource, array $_ = null)
    {
        if (!static::isConfigurableWith($optionsResource))
            throw new \InvalidArgumentException(sprintf(
                'Resource must be an array or string, given: (%s).'
                , \Poirot\Std\flatten($optionsResource)
            ));

        $self = new static;
        (empty($_)) ?: $self->with($_);
        
        if (is_string($optionsResource))
            $optionsResource = $self->doParseFromString($optionsResource);

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
        return is_array($optionsResource) || is_string($optionsResource);
    }
    
    
    // ..
    
    function __toString()
    {
        return $this->toString();
    }
}
