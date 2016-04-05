<?php

/**
 *
 * PHP Pro Bid $Id$ 3MD+zecN5imHvuq1uHatNnBTn2Bz9Mxyz8u7ZN2RXus=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * installation form
 */

namespace Install\Form;

use Install\Model\Elements,
    Ppb\Form\AbstractBaseForm,
    Ppb\Authentication\Adapter,
    Cube\Validate;

class Install extends AbstractBaseForm
{

    /**
     *
     * install elements model
     *
     * @var \Install\Model\Elements\Install
     */
    protected $_model;

    public function __construct($formId = null, $action = null)
    {
        parent::__construct($action);

        $this->setTitle('Installation');

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_POST);

        $this->_model = new Elements\Install($formId);

        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->getElement('submit')
                ->addAttribute('class', 'btn-loading-modal');
            $this->setPartial('forms/generic-horizontal.phtml');
        }
    }

    /**
     *
     * override setData() method
     *
     * @param array $data
     *
     * @return array
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);
        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->getElement('submit')
                ->addAttribute('class', 'btn-loading-modal');
        }

        $translate = $this->getTranslate();

        if ($this->hasElement('admin_password')) {
            $passwordConfirmValidator = new Validate\Identical();
            $passwordConfirmValidator->setStrict()
                ->setVariableName($translate->_('Confirm Password'));

            if (isset($data['admin_password_confirm'])) {
                $passwordConfirmValidator->setVariableValue($data['admin_password_confirm']);
            }

            $password = $this->getElement('admin_password')
                ->addValidator($passwordConfirmValidator);

            $this->addElement($password);
        }

        parent::setData($data);

        return $this;
    }

    public function isValid()
    {
        $valid = parent::isValid();

        if ($this->hasElement('licensing_username') && $this->hasElement('licensing_password')) {
            // first we check for valid admin login details
            $adapter = new Adapter(array(
                'username' => $this->getData('licensing_username'),
                'password' => $this->getData('licensing_password'),
            ));

            $authenticationResult = $adapter->authenticate();
            $identity =  $authenticationResult->getIdentity() ;

            if (!$authenticationResult->isValid() || $identity['role'] != 'Admin') {
                $this->setMessage('The authentication has failed.');
                $valid = false;
            }
        }

        return $valid;
    }
}

