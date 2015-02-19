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

    /**
     * Build Object From PathUri
     *
     * - it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathUri $path
     *
     * @return $this
     */
    function fromPathUri(/*iUriPath*/ $path)
    {
        if (!is_object($path) || ! $path instanceof $this)
            throw new \InvalidArgumentException(sprintf(
                'PathUri must be instanceof %s, given: %s'
                , get_class($this)
                , is_object($path) ? get_class($path) : gettype($path)
            ));

        $this->fromArray($path->toArray());

        return $this;
    }

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset()
    {
        $arrCp = $this->toArray();
        foreach($arrCp as $key => &$val)
            $val = null;

        $this->fromArray($arrCp);

        return $this;
    }
}
 