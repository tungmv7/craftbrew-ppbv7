<?php

/**
 *
 * Cube Framework $Id$ A7pzixVmO8WQIZ0y53oGkYZmaFLj6SuzT2XGQ28qZIM=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * config object
 */

namespace Cube\Config;

abstract class AbstractConfig
{

    /**
     *
     * the data that the config object holds
     *
     * @var mixed
     */
    protected $_data;

    /**
     *
     * a node of the config array
     *
     * @var string
     */
    protected $_node;

    /**
     *
     * class constructor
     * accepts a file, the location of the config file, and a node, in case we want to only hold a
     * part of the configuration array in the config object
     *
     * @param string $data
     * @param string $node
     */
    public function __construct($data = null, $node = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $this->setNode($node);
    }

    /**
     *
     * get the data held in the config container as an array
     * if a node is not found, return an empty array rather than the whole data array
     *
     * @param string $node
     *
     * @return array
     */
    public function getData($node = null)
    {
        if ($node !== null) {
            $this->setNode($node);
        }

        if ($this->_node !== null) {

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($this->_data),
                \RecursiveIteratorIterator::SELF_FIRST);


            foreach ($iterator as $key => $array) {
                if ($key == $this->_node) {
                    return $array;
                }
            }

            return array();
        }

        return (array)$this->_data;
    }

    /**
     *
     * set the config object data
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->clearData();
        $this->addData($data);

        return $this;
    }

    /**
     *
     * clear data
     *
     * @return $this
     */
    public function clearData()
    {
        $this->_data = array();

        return $this;
    }

    /**
     *
     * get the node name
     *
     * @return string
     */
    public function getNode()
    {
        return $this->_node;
    }

    /**
     *
     * set the node for the config array
     *
     * @param string $node
     */
    public function setNode($node)
    {
        $this->_node = $node;
    }

    /**
     *
     * add data
     *
     * @param array $data
     *
     * @return $this
     */
    abstract public function addData($data);

}

