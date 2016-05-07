<?php
namespace Poirot\PathUri\Interfaces;

use Poirot\Std\Interfaces\Struct\iData;

interface iDataQueryParams 
    extends iData
{
    /**
     * Represent query string from attributes
     *
     * @return string
     */
    function toString();
}
