<?php

/**
 *
 * PHP Pro Bid $Id$ enoDMA3YeQvFwi3Q2eFBgUJtCeC48+9gKgBFrxfoy1s=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * blocked user management form
 */
namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel,
    Cube\Validate;

class BlockedUser extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     * @param int    $userId if user id != null, we have an admin block
     */
    public function __construct($action = null, $userId = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $type = $this->createElement('select', 'type');
        $type->setLabel('Block Type')
            ->setDescription('Select the type of block that you want to add.')
            ->setMultiOptions(BlockedUserModel::$blockTypes)
            ->setAttributes(array(
                'class' => 'form-control input-default'
            ));
        $this->addElement($type);

        $value = $this->createElement('text', 'value');
        $value->setLabel('Value')
            ->setDescription('Enter the username/email/ip that you want to block.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($value);


        $multiOptions = BlockedUserModel::$blockedActions;

        if ($userId) {
            unset($multiOptions[BlockedUserModel::ACTION_REGISTER]);
        }

        $blockedActions = $this->createElement('checkbox', 'blocked_actions');
        $blockedActions->setLabel('Blocked Actions')
            ->setDescription('Select the actions that you want to block for this user.')
            ->setMultiOptions($multiOptions)
            ->setRequired();
        $this->addElement($blockedActions);

        $blockReason = $this->createElement('textarea', 'block_reason');
        $blockReason->setLabel('Block Reason')
            ->setDescription('Add a description for this block.')
            ->setAttributes(array(
                'rows'  => 6,
                'class' => 'form-control'
            ))
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 500)),
            ));
        $this->addElement($blockReason);

        $showReason = $this->createElement('checkbox', 'show_reason');
        $showReason->setLabel('Show Reason')
            ->setDescription('Check the above checkbox to display the blocking reason to the blocked user.')
            ->setMultiOptions(array(
                1 => null,
            ));
        $this->addElement($showReason);


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * will generate the edit form
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
                sprintf($translate->_('Edit Blocked User - ID: #%s'), $id));
        }

        return $this;
    }
}