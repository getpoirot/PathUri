<?php
namespace Poirot\PathUri\Interfaces;

interface iHttpUri extends iSeqPathUri
{
    // Parse Getter/Setter Methods:

    /**
     * Get the scheme
     *
     * @return string|false
     */
    function getScheme();

    /**
     * Get the URI host
     *
     * @return string|false
     */
    function getHost();

    /**
     * Get the User-info
     * (usually user:password)
     *
     * @return string|false
     */
    function getUserInfo();

    
}
