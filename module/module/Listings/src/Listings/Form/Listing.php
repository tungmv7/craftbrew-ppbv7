<?php

/**
 *
 * PHP Pro Bid $Id$ mrOuBmJIkhgHvHxC6EKyhCQ3VFizhK9QSLFxUgUHdzQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing form
 */

namespace Listings\Form;

use Ppb\Model\Elements,
    Ppb\Service\Users as UsersService,
    Ppb\Form\AbstractBaseForm;

class Listing extends AbstractBaseForm
{

    /**
     * current step form element name
     */
    const ELEMENT_STEP = 'current_step';

    /**
     * submit buttons names
     */
    const BTN_NEXT = 'next_step';
    const BTN_PREV = 'prev_step';
    const BTN_LIST = 'btn_list';
    const BTN_DRAFT = 'btn_draft';
    const BTN_SUBMIT = 'btn_submit';

    /**
     *
     * listing form elements
     *
     * @var \Ppb\Model\Elements\Listing
     */
    protected $_model;

    /**
     *
     * listing form steps (used for the create listing form only)
     *
     * @var array
     */
    protected $_steps = array('details', 'settings', 'shipping', 'preview');

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_NEXT   => 'Next Step',
        self::BTN_PREV   => 'Previous Step',
        self::BTN_LIST   => 'List Now',
        self::BTN_DRAFT  => 'Save as Draft',
        self::BTN_SUBMIT => 'Proceed',
    );

    /**
     *
     * the current form step
     *
     * @var string
     */
    protected $_currentStep = null;

    /**
     *
     * true if sub-forms are used, false otherwise
     *
     * @var bool
     */
    protected $_isSubForm = false;

    /**
     *
     * class constructor
     *
     * @param string|array $formId the id of the form, used by the form elements model
     * @param string       $action the form's action
     * @param int          $userId (optional) the id of the owner (if the listing is edited by an administrator)
     */
    public function __construct($formId = null, $action = null, $userId = null)
    {
        parent::__construct($action);

        $this->setTitle('Create Listing');

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_POST);

        $inAdmin = ($userId !== null) ? true : false;
        $this->_model = new Elements\Listing($formId, $inAdmin);

        if ($userId !== null) {
            $user = new UsersService();
            $this->_model->setUser(
                $user->findBy('id', $userId))
                ->getFees()
                ->disableFees(true);
        }

        $this->addElements(
            $this->_model->getElements());


        $this->_generateFormButtons();

        $this->setPartial('forms/listing.phtml');
    }

    /**
     *
     * get listing model
     *
     * @return \Ppb\Model\Elements\Listing
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     *
     * generate all submit buttons for a subform:
     * - next step
     * - previous step
     * - list now/proceed
     * - save as draft
     *
     * @return \Listings\Form\Listing
     */
    protected function _generateFormButtons()
    {
        if ($this->isSubForm()) {
            foreach ($this->_buttons as $key => $element) {
                $this->removeElement($key);
            }

            $currentStep = $this->getCurrentStep();

            if ($this->prevStep($currentStep) !== false) {
                $this->addSubmitElement($this->_buttons[self::BTN_PREV], self::BTN_PREV);
            }

            if ($this->nextStep($currentStep) !== false) {
                $this->addSubmitElement($this->_buttons[self::BTN_NEXT], self::BTN_NEXT);
            }
            else {
                $this->addSubmitElement($this->_buttons[self::BTN_LIST], self::BTN_LIST)
                    ->addSubmitElement($this->_buttons[self::BTN_DRAFT], self::BTN_DRAFT);
            }
        }
        else {
            if (count($this->getElements()) > 0) {
                $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);
            }
        }

        return $this;
    }

    /**
     *
     * get sub form flag
     *
     * @return bool
     */
    public function isSubForm()
    {
        return $this->_isSubForm;
    }

    /**
     *
     * get current form step
     *
     * @return string
     */
    public function getCurrentStep()
    {
        if ($this->_currentStep === null) {
            $this->setCurrentStep();
        }

        return $this->_currentStep;
    }

    /**
     *
     * set current form step
     *
     * @param string $currentStep
     *
     * @return \Listings\Form\Listing
     */
    public function setCurrentStep($currentStep = null)
    {
        if (!in_array($currentStep, $this->_steps)) {
            reset($this->_steps);
            $currentStep = current($this->_steps);
        }

        $element = $this->createElement('hidden', self::ELEMENT_STEP);
        $element->setHidden()
            ->setValue($currentStep);
        $this->addElement($element);

        $this->_currentStep = $currentStep;

        return $this;
    }

    /**
     *
     * get the previous step of the form, based on a set current step
     *
     * @param string $currentStep
     *
     * @return string|false     previous step value or false if the current step is the first step
     */
    public function prevStep($currentStep = null)
    {
        end($this->_steps);

        while (current($this->_steps) !== $currentStep) {
            $prev = prev($this->_steps);

            if ($prev === false) {
                return $prev;
            }
        }

        return prev($this->_steps);
    }

    /**
     *
     * get the next step of the form, based on a set current step
     *
     * @param string $currentStep
     *
     * @return string|false     next step value or false if the current step was the last step
     */
    public function nextStep($currentStep = null)
    {
        reset($this->_steps);

        while (current($this->_steps) !== $currentStep) {
            $next = next($this->_steps);

            if ($next === false) {
                return false;
            }
        }

        return next($this->_steps);
    }

    /**
     *
     * will generate the edit listing form
     *
     * @param integer $id the id of the table row
     *
     * @return \Listings\Form\Listing
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Listing - ID: #%s'), $id));

            if (strtotime($this->getData('start_time')) < time()) {
                $this->removeElement('start_time_type');

                $startTime = $this->getElement('start_time');
                $startTime->setType('hidden')
                    ->setValue($this->getData('start_time'))
                    ->clearValidators();

                $this->addElement($startTime);
            }
            else {
                $this->_data['start_time_type'] = 1;
            }
        }

        return $this;
    }

    /**
     *
     * set the data of the submitted form
     * plus add the data in the listing model
     *
     * @param array $data form data
     *
     * @return \Listings\Form\Listing
     */
    public function setData(array $data = null)
    {
        $settings = $this->getSettings();

        $data['start_time_type'] = (isset($data['start_time_type'])) ? $data['start_time_type'] : 0;
        $listingType = (isset($data['listing_type'])) ? $data['listing_type'] : null;

        if (isset($data['start_time'])) {
            $data['start_time_type'] = (strtotime($data['start_time']) > time()) ? 1 : 0;
        }

        if ($settings['enable_unlimited_duration'] && $settings['force_unlimited_duration'] && $listingType == 'product') {
            $data['end_time_type'] = 0;
            $data['duration'] = null;
        }
        else if (isset($data['end_time_type'])) {
            if ($data['end_time_type'] == 1) {
                $data['duration'] = null;
            }
        }
        else {
            $data['end_time_type'] = (empty($data['duration']) && !empty($data['end_time'])) ? 1 : 0;
        }

        foreach ($data as $key => $value) {
            if (($array = \Ppb\Utility::unserialize($value)) !== $value) {
                $data[$key] = $array;
            }
        }

        $this->_model->setData($data);

        $this->addElements(
            $this->_model->getElements());

        $this->_generateFormButtons();

        parent::setData($data);

        return $this;
    }

    /**
     *
     * get form steps array
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     *
     * generate a sub form based on the current step
     * all elements corresponding to the form step will be displayed,
     * while the rest of the elements in the form will be displayed as hidden elements
     * Important: elements with multiple values are not serialized anymore
     *
     * @param string $currentStep
     *
     * @return \Listings\Form\Listing
     */
    public function generateSubForm($currentStep = null)
    {
        $this->setSubForm(true)
            ->setCurrentStep($currentStep);

        /** @var \Cube\Form\Element $element */
        foreach ($this->getElements() as $name => $element) {
            $subForm = $element->getSubForm();

            if ($subForm !== null && $subForm != $this->_currentStep) {
                /** @var \Cube\Form\Element\Hidden $hiddenElement */
                $hiddenElement = $this->createElement('hidden', $name);
                $value = $element->getValue();

                if (is_array($value)) {
                    $hiddenElement->setForceCountMultiple(true)->setMultiple(true);
                }

                $hiddenElement->setValue($value);

                $this->addElement($hiddenElement);
            }
        }

        $this->_generateFormButtons();

        return $this;
    }

    /**
     *
     * set sub form flag
     *
     * @param bool $subForm
     *
     * @return $this
     */
    public function setSubForm($subForm = true)
    {
        $this->_isSubForm = (bool)$subForm;

        return $this;
    }

}
