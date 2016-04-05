<?php

/**
 *
 * PHP Pro Bid $Id$ Q5aS+NtUIbaHxnhBlmlJu3Havemd/wlkhGUhMlH2RDQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * user account options form
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
        Cube\Validate,
        Cube\Controller\Front,
        Ppb\Db\Table\Row\User;

class UserOptions extends AbstractBaseForm
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
     * @param \Ppb\Db\Table\Row\User $user   selected user
     * @param string                 $action the form's action
     */
    public function __construct(User $user, $action = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id')
                ->setBodyCode("<script type=\"text/javascript\">
                    function checkFormFields()
                    {
                        if ($('input:radio[name=\"account_mode\"]:checked').val() == 'account') {
                            $('[name=\"balance\"]').closest('.form-group').show();
                            $('[name=\"balance_adjustment_reason\"]').closest('.form-group').show();
                            $('[name=\"max_debit\"]').closest('.form-group').show();
                        }
                        else {
                            $('[name=\"balance\"]').closest('.form-group').hide();
                            $('[name=\"balance_adjustment_reason\"]').closest('.form-group').hide();
                            $('[name=\"max_debit\"]').closest('.form-group').hide();
                        }

                        if ($('input:checkbox[name=\"store_active\"]').is(':checked')) {
                            $('[name=\"assign_default_store_account\"]').closest('.form-group').show();
                        }
                        else {
                            $('[name=\"assign_default_store_account\"]').closest('.form-group').hide();
                        }
                    }

                    $(document).ready(function() {
                        checkFormFields();
                    });

                    $(document).on('change', '.field-changeable', function() {
                        checkFormFields();
                    });
                </script>");
        $this->addElement($id);

        $accountType = $this->createElement('radio', 'account_mode');
        $accountType->setLabel('Account Type')
                ->setAttributes(array(
                    'class' => 'field-changeable',
                ))
                ->setMultiOptions(array(
                    'live'    => $translate->_('Live'),
                    'account' => $translate->_('Account Mode'),
                ))
                ->setDescription('Select the user\'s account type.');
        $this->addElement($accountType);

        $balance = $this->createElement('text', 'balance')
                ->setLabel('Account Balance')
                ->setPrefix($settings['currency'])
                ->setSuffix('[ Positive value: Debit ] [ Negative value: Credit ]')
                ->setAttributes(array(
                    'class' => 'form-control input-mini'
                ))
                ->setDescription('Edit the user\'s site account balance.')
                ->addValidator(new Validate\Numeric());
        $this->addElement($balance);

        $balanceAdjustmentReason = $this->createElement('text', 'balance_adjustment_reason')
                ->setLabel('Balance Adjustment Reason')
                ->setAttributes(array(
                    'class' => 'form-control input-large',
                ))
                ->setDescription('If altering the account balance, you can enter a reason in the field above (optional)');
        $this->addElement($balanceAdjustmentReason);


        $maxDebit = $this->createElement('text', 'max_debit')
                ->setLabel('Max. Debit')
                ->setPrefix($settings['currency'])
                ->setAttributes(array(
                    'class' => 'form-control input-mini'
                ))
                ->setDescription('Enter the user\'s maximum debit balance allowed.')
                ->addValidator(
                    new Validate\Numeric())
                ->addValidator(
                    new Validate\GreaterThan(array(0, true)));
        $this->addElement($maxDebit);

        $verifiedUser = $this->createElement('checkbox', 'user_verified');
        $verifiedUser->setLabel('Verified User')
                ->setMultiOptions(
                    array(1 => null))
                ->setDescription('Check the above checkbox to set the account status as verified.');
        $this->addElement($verifiedUser);

        if ($settings['private_site']) {
            $sellingCapabilities = $this->createElement('checkbox', 'is_seller');
            $sellingCapabilities->setLabel('Can List')
                    ->setAttributes(array(
                        'class' => 'field-changeable',
                    ))
                    ->setMultiOptions(
                        array(1 => null))
                    ->setDescription('Check the above checkbox to allow the account to create listings on the website.');
            $this->addElement($sellingCapabilities);
        }

        if ($settings['preferred_sellers']) {
            $preferredSeller = $this->createElement('checkbox', 'preferred_seller');
            $preferredSeller->setLabel('Preferred Seller')
                    ->setMultiOptions(
                        array(1 => null))
                    ->setDescription('Check the above checkbox to set the account as preferred seller.');
            $this->addElement($preferredSeller);
        }

        if ($settings['enable_stores']) {
            $preferredSeller = $this->createElement('checkbox', 'store_active');
            $preferredSeller->setLabel('Enable Store')
                    ->setAttributes(array(
                        'class' => 'field-changeable',
                    ))
                    ->setMultiOptions(
                        array(1 => null))
                    ->setDescription('Check the above checkbox to activate this user\'s store.');
            $this->addElement($preferredSeller);

            $defaultStoreAccount = $this->createElement('checkbox', 'assign_default_store_account');
            $defaultStoreAccount->setLabel('Assign Default Store Account')
                    ->setMultiOptions(
                        array(1 => null))
                    ->setDescription('Check the above checkbox to set the user\'s store as "Default" (can add unlimited listings and has no expiration date).');
            $this->addElement($defaultStoreAccount);
        }


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/popup-form.phtml');
    }

}