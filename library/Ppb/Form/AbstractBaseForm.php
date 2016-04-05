<?php

/**
 *
 * PHP Pro Bid $Id$ LNo18MhxSv07b6Z+qVsWgdJ2Z0qw2qarlhESrJ4dkww=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Ppb\Form;

use Cube\Form,
    Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Cube\Form\Element\Csrf,
    Cube\Form\Element as CubeElement,
    Ppb\Db\Table\Row\User as UserModel;

abstract class AbstractBaseForm extends Form
{
    /**
     * global element id
     */

    const EL_GLOBAL = 'global';

    /**
     *
     * included form ids
     *
     * @var array
     */
    protected $_includedForms = array(
        self::EL_GLOBAL);

    /**
     *
     * submit buttons - overridden by child methods
     *
     * @var array
     */
    protected $_buttons = array();

    /**
     *
     * edit form id
     *
     * @var int
     */
    protected $_editId = null;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * logged in user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * display form elements subtitles
     *
     * @var bool
     */
    protected $_displaySubtitles = true;

    /**
     *
     * set included forms array
     *
     * @param array $forms
     *
     * @return $this
     */
    public function setIncludedForms(array $forms)
    {
        $this->_includedForms = $forms;

        return $this;
    }


    /**
     *
     * get settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (!is_array($this->_settings)) {
            $this->setSettings(
                Front::getInstance()->getBootstrap()->getResource('settings'));
        }

        return $this->_settings;
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * get user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');

            if ($user instanceof UserModel) {
                $this->setUser($user);
            }
        }

        return $this->_user;
    }

    /**
     *
     * set user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get display subtitles flag
     *
     * @return boolean
     */
    public function isDisplaySubtitles()
    {
        return $this->_displaySubtitles;
    }

    /**
     *
     * set display subtitles flag
     *
     * @param boolean $displaySubtitles
     */
    public function setDisplaySubtitles($displaySubtitles)
    {
        $this->_displaySubtitles = $displaySubtitles;
    }


    /**
     *
     * check if one of the form submit buttons has been clicked
     * if so, submit the form/subform
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool
     */
    public function isPost(AbstractRequest $request)
    {
        foreach ($this->_buttons as $key => $value) {
            $button = $request->getParam($key);
            if (isset($button)) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * method to create a form element from an array
     *
     * @param array $elements
     * @param bool  $allElements
     * @param bool  $clearElements
     *
     * @return $this
     */
    public function addElements(array $elements, $allElements = false, $clearElements = true)
    {
        if ($clearElements) {
            $this->clearElements();

            $this->addElement(new Csrf());
        }

        foreach ($elements as $element) {
            $formId = (isset($element['form_id'])) ? $element['form_id'] : self::EL_GLOBAL;

            if (array_intersect((array)$formId, $this->_includedForms) || $allElements === true) {
                $formElement = $this->createElementFromArray($element);
                if ($formElement !== null) {
                    $this->addElement($formElement);
                }
            }
        }

        $this->generateEditForm();

        return $this;
    }

    /**
     *
     * create an element object from an array
     *
     * @param array $element
     *
     * @return \Cube\Form\Element|null
     */
    public function createElementFromArray(array $element)
    {
        if ($element['element'] !== false) {
            $formElement = $this->createElement($element['element'], $element['id']);

            foreach ($element as $method => $params) {
                $methodName = 'set' . ucfirst($method);
                if (method_exists($formElement, $methodName) && !empty($element[$method])) {
                    $formElement->$methodName(
                        $this->_prepareData($params));
                }
            }

            return $formElement;
        }

        return null;
    }


    /**
     *
     * add a string element to the form
     * overwrites an element with the same name
     *
     * @param \Cube\Form\Element $element
     *
     * @return $this
     */
    public function addElement(CubeElement $element)
    {
        if (!$this->isDisplaySubtitles()) {
            $element->clearSubtitle();
        }

        return parent::addElement($element);
    }

    /**
     *
     * create a submit button
     *
     * @param string $value element value
     * @param string $name  element name
     *
     * @return $this
     */
    public function addSubmitElement($value = null, $name = null)
    {
        if ($value === null) {
            $value = $this->getTranslate()->_('Proceed');
        }
        else {
            $value = $this->getTranslate()->_($value);
        }

        if ($name === null) {
            $name = 'submit';
        }

        /* submit button */
        $element = $this->createElement('submit', $name)
            ->setAttributes(array(
                'class' => 'btn btn-primary btn-lg',
            ))
            ->setValue($value);

        $this->addElement($element);

        return $this;
    }

    /**
     *
     * prepare serialized data and return it as an array which can be used by the class methods
     *
     * @param mixed $data
     * @param bool  $raw if true, do not combine multi key value fields as key => value
     *
     * @return array
     */
    protected function _prepareData($data, $raw = false)
    {
        if (!is_array($data)) {
            $array = \Ppb\Utility::unserialize($data);

            if ($array === $data) {
                return $data;
            }

            if ($raw === true) {
                return $array;
            }

            $keys = (isset($array['key'])) ? array_values($array['key']) : array();
            $values = (isset($array['value'])) ? array_values($array['value']) : array();

            return array_filter(
                array_combine($keys, $values));
        }

        return $data;
    }

    /**
     *
     * will generate the edit listing form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        if ($id !== null) {
            $this->_editId = $id;
        }

        return $this;
    }

    /**
     *
     * set the data for the form, and also convert any serialized values to array
     *
     * @param array $data form data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->_prepareData($value, true);
        }

        parent::setData($data);

        return $this;
    }
}

