<?php
namespace Poirot\UriPath;

use Poirot\Core\BuilderSetterTrait;

abstract class AbstractPathUri
    implements iPathUri
{
    use BuilderSetterTrait {
        setupFromArray as fromArray;
    }

    /**
     * Construct
     *
     * @param array|string|iPathUri $pathUri
     * @throws \Exception
     */
    function __construct($pathUri = null)
    {
        if ($pathUri instanceof iPathUri)
            $pathUri = $pathUri->toArray();

        if ($pathUri !== null) {
            if (is_string($pathUri))
                $this->fromString($pathUri);
            elseif (is_array($pathUri))
                $this->fromArray($pathUri);
            else
                throw new \Exception(sprintf(
                    'PathUri must be instanceof iPathUri, Array or String, given: %s'
                    , is_object($pathUri) ? get_class($pathUri) : gettype($pathUri)
                ));
        }
    }
}
 