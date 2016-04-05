<?php

/**
 *
 * Cube Framework $Id$ Lx3T6UbCLnXABRKefRarsTx6MyJN+3VbgYjuQtqycSI=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */

namespace Cube\Navigation\Page;

use Cube\Navigation\AbstractContainer,
    Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Permissions;

abstract class AbstractPage extends AbstractContainer
{

    const URI_DELIMITER = '/';

    /**
     *
     * page label
     *
     * @var string
     */
    protected $_label;

    /**
     *
     * page id
     *
     * @var string
     */
    protected $_id;

    /**
     *
     * page order
     *
     * @var int
     */
    protected $_order = null;

    /**
     *
     * active page flag
     *
     * @var bool
     */
    protected $_active;

    /**
     *
     * ACL resource associated with the page
     *
     * @var \Cube\Permissions\ResourceInterface
     */
    protected $_resource;

    /**
     *
     * ACL privilege associated with the page
     *
     * @var string|null
     */
    protected $_privilege;

    /**
     *
     * custom page attributes (eg: icon)
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     *
     * the parent of the page
     *
     * @var \Cube\Navigation\AbstractContainer
     */
    protected $_parent;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * creates a new page object
     * to create MVC pages, at least one of the 'controller', 'action' or 'params' keys needs to be set
     *
     * @param array $options
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \RuntimeException
     */
    public static function factory($options = null)
    {
        if (isset($options['className'])) {
            $className = $options['className'];
            unset($options['className']);

            if (class_exists($className)) {
                $page = new $className($options);

                if (!$page instanceof AbstractPage) {
                    throw new \RuntimeException("The page object must be an instance of \Cube\Navigation\Page\AbstractPage.");
                }
                else {
                    return $page;
                }
            }
            else {
                throw new \RuntimeException(
                    printf("Could not create page object. Class '%s' does not exist.", $options['type']));
            }
        }
        else if (isset($options['uri'])) {
            return new Uri($options);
        }
        else if (isset($options['controller']) || isset($options['action']) || isset($options['params'])) {
            return new Mvc($options);
        }
        else {
            return false;
////            throw new \InvalidArgumentException(
////                    sprintf("Could not create navigation page, the 'uri' or
////                        'controller' and 'action' parameters are required to
////                        create a page object, provided: %s.", serialize($options)));
        }
    }

    /**
     *
     * class constructor
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     *
     * get page label
     *
     * @return string
     */
    public function getLabel()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_label);
        }

        return $this->_label;
    }

    /**
     *
     * set page label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    /**
     *
     * get page id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     * set page id
     *
     * @param string $id
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \InvalidArgumentException
     */
    public function setId($id = null)
    {
        if (!is_string($id) && !is_numeric($id) && $id === null) {
            throw new \InvalidArgumentException(
                sprintf("The page id must be a string, numeric or null, %s given.", gettype($id)));
        }

        $this->_id = $id;

        return $this;
    }

    /**
     *
     * get page order in pages array
     *
     * @return string|null
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     *
     * set page order
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->_order = $order;
    }

    /**
     *
     * get page active status
     * if recursive, check children as well and if a child is active then the page is active as well
     *
     * @param bool $recursive
     *
     * @return bool
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active && $recursive) {
            /** @var \Cube\Navigation\Page\AbstractPage $page */
            foreach ($this->_pages as $page) {
                if ($page->isActive(true)) {
                    return true;
                }
            }

            return false;
        }

        return $this->_active;
    }

    /**
     *
     * set active status
     *
     * @param bool $active
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setActive($active = true)
    {
        $this->_active = (bool)$active;

        return $this;
    }

    /**
     *
     * get ACL resource corresponding to the page
     *
     * @return \Cube\Permissions\ResourceInterface
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     *
     * set ACL resource
     *
     * @param \Cube\Permissions\ResourceInterface $resource
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setResource($resource)
    {
        if ($resource instanceof Permissions\ResourceInterface) {
            $this->_resource = $resource;
        }
        else if (is_string($resource)) {
            $this->_resource = new Permissions\Resource($resource);
        }


        return $this;
    }

    /**
     *
     * get ACL privilege corresponding to this page
     *
     * @return string|null
     */
    public function getPrivilege()
    {
        return $this->_privilege;
    }

    /**
     *
     * set ACL privilege
     *
     * @param string $privilege
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setPrivilege($privilege)
    {
        $this->_privilege = is_string($privilege) ? $privilege : null;

        return $this;
    }

    /**
     *
     * get page attributes (custom tags)
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     *
     * set page attributes
     *
     * @param array $attributes
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;

        return $this;
    }

    /**
     *
     * get parent container
     *
     * @return \Cube\Navigation\AbstractContainer|null  parent container or null
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     *
     * set parent container
     *
     * @param \Cube\Navigation\AbstractContainer $parent
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setParent(AbstractContainer $parent = null)
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('A page cannot have itself as a parent');
        }

        // return if the given parent already is parent
        if ($parent === $this->_parent) {
            return $this;
        }

        // set new parent
        $this->_parent = $parent;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * return a unique hash code for the object
     *
     * @return string
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     *
     * get magic method, enables <code> echo $page->name </code>
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }

        return null;
    }

    /**
     *
     * set page attributes (magic method): enables <code>$page->name = $value</code>
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if (method_exists($this, $method)) {
            $this->$method($value);
        }
        else {
            $this->_attributes[$name] = $value;
        }

        return $this;
    }

    /**
     *
     * set options array as $page->name = $value
     *
     * @param array $options
     *
     * @return \Cube\Navigation\Page\AbstractPage
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     *
     * get magic method, proxy to $this->get($name)
     *
     * @param string $name
     *
     * @return string|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     *
     * set magic method, proxy for $this->set($name, $value) method
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     *
     * isset magic method, enables <code>isset($this->name)</code>
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return true;
        }

        return isset($this->_attributes[$name]);
    }

    /**
     * to string magic method, returns page label, enables <code>echo $page</code>
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_label;
    }

}

