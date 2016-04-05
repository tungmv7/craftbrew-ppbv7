<?php

/**
 *
 * Cube Framework $Id$ pTIYtU+hh8JF4PLpav5IzhK6JWQKNoS7Wau8HEi+3vM=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * create head title html tag
 */

namespace Cube\View\Helper;

class HeadTitle extends AbstractHelper
{

    /**
     * default separator
     */
    const DEFAULT_SEPARATOR = ' / ';

    /**
     * title operation types
     */
    const SET = 'set';
    const APPEND = 'append';
    const PREPEND = 'prepend';

    /**
     *
     * separator
     *
     * @var string
     */
    protected $_separator = self::DEFAULT_SEPARATOR;
    /**
     *
     * page container container
     *
     * @var array
     */
    protected $_container = array();

    /**
     *
     * head title method
     *
     * @param string $title
     * @param string $type
     *
     * @return $this
     */
    public function headTitle($title = null, $type = null)
    {
        if ($title !== null && !empty($title)) {
            switch ($type) {
                case self::PREPEND:
                    $this->prepend($title);
                    break;
                case self::SET:
                    $this->set($title);
                    break;

                default:
                    $this->append($title);
                    break;
            }
        }

        return $this;
    }

    /**
     *
     * append string to title container
     *
     * @param string $title
     *
     * @return $this
     */
    public function append($title)
    {
        array_push($this->_container, $title);

        return $this;
    }

    /**
     *
     * prepend string to title container
     *
     * @param string $title
     *
     * @return $this
     */
    public function prepend($title)
    {
        array_unshift($this->_container, $title);

        return $this;
    }

    /**
     *
     * set new title
     *
     * @param $title
     *
     * @return $this
     */
    public function set($title)
    {
        $this->clearContainer()
            ->append($title);

        return $this;
    }

    /**
     *
     * clear title container
     *
     * @return $this
     */
    public function clearContainer()
    {
        $this->_container = array();

        return $this;
    }

    /**
     *
     * set title separator
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;

        return $this;
    }

    /**
     *
     * get title separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }


    /**
     *
     * to string magic method
     *
     * enables <code>echo $this->headTitle(); </code>
     *
     * @return string
     */
    public function __toString()
    {
        return '<title>' . implode($this->_separator, $this->_container) . '</title>';
    }

}

