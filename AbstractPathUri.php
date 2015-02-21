<?php
namespace Poirot\PathUri;

use Poirot\Core\BuilderSetterTrait;
use Poirot\PathUri\Interfaces\iPathUri;

abstract class AbstractPathUri
    implements iPathUri
{
    use BuilderSetterTrait {
        setupFromArray as protected __fromArray;
    }

    private $__reseting;

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
     * - reset object current parts
     *
     * note: it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iPathUri*/ $path)
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
     * Build Object From Array
     *
     * - reset object current parts
     *
     * @param array $arrPath
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromArray(array $arrPath)
    {
        if (!$this->__reseting) // recursive fromArray call on reseting
            $this->reset();

        $this->__fromArray($arrPath);

        return $this;
    }

    /**
     * Reset parts
     *
     * @return $this
     */
    function reset()
    {
        $this->__reseting = true; // recursive fromArray call on reseting

        $arrCp = $this->toArray();
        foreach($arrCp as $key => &$val)
            $val = null;

        $this->fromArray($arrCp);

        $this->__reseting = false;

        return $this;
    }
}
 