<?php
namespace Poirot\PathUri;

use Poirot\Core\BuilderSetterTrait;
use Poirot\PathUri\Interfaces\iPathAbstractUri;

abstract class PathAbstractUri
    implements iPathAbstractUri
{
    use BuilderSetterTrait {
        setupFromArray as protected __fromArray;
    }

    private $__reseting;

    /**
     * Create a new URI object
     *
     * @param  iPathAbstractUri|string|array $pathUri
     *
     * @throws \InvalidArgumentException
     */
    function __construct($pathUri = null)
    {
        if (is_object($pathUri)) {
            if (!$pathUri instanceof $this)
                throw new \InvalidArgumentException(sprintf(
                    'PathUri must be instanceof "%s" but "%s" given.'
                    , get_class($this)
                    , get_class($pathUri)
                ));

            $pathUri = $pathUri->toArray();
        }

        if ($pathUri !== null) {
            if (is_string($pathUri))
                $pathUri = $this->parse($pathUri);

            if (is_array($pathUri))
                $this->fromArray($pathUri);
            else
                throw new \InvalidArgumentException(sprintf(
                    'PathUri must be instanceof iPathUri, Array or String, given: %s'
                    , is_object($pathUri) ? get_class($pathUri) : gettype($pathUri)
                ));
        }
    }

    /**
     * Build Object From PathUri
     *
     * note: it take a instance of pathUri object
     *   same as base object
     *
     * @param iPathAbstractUri $path
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromPathUri(/*iPathAbstractUri*/ $path)
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
     * @param array $arrPath
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromArray(array $arrPath)
    {
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
 