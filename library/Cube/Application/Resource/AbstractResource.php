<?php

/**
 *
 * Cube Framework $Id$ CLUYYa1R6DNcxICakNQroQp+sgzpGB1fSDSflrJBdfc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * abstract application resources class
 */

namespace Cube\Application\Resource;

abstract class AbstractResource implements ResourceInterface
{

    /**
     *
     * array of settings for a certain resource
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * get options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * set options array
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        if (is_array($this->_options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        else {
            $this->_options = $options;
        }

        return $this;
    }

    /**
     *
     * get a key from the options array
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getOption($key)
    {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return null;
    }

    /**
     *
     * set or unset a key in the options array
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setOption($key, $value = null)
    {
        if ($value === null && isset($this->_options[$key])) {
            unset($this->_options[$key]);
        }
        else {
            $this->_options[$key] = $value;
        }

        return $this;
    }

}

