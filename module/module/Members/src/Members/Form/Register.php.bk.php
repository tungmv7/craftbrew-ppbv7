<?php

/**
 *
 * PHP Pro Bid $Id$ DM2dvK9pPdkLnLXM6N5yk0YiM+E272ZWbyfp512HvcM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * members module registration form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Model\Elements;

class Register extends AbstractBaseForm
{
    /**
     *
     * search elements model
     *
     * @var \Ppb\Model\Elements\User
     */
    protected $_model;

    public function __construct($formId = null, $action = null, $user = null, $displaySubtitles = true)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST)
            ->setTitle('Create Account')
            ->setDisplaySubtitles($displaySubtitles);

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->_model = new Elements\User($formId);

        if ($user !== null) {
            $this->_model->setUser($user);
        }

        $this->addElements(
            $this->_model->getElements());


        /* submit button */
        $this->addSubmitElement('Submit');

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * override setData() method to add validators that depend on multiple elements
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);

        $this->addElements(
            $this->_model->getElements());

        /* submit button */
        $this->addSubmitElement('Submit');

        $translate = $this->getTranslate();

        if ($this->hasElement('password')) {
            $passwordConfirmValidator = new Validate\Identical();
            $passwordConfirmValidator->setStrict()
                ->setVariableName($translate->_('Confirm Password'));

            if (isset($data['password_confirm'])) {
                $passwordConfirmValidator->setVariableValue($data['password_confirm']);
            }

            $password = $this->getElement('password')
                ->addValidator($passwordConfirmValidator);

            $this->addElement($password);
        }

        parent::setData($data);

        return $this;
    }

    /**
     *
     * will generate the edit user form
     *
     * @param integer $id the id of the table row
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $translate = $this->getTranslate();

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $this->setTitle('Edit User');

            if ($this->hasElement('username')) {
                $this->getElement('username')
                    ->setAttributes(array('readonly' => 'readonly'))
                    ->setDescription(null)
                    ->clearValidators();
            }

            if ($this->hasElement('email')) {
                $this->getElement('email')
                    ->getValidator('Cube\\Validate\\Db\\NoRecordExists')
                    ->setExclude(array('field' => 'id', 'value' => $id));
            }

            if ($this->hasElement('password')) {
                $this->getElement('password')
                    ->setValue('')
                    ->setDescription('Type a new password if you want to change it.')
                    ->setRequired(false);
            }

            $this->removeElement('recaptcha')
                ->removeElement('agree_terms');

            if ($this->hasElement('submit')) {
                $this->getElement('submit')
                    ->setValue($translate->_('Proceed'));
            }
        }


        return $this;
    }

}

