<?php
/**
 *
 * Cube Framework $Id$ nHimjbKPf4nL0iQpS/w6gZ09ZtvCRMvRXci0KCcL0bw=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.3
 */
/**
 * abstract feed class
 */
namespace Cube\Feed;


class Entry
{
    /**
     *
     * elements array
     *
     * @var array
     */
    protected $_elements = array();

    /**
     *
     * set elements
     *
     * @param array $elements
     *
     * @return $this
     */
    public function setElements($elements)
    {
        foreach ((array)$elements as $key => $value) {
            $this->addElement($key, $value);
        }

        return $this;
    }

    /**
     *
     * add single element to elements array
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addElement($key, $value)
    {
        $this->_elements[$key] = $this->_formatString($value);

        return $this;
    }

    /**
     *
     * get elements array
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     *
     * clear elements array
     *
     * @return $this
     */
    public function clearElements()
    {
        $this->_elements = array();

        return $this;
    }

    /**
     *
     * format string
     *
     * @param string $string
     *
     * @return string
     */
    protected function _formatString($string)
    {
        return strip_tags(
            str_ireplace(
                array('&'), array('&amp;'), $string));
    }


}