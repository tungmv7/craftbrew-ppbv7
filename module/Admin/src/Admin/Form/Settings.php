<?php

/**
 *
 * PHP Pro Bid $Id$ tJPMcn3M2bYE/5qU0galTwCyZobNLpmvTu7N/RVU45E=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */

namespace Admin\Form;

use Ppb\Model\Elements,
        Ppb\Form\AbstractBaseForm;

class Settings extends AbstractBaseForm
{
    /**
     *
     * listing form elements
     *
     * @var \Ppb\Model\Elements\AdminSettings
     */
    protected $_model;

    public function __construct($formId, $action = null)
    {
        parent::__construct($action);

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_POST);

        $this->_model = new Elements\AdminSettings($formId);

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
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);

        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            /* submit button */
            $this->addSubmitElement();
        }

        parent::setData($data);

        return $this;
    }

}

