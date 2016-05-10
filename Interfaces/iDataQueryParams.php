<?php
namespace Poirot\PathUri\Interfaces;

use Poirot\Std\Interfaces\Pact\ipConfigurable;
use Poirot\Std\Interfaces\Struct\iData;

interface iDataQueryParams 
    extends iData
    , ipConfigurable
{
    /**
     * Represent query string from attributes
     *
     * @return string
     */
    function toString();
}
