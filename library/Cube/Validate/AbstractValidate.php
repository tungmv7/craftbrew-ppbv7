<?php

/**
 *
 * Cube Framework $Id$ XmR3pYQMapKwFNJhNQY5sZxM2/5XCu+un1X+PZgIBA8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * validator abstract class
 */

namespace Cube\Validate;

use Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter;

abstract class AbstractValidate
{

    /**
     *
     * the message returned if the validator fails
     *
     * @var string
     */
    protected $_message;

    /**
     *
     * the name of the element to be checked
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * the variable to be validated
     *
     * @var mixed
     */
    protected $_value;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * get the validation error message
     *
     * @return string
     */
    public function getMessage()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_message);
        }

        return $this->_message;
    }

    /**
     *
     * set the validation error message that will be output
     *
     * @param string $message
     *
     * @return \Cube\Validate\AbstractValidate
     */
    public function setMessage($message)
    {
        $this->_message = (string)$message;

        return $this;
    }

    /**
     *
     * reset the message variable
     *
     * @return \Cube\Validate\AbstractValidate
     */
    public function resetMessage()
    {
        $this->_message = null;

        return $this;
    }

    /**
     *
     * get the name of the form element to be validated
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * set the name of the element to be validated
     *
     * @param string $name
     *
     * @return \Cube\Validate\AbstractValidate
     */
    public function setName($name)
    {
        $this->_name = (string)$name;

        return $this;
    }

    /**
     *
     * get the variable that needs to be validated
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     *
     * set the variable that needs to be validated
     *
     * @param mixed $value
     *
     * @return \Cube\Validate\AbstractValidate
     */
    public function setValue($value)
    {
        $this->_value = $value;

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
     * abstract method
     *
     * @return bool
     */
    abstract public function isValid();
}

