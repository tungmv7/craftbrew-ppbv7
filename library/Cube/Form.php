<?php

/**
 *
 * Cube Framework $Id$ bbzfTOqFPBqdTTzSo4aoJygN6ZUZNJiijNv9PiEaPoc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * form generator class
 */

namespace Cube;

use Cube\Form\Element,
    Cube\Validate,
    Cube\Controller\Front,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter;

class Form
{

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     *
     * the action of the form
     *
     * @var string
     */
    protected $_action;

    /**
     *
     * the method of the form
     *
     * @var string  method - get|post
     */
    protected $_method;

    /**
     *
     * form elements
     *
     * @var array
     */
    protected $_elements = array();

    /**
     *
     * data resulted from a previous form submit, used to pre-fill the elements
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * an array of validator messages resulted from the form validation method
     *
     * @var array
     */
    protected $_messages = array();

    /**
     *
     * the title of the form
     *
     * @var string
     */
    protected $_title;

    /**
     *
     * a description for the form
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * the view partial used to display the form
     *
     * @var string
     */
    protected $_partial;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     * @param bool   $csrf   set whether to add csrf validation to the form
     */
    public function __construct($action = null, $csrf = true)
    {
        $this->setAction($action);

        if ($csrf === true) {
            $this->addElement(new Element\Csrf());
        }
    }

    /**
     *
     * get form action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     *
     * set the action of the form
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->_action = $action;

        return $this;
    }

    /**
     *
     * get form method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     *
     * set the method of the form
     *
     * @param string $method get|post
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);

        if ($method !== self::METHOD_GET && $method !== self::METHOD_POST) {
            throw new \InvalidArgumentException(
                sprintf("Invalid form method provided, '%s'.", $method));
        }

        $this->_method = $method;

        return $this;
    }

    /**
     *
     * get form elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     *
     * return true if the form has elements, false otherwise
     *
     * @return bool
     */
    public function hasElements()
    {
        return (count($this->_elements) > 0) ? true : false;
    }

