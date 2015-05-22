<?php
namespace Poirot\PathUri\Interfaces;

use Poirot\Core\Interfaces\iPoirotEntity;

interface iPQueryEntity extends iPoirotEntity
{
    /**
     * Represent query string from attributes
     *
     * @return string
     */
    function toString();
}
