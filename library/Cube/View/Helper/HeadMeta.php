<?php

/**
 *
 * Cube Framework $Id$ tbkkpy6dJ04DV9YDDHMZBMGxNI5tIMY9cPA+hqhVR14=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.1
 */
/**
 * create head meta html tags
 */

namespace Cube\View\Helper;

class HeadMeta extends AbstractHelper
{

    /**
     * title operation types
     */
    const SET = 'set';
    const APPEND = 'append';
    const PREPEND = 'prepend';

    /**
     *
     * page container container
     *
     * @var array
     */
    protected $_container = array();

    /**
     *
     * head meta method
     * only a proxy to the class - all calls are done through the magic method
     *
     * @return $this
     */
    public function headMeta()
    {
        return $this;
    }

    /**
     *
     * call magic method, enables the calling of the following methods:
     * append|prepend - Name|HttpEquiv|Charset
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(pre|ap)pend)(?P<type>Name|HttpEquiv|Property|Charset)$/', $method, $matches)
        ) {
            $action = '_' . $matches['action'];
            $type = strtolower(
                preg_replace("/([a-z])([A-Z])/", '$1-$2', $matches['type']));

            $nbArgs = count($args);
            if ($nbArgs < 1 || $nbArgs > 2) {
                throw new \InvalidArgumentException("Invalid number of arguments given in headMeta function call.");
            }

            if (!isset($args[1])) {
                $args[1] = null;
            }

            $data = $this->_createData($type, $args[0], $args[1]);
            $this->$action($data);
        }

        return $this;
    }

    /**
     *
     * append data to meta tags container
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _append($data)
    {
        array_push($this->_container, $data);

        return $this;
    }

    /**
     *
     * prepend data to meta tags container
     *
     * @param string $data
     *
     * @return $this
     */
    protected function _prepend($data)
    {
        array_unshift($this->_container, $data);

        return $this;
    }

    /**
     *
     * set data to meta tags container - will first remove all tags that have the same key name
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _set($data)
    {
        foreach ($this->_container as $key => $item) {
            if (strcmp($item['value'], $data['value']) === 0 && strcmp($item['key'], $data['key']) === 0) {
                unset($this->_container[$key]);
            }
        }

        return $this->_append($data);
    }

    /**
     *
     * clear meta tags container
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
     * format data to add to the container
     *
     * @param string $keyName
     * @param string $keyValue
     * @param string $content
     *
     * @return array
     */
    protected function _createData($keyName, $keyValue, $content)
    {
        return array(
            'key'     => $keyName,
            'value'   => $keyValue,
            'content' => $content,
        );
    }

    /**
     *
     * to string magic method
     * only outputs meta tags with key/value/content if content is not empty
     *
     * enables <code>echo $this->headMeta(); </code>
     *
     * @return string
     */
    public function __toString()
    {
        $output = null;

        foreach ($this->_container as $item) {
            if (isset($item['content'])) {
                if (!empty($item['content'])) {
                    $output .= sprintf('<meta %s="%s" content="%s">', $item['key'], $item['value'],
                            str_ireplace('"', '', $item['content'])) . "\n";
                }
            }
            else {
                $output .= sprintf('<meta %s="%s">', $item['key'], $item['value']) . "\n";
            }
        }

        return strval($output);
    }

    /**
     *
     * normalize head meta type call
     *
     * @param string $type
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function _normalizeType($type)
    {
        switch ($type) {
            case 'Name':
                return 'name';
            case 'HttpEquiv':
                return 'http-equiv';
            default:
                throw new \InvalidArgumentException("Invalid headMeta type function called.");
        }
    }
}

