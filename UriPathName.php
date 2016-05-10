<?php
namespace Poirot\PathUri;

use Poirot\PathUri\Interfaces\iUriPathName;

class UriPathName
    extends UriSequence
    implements iUriPathName
{
    protected $filename;
    protected $extension;
    
    /**
     * Parse path string to parts in associateArray
     *
     * @param string $stringPath
     *
     * @return mixed
     */
    protected function doParseFromString($stringPath)
    {
        #! Parse string may give:
        #- . and .. in some cases
        #- when you iterate over directory lists

        ## reset everything
        $parsed = array(
            'path'      => '',
            'filename'  => '',
            'extension' => '',
        );

        $stringPath = str_replace('\\', '/', $stringPath);
        if ( substr($stringPath, -1) == $this->getSeparator() )
            ## trick to have "/path/to/directory/" considered as directory
            #- back slash on end of string
            $stringPath .= '.';


        $m          = pathinfo($stringPath);

        if ($m['basename'] == '.' || $m['basename'] == '..') {
            $m['basename'] = '';
            $m['filename'] = '';
        }

        (!isset($m['dirname']))   ?: $parsed['path']      = $m['dirname'];
        (!isset($m['filename']))  ?: $parsed['filename']  = $m['filename'];
        (!isset($m['extension'])) ?: $parsed['extension'] = $m['extension'];

        if (isset($parsed['extension']) && $m['filename'] === '') {
            ## for directories similar to .ssh
            $parsed['extension'] = '';
            $parsed['filename']  = $m['basename'];
        }

        if ($parsed['path'] === '.')
            ## no path to uri detected, happen when "file.ext" given
            $parsed['path'] = '';

        $parsed = array_merge($parsed, parent::doParseFromString($parsed['path']));
        return $parsed;
    }
    
    /**
     * Get Array In Form Of PathInfo
     *
     * @return array
     */
    function toArray()
    {
        return array(
            'path'      => $this->getPath(),
            'filename'  => $this->getFilename(),
            'extension' => $this->getExtension(),

            'basename'  => $this->getFilename(),
        );
    }

    /**
     * Get Assembled Path As String
     *
     * - don`t call normalize path inside this method
     *   normalizing does happen when needed by call
     *   ::normalize
     *
     * @return string
     */
    function toString()
    {
        $parts = array();
        if ( ($r = parent::toString()) !== '' )
            ## avoid double slash trail issue
            $parts[] = ($r == $this->getSeparator()) ? '' : $r;
        if ( ($r = $this->getBasename()) !== '')
            $parts[] = $r;

        return implode($this->getSeparator(), $parts);
    }


    // Options:
    
    /**
     * Set Filename of file or folder
     *
     * ! without extension
     *
     * - /path/to/filename[.ext]
     * - /path/to/folderName/
     *
     * @param string $name Basename
     *
     * @return $this
     */
    function setFilename($name)
    {
        $this->filename = (string) $name;
        return $this;
    }

    /**
     * Gets the file name of the file
     *
     * - Without extension on files
     *
     * @return string
     */
    function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the file extension
     *
     * ! throw exception if file is lock
     *
     * @param string|null $ext File Extension
     *
     * @return $this
     */
    function setExtension($ext)
    {
        $this->extension = (string) $ext;
        return $this;
    }

    /**
     * Gets the file extension
     *
     * @return string
     */
    function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get Filename Include File Extension
     *
     * ! It's a combination of basename+'.'.extension
     *   combined with a dot
     *
     * @return string
     */
    function getBasename()
    {
        $filename  = $this->getFilename();
        $extension = $this->getExtension();

        return ($extension === '' || $extension === null)
            ? $filename
            : $filename.'.'.$extension;
    }
}
