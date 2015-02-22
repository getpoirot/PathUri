<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iPathAbstractUri;
use Poirot\PathUri\Interfaces\iPathJoinedUri;

/**
 * note: string paths usually must be normalized from
 *       the class that used this
 */
class PathJoinUri extends PathAbstractUri
    implements iPathJoinedUri
{
    // From SetterTrait ... {
    //   used on fromArray, it will first set Separator

    /**
     * @var array List Setters By Priority
     * [
     *  'service_config',
     *  'listeners',
     *  // ...
     * ]
     *
     * application calls setter methods from top ...
     *
     */
    protected $__setup_array_priority = [
        'separator',
    ];
    // ... }

    protected $path = [];

    protected $separator = '/';


    // Used as a helper for fromArray setter ... {

    protected function getPath()
    {
        return $this->path;
    }

    /**
     * note: in case of string path using separator
     *       to build an array
     *
     * @param array|string $arrPath
     *
     * @return $this
     */
    protected function setPath($arrPath)
    {
        if (is_string($arrPath))
            $arrPath = $this->parse($arrPath);

        // the associate array is useless
        $this->path = array_values($arrPath);

        return $this;
    }

    // ... }

    /**
     * Build Object From String
     *
     * - parse string to associateArray setter
     *
     * @param string $pathStr
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    function parse($pathStr)
    {
        if (!is_string($pathStr))
            throw new \InvalidArgumentException(sprintf(
                'PathStr must be string but "%s" given.'
                , is_object($pathStr) ? get_class($pathStr) : gettype($pathStr)
            ));

        return explode($this->getSeparator(), $pathStr);
    }

    /**
     * Is Absolute Path?
     *
     * @return boolean
     */
    function isAbsolute()
    {
        $p = $this->getPath();

        reset($p);
        $fi = current($p);

        return $fi === '' || substr($fi, -1) === ':';
    }

    /**
     * Get Array In Form Of AssocArray
     *
     * return [
     *  'path'      => ['', 'absolute', 'path'],
     *  'separator' => '/',
     * ]
     *
     * @return array
     */
    function toArray()
    {
        return [
            'path'      => $this->getPath(),
            'separator' => $this->getSeparator()
        ];
    }

    /**
     * Get Assembled Path As String
     *
     * @return string
     */
    function toString()
    {
        $separator = $this->getSeparator();
        $return    = $separator . implode( $this->getSeparator(), $this->getPath() );

        return $return;
    }

    /**
     * Set Path Separator
     *
     * @param string $sep
     *
     * @return $this
     */
    function setSeparator($sep)
    {
        $this->separator = (string) $sep;

        return $this;
    }

    /**
     * Get Path Separator
     *
     * @return string
     */
    function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Append Path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function append($pathUri)
    {
        // TODO: Implement append() method.
    }

    /**
     * Prepend Path
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return $this
     */
    function prepend($pathUri)
    {
        // TODO: Implement prepend() method.
    }

    /**
     * Mask Given PathUri with Current Path
     * And Return New Object Of Difference
     *
     * /var/www/html <=> /var/www/ ===> /html
     *
     * @param iPathAbstractUri $pathUri
     *
     * @return iPathAbstractUri
     */
    function mask($pathUri)
    {
        // TODO: Implement mask() method.
    }
}
 