    /**
     *
     * get a single form element
     *
     * @param string $name
     *
     * @return \Cube\Form\Element
     * @throws \InvalidArgumentException
     */
    public function getElement($name)
    {
        if (isset($this->_elements[$name])) {
            return $this->_elements[$name];
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("The element with the name '%s' does not exist in the form.", $name));
        }
    }


    /**
     *
     * check if an element exists in the form
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasElement($name)
    {
        return (isset($this->_elements[$name])) ? true : false;
    }

    /**
     *
     * remove an element from the form
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeElement($name)
    {
        if (isset($this->_elements[$name])) {
            unset($this->_elements[$name]);
        }

        return $this;
    }

    /**
     *
     * remove all elements from the form
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
     * add elements to the form
     *
     * @param array $elements
     *
     * @return $this
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            $this->addElement($element);
        }

        return $this;
    }

    /**
     *
     * add a string element to the form
     * overwrites an element with the same name
     *
     * @param \Cube\Form\Element $element
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addElement(Element $element)
    {
        $this->_elements[(string)$element->getName()] = $element;

        return $this;
    }

    /**
     *
     * method to create a new form element
     *
     * @param string $element the element type
     * @param string $name    the name of the element
     *
     * @return \Cube\Form\Element    returns a form element object
     */
    public function createElement($element, $name)
    {
        $elementClass = '\\Cube\\Form\\Element\\' . ucfirst($element);

        if (class_exists($element)) {
            return new $element($name);
        }
        else if (class_exists($elementClass)) {
            return new $elementClass($name);
        }
        else {
            return new Element($element, $name);
        }
    }

    /**
     *
     * get data
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($key !== null) {
            if (!empty($this->_data[$key])) {
                return $this->_data[$key];
            }

            return null;
        }

        return $this->_data;
    }

    /**
     *
     * set the data of the submitted form,
     * plus set the submit data for each element in the form
     * the data array is filter on a per element basis
     *
     * @param array $data form data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->_data = $data;

        /* @var \Cube\Form\Element $element */
        foreach ($this->_elements as $element) {
            $elementName = $element->getName();

            // TODO: if element name contains brackets => bug
            if (array_key_exists($elementName, $this->_data)) {

                $element->setData($this->_data[$elementName]);
                $this->_data[$elementName] = $element->getValue();
            }

        }

        return $this;
    }

    /**
     *
     * get the title set for the form
     *
     * @return string
     */
    public function getTitle()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_title);
        }

        return $this->_title;
    }

    /**
     *
     * set a title for the form
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;

        return $this;
    }

    /**
     *
     * get the description set for the form
     *
     * @return string
     */
    public function getDescription()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_description);
        }

        return $this->_description;
    }

    /**
     *
     * set a description for the form
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->_description = (string)$description;

        return $this;
    }

    /**
     *
     * get the messages resulted from an isValid function
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     *
     * set multiple validation messages
     *
     * @param array $messages
     *
     * @return $this
     */
    public function setMessages(array $messages = null)
    {
        foreach ($messages as $message) {
            $this->setMessage($message);
        }

        return $this;
    }

    /**
     *
     * clear form validator messages
     *
     * @return $this
     */
    public function clearMessages()
    {
        $this->_messages = array();

        return $this;
    }

    /**
     *
     * add a new validation message, but only if the message is not empty
     * also translate the message if a translate adapter is available
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        if (!empty($message)) {
            $translate = $this->getTranslate();

            if (null !== $translate) {
                $message = $translate->_($message);
            }

            $this->_messages[] = $message;
        }
    }

    /**
     *
     * get the view object
     *
     * @return \Cube\View
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->setView();
        }

        return $this->_view;
    }

    /**
     * set the view object
     *
     * @param \Cube\View $view
     *
     * @return $this
     */
    public function setView(View $view = null)
    {
        if (!$view instanceof View) {
            $bootstrap = Front::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('view')) {
                $view = $bootstrap->getResource('view');
            }
            else {
                $view = new View();
            }
        }

        $this->_view = $view;

        return $this;
    }

    /**
     *
     * get the view file
     *
     * @return string
     */
    public function getPartial()
    {
        return $this->_partial;
    }

    /**
     *
     * set the view partial
     *
     * @param string $partial
     *
     * @return $this
     */
    public function setPartial($partial)
    {
        $this->_partial = $partial;

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
     * checks if the form is valid based on the validators entered for each element
     *
     * TODO: validator for multiple fields - should check in an array of values
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        /* @var \Cube\Form\Element $element */
        foreach ($this->_elements as $element) {
            $required = $element->getRequired();

            /**
             * in case we have an array in the required field, we will check
             * for the dependency first to see whether the field is required or not.
             */
            if (is_array($required)) {
                $required = ($this->_data[$required[0]] == $required[1]) ? $required[2] : !$required[2];
            }

            if ($required === true) {
                $element->addValidator(
                    new Validate\NotEmpty());
            }

            $valid = ($elementValid = $element->isValid()) ? $valid : false;

            if (!$elementValid) {
                $this->setMessages(
                    $element->getMessages());
            }
        }

        return (bool)$valid;
    }

    /**
     *
     * renders all hidden elements for the form
     * called in the view partial file, usable through the __get() magic method
     * Important: by default, multiple hidden elements are not rendered
     *
     * @param bool $multiple
     *
     * @return string
     */
    public function getHiddenElements($multiple = false)
    {
        $elements = null;
        /* @var \Cube\Form\Element $element */
        foreach ($this->_elements as $element) {
            if ($element->isHidden() && (!$element->getMultiple() || $multiple)) {
                $elements .= $element->render() . "\n";
            }
        }

        return $elements;
    }

    /**
     *
     * renders the form
     * if no action is set, use the request uri
     *
     * @return string               the formatted html
     */
    public function render()
    {
        $this->renderHeaderCode();
        $this->renderBodyCode();

        if (!$this->getAction()) {

            $request = Front::getInstance()->getRequest();
            $action = $request->getBaseUrl() . $request->getRequestUri();

            $this->setAction($action);
        }
        $view = $this->getView();

        $view->set('form', $this);

        return $view->process(
            $this->getPartial(), true);
    }

    /**
     *
     * when called, it will get the header code from all elements
     * and append it to the Script view helper
     *
     * @return string
     */
    public function renderHeaderCode()
    {
        /* @var \Cube\View\Helper\Script $helper */
        $helper = $this->getView()->getHelper('script');

        /* @var \Cube\Form\Element $element */
        foreach ($this->_elements as $element) {
            $elementHeaderCode = $element->getHeaderCode();

            foreach ($elementHeaderCode as $code) {
                $helper->addHeaderCode($code);
            }
        }

        return $this;
    }

    /**
     *
     * when called, it will get the body code from all elements
     * and append it to the Script view helper
     *
     * @return string
     */
    public function renderBodyCode()
    {
        /* @var \Cube\View\Helper\Script $helper */
        $helper = $this->getView()->getHelper('script');

        /* @var \Cube\Form\Element $element */
        foreach ($this->_elements as $element) {
            $elementBodyCode = $element->getBodyCode();

            foreach ($elementBodyCode as $code) {
                $helper->addBodyCode($code);
            }
        }

        return $this;
    }

    /**
     *
     * get magic method, enables <code> echo $form->name </code>
     * name will be the name of an element, and when called, it will render it
     *
     * @param string $name
     *
     * @return mixed|string                 the rendered element
     * @throws \InvalidArgumentException    an error is thrown if the element doesnt exist
     */
    public function get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else if (isset($this->_elements[$name])) {
            return $this->_elements[$name]->render();
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("The element with the name '%s' does not exist in the form.", $name));
        }
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
     * toString magic method, enabled <code> echo $form </code>
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $render = $this->render();
        } catch (\Exception $e) {
            $render = 'Form rendering error: ' . $e->getMessage();
        }

        return $render;
    }

}

