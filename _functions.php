<?php
namespace Poirot\PathUri;

/**
 * Fix common problems with a file path
 *
 * @param string $path
 * @param string $separator
 * @param bool   $stripTrailingSlash
 *
 * @return string
 */
function normalizeUnixPath($path, $separator = '/', $stripTrailingSlash = true)
{
    if ($path == '')
        return $path;

    $path = str_replace('\\', $separator, $path);

    // remove sequences of slashes
    ##! has error warning on "/payam"
    $path = @preg_replace('#'.$separator.'{2,}#', $separator, $path);

    //remove trailing slash, /dir[/] not /
    if ($stripTrailingSlash
        && strlen($path) > 1
        && substr($path, -1, 1) === $separator
    )
        $path = substr($path, 0, -1);

    return $path;
}

function encodeUri($pathStr) 
{
    return preg_replace_callback(
        '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
        function (array $matches) {
            return rawurlencode($matches[0]);
        }
        , $pathStr
    );
}
