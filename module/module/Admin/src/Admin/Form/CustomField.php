<?php

/**
 *
 * PHP Pro Bid $Id$ Z4/Sq9zzrx6Pd+C7wBWkpLeysSdsAZiSrM82XdUViNg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * custom field creation form
 */

namespace Admin\Form;

use Ppb\Model\Elements,
    Ppb\Form\AbstractBaseForm;

class CustomField extends AbstractBaseForm
{
    /**
     *
     * custom field elements model
     *
     * @var \Ppb\Model\Elements\CustomField
     */
    protected $_model;

    public function __construct($formId = null, $action = null)
    {
        parent::__construct($action);

        $this->setTitle('Create Custom Field');

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_POST);

        $this->_model = new Elements\CustomField($formId);

        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
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
        }

        parent::setData($data);

        return $this;
    }

    /**
     *
     * will generate the edit custom field form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Custom Field - ID: #%s'), $id));
        }

        return $this;
    }

}

