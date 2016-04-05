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
/**
 * creates a cache resource
 */

namespace Cube\Application\Resource;

use Cube\Cache as CacheObject;

class Cache extends AbstractResource
{

    /**
     *
     * cache object
     * 
     * @var \Cube\Cache 
     */
    protected $_cache;

    /**
     *
     * initialize translate object
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Cache
     */
    public function init()
    {
        if (!($this->_cache instanceof CacheObject)) {
            if (!isset($this->_options['cache']['folder'])) {
                throw new \InvalidArgumentException("A cache folder is required for creating a Cache resource.");
            }

            $this->_cache = CacheObject::getInstance($this->_options['cache']);
        }

        return $this->_cache;
    }

}

