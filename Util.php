<?php
namespace Poirot\PathUri;

class Util 
{
    /**
     * Fix common problems with a file path
     *
     * @param string $path
     * @param string $separator
     * @param bool   $stripTrailingSlash
     *
     * @return string
     */
    static function normalizeUnixPath($path, $separator = '/', $stripTrailingSlash = true)
    {
        if ($path == '')
            return $path;

        // remove sequences of slashes
        $path = preg_replace('#'.$separator.'{2,}#', $separator, $path);

        //remove trailing slash
        if ($stripTrailingSlash
            && strlen($path) > 1
            && substr($path, -1, 1) === $separator
        )
            $path = substr($path, 0, -1);

        return $path;
    }
}
 