<?php

/**
 *
 * Cube Framework $Id$ SdWeN7ltQH+Ubt2yL3xmRgOuphKdTWjdkc/otUnWpl8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */

namespace Cube;

class Cache
{

    /**
     *
     * returns an instance of the object and creates it if it wasn't instantiated yet
     *
     * @return \Cube\Cache
     */
    private static $_instance;

    /**
     *
     * number of seconds after a cache file expires
     *
     * @var integer
     */
    protected $_expires = 86400; //24 hours

    /**
     *
     * cache folder
     *
     * @var string
     */
    protected $_folder;

    /**
     *
     * whether to serialize data upon read and write
     *
     * @var bool
     */
    protected $_serialization = true;

    /**
     *
     * whether to cache select queries
     *
     * @var bool
     */
    protected $_cacheQueries = false;

    /**
     *
     * whether to cache table metadatas
     *
     * @var bool
     */
    protected $_cacheMetadata = true;

    /**
     *
     * class constructor
     *
     * @param array $options configuration array
     * @throws \RuntimeException
     */
    protected function __construct($options = array())
    {
        if (!isset($options['folder'])) {
            throw new \RuntimeException("Cache folder not specified.");
        }

        if (isset($options['queries'])) {
            $this->setCacheQueries($options['queries']);
        }

        if (isset($options['metadata'])) {
            $this->setCacheMetadata($options['metadata']);
        }

        $this->_folder = $options['folder'];
    }

    /**
     *
     * initialize application as singleton
     *
     * @param array $options configuration array
     * @return \Cube\Cache
     */
    public static function getInstance($options = array())
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($options);
        }

        return self::$_instance;
    }

    /**
     *
     * get expiration time
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->_expires;
    }

    /**
     *
     * set expiration time
     *
     * @param int $expires
     * @return \Cube\Cache
     */
    public function setExpires($expires)
    {
        $this->_expires = (int)$expires;

        return $this;
    }

    /**
     *
     * get cache folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->_folder;
    }

    /**
     *
     * set cache folder
     *
     * @param string $folder
     * @return \Cube\Cache
     * @throws \InvalidArgumentException
     */
    public function setFolder($folder)
    {
        if (!file_exists($folder)) {
            throw new \InvalidArgumentException(
                sprintf("The cache folder '%s' could not be found.", $folder));
        }

        $this->_folder = $folder;

        return $this;
    }

    /**
     *
     * get serialization flag
     *
     * @return bool
     */
    public function getSerialization()
    {
        return $this->_serialization;
    }

    /**
     *
     * set serialization flag
     *
     * @param bool $serialization
     * @return \Cube\Cache
     */
    public function setSerialization($serialization)
    {
        $this->_serialization = (bool)$serialization;

        return $this;
    }

    /**
     *
     * get cache queries flag
     *
     * @return bool
     */
    public function getCacheQueries()
    {
        return $this->_cacheQueries;
    }

    /**
     *
     * set cache queries flag
     *
     * @param bool $cacheQueries
     * @return \Cube\Cache
     */
    public function setCacheQueries($cacheQueries)
    {
        $this->_cacheQueries = $cacheQueries;

        return $this;
    }

    /**
     *
     * get cache metadata flag
     *
     * @return bool
     */
    public function getCacheMetadata()
    {
        return $this->_cacheMetadata;
    }

    /**
     *
     * set cache metadata flag
     *
     * @param bool $cacheMetadata
     * @return \Cube\Cache
     */
    public function setCacheMetadata($cacheMetadata)
    {
        $this->_cacheMetadata = (bool)$cacheMetadata;

        return $this;
    }

    /**
     *
     * reads the contents of a cache file and returns the output or false if the file could not be found
     *
     * @param string $file
     * @return string|false
     */
    public function read($file)
    {
        if (file_exists($file)) {
            $cacheFile = $file;
        } else if (file_exists($this->_folder . DIRECTORY_SEPARATOR . $file)) {
            $cacheFile = $this->_folder . DIRECTORY_SEPARATOR . $file;
        } else {
            return false;
        }

        if (filemtime($cacheFile) > (time() - $this->_expires)) {
            $contents = file_get_contents($cacheFile);

            return ($this->_serialization === true) ? unserialize($contents) : $contents;
        } else {
            unlink($cacheFile);

            return false;
        }
    }

    /**
     *
     * create/update cache file
     *
     * @param string $file
     * @param mixed  $data
     * @return \Cube\Cache
     * @throws \RuntimeException
     */
    public function write($file, $data)
    {
        $cacheFile = $this->_folder . DIRECTORY_SEPARATOR . $file;

        if (!$fp = fopen($cacheFile, 'w')) {
            throw new \RuntimeException(
                sprintf("Could not open cache file '%s'.", $file));
        }

        if (!flock($fp, LOCK_EX)) {
            throw new \RuntimeException(
                sprintf("Could not lock cache file '%s'.", $file));
        }

        if ($this->_serialization === true) {
            $data = serialize($data);
        }

        if (!fwrite($fp, $data)) {
            throw new \RuntimeException(
                sprintf("Could not write to cache file '%s'.", $file));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $this;
    }

}

