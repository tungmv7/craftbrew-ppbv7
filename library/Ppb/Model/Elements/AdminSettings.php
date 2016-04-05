<?php

/**
 *
 * PHP Pro Bid $Id$ 8K86/Ks0rn7OstjQP6vmvc4SAfS3KmdhxlpZIIdSP/8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * admin form elements collection
 */
/**
 * MOD:- FACEBOOK LOGIN
 */

namespace Ppb\Model\Elements;

use Cube\Controller\Front,
    Cube\Db\Select,
    Cube\Db\Expr,
    Ppb\Service\Table\Currencies as CurrenciesService,
    Ppb\Service\Timezones as TimezonesService,
    Ppb\Service\Listings as ListingsService,
    Ppb\Service\Fees;

class AdminSettings extends AbstractElements
{

    /**
     *
     * form id
     *
     * @var string
     */
    protected $_formId;

    /**
     *
     * timezones table service
     *
     * @var \Ppb\Service\Timezones
     */
    protected $_timezones;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * class constructor
     *
     * @param string $formId
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->_formId = $formId;
    }

    /**
     *
     * get timezones table service
     *
     * @return \Ppb\Service\Timezones
     */
    public function getTimezones()
    {
        if (!$this->_timezones instanceof TimezonesService) {
            $this->setTimezones(
                new TimezonesService());
        }

        return $this->_timezones;
    }

    /**
     *
     * set timezones table service
     *
     * @param \Ppb\Service\Timezones $timezones
     *
     * @return \Ppb\Model\Elements\AdminSettings
     */
    public function setTimezones(TimezonesService $timezones)
    {

        $this->_timezones = $timezones;

        return $this;
    }

    /**
     *
     * get currencies table service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrencies()
    {
        if (!$this->_currencies instanceof CurrenciesService) {
            $this->setCurrencies(
                new CurrenciesService());
        }

        return $this->_currencies;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currencies
     *
     * @return \Ppb\Model\Elements\AdminSettings
     */
    public function setCurrencies(CurrenciesService $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        $basePath = Front::getInstance()->getRequest()->getBasePath();

        $totalListings = 0;
        if ($this->_formId == 'listings_counters') {
            $listingsService = new ListingsService();

            $select = $listingsService->select(ListingsService::SELECT_LISTINGS);

            $select->reset(Select::COLUMNS);
            $select->columns(array('nb_rows' => new Expr('count(*)')));

            $stmt = $select->query();

            $totalListings = (integer)$stmt->fetchColumn('nb_rows');
        }

        return array(
            /**
             * --------------
             * SITE SETUP
             * --------------
             */
            array(
                'form_id'     => 'site_setup',
                'id'          => 'sitename',
                'element'     => 'text',
                'label'       => $this->_('Site Name'),
                'description' => $this->_('Enter your site\'s name. The name will be used for generating dynamic meta titles, and it will appear in all the emails sent by and through the site.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml'
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'site_path',
                'element'     => 'text',
                'label'       => $this->_('Site URL'),
                'description' => $this->_('Enter your site\'s URL. <br>'
                        . 'The URL must have the following format: http://www.yoursite.com<br>'
//                                          . 'Important: If you have an SSL certificate, you can enter the site\'s URL as: https://www.yoursite.com'),
                        . 'If you have SSL available you can set your URL using https:// rather than http:// (Optional)'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Url',
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'site_logo_path',
                'element'     => '\\Ppb\\Form\\Element\\MultiUpload',
                'label'       => $this->_('Site Logo'),
                'description' => $this->_('Upload a logo for your website.'),
                'required'    => true,
                'customData'  => array(
                    'buttonText'      => 'Select Logo',
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 10000000, // approx 10MB
                        'uploadLimit'   => 1,
                    ),
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'default_theme',
                'element'      => 'select',
                'label'        => $this->_('Site Theme'),
                'multiOptions' => \Ppb\Utility::getThemes(),
                'description'  => $this->_('Select a theme for your website.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'site_lang',
                'element'      => 'select',
                'label'        => $this->_('Site Language'),
                'multiOptions' => \Ppb\Utility::getLanguages(),
                'description'  => $this->_('Select a default language for your website.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'admin_security_key',
                'element'     => 'text',
                'label'       => $this->_('Admin Area Security Key'),
                'description' => $this->_('(Optional) You can add a security key that will be required to be added to the admin path in order to be able to access it. <br>'
                        . 'Current Admin Path:')
                    . '<div class="text-info"><strong>'
                    . $this->getView()->url(array('skey' => $this->getData('admin_security_key')), 'admin-index-index') . '</strong></div>',
                'attributes'  => array(
                    'class'        => 'form-control input-medium alert-box',
                    'data-message' => $translate->_('Warning! If adding a security key, please save the admin path in a safe place because you will '
                        . 'not be able to access the admin area without adding the security key to the url.'),
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'admin_email',
                'element'     => 'text',
                'label'       => $this->_('Admin Email Address'),
                'description' => $this->_('Enter your admin email address. This address will be used in the "From" field by all system emails.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Email'
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'email_admin_title',
                'element'     => 'text',
                'label'       => $this->_('Admin Email From Name'),
                'description' => $this->_('Enter the from name which will appear on all emails sent by the site on behalf of the administrator.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml'
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'mailer',
                'element'      => 'select',
                'label'        => $this->_('Choose Mailer'),
                'multiOptions' => array(
                    'mail'     => 'PHP mail()',
                    'sendmail' => 'Sendmail',
                    'smtp'     => 'SMTP',
                ),
                'description'  => $this->_('Available methods: php mail() function, unix sendmail app, SMTP protocol.<br>'
                        . 'SMTP recommended (if available on your server)'),
                'required'     => true,
                'attributes'   => array(
                    'id'       => 'mailer',
                    'class'    => 'form-control input-medium',
                    'onchange' => 'javascript:checkMailFields()',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkMailFields() {
                            switch ($('#mailer').val()) {
                                case 'sendmail':
                                    $('.mailer-sendmail').closest('.form-group').show();
                                    $('.mailer-smtp').closest('.form-group').hide();
                                    break;
                                case 'smtp':
                                    $('.mailer-sendmail').closest('.form-group').hide();
                                    $('.mailer-smtp').closest('.form-group').show();
                                    break;
                                default:
                                    $('.mailer-sendmail').closest('.form-group').hide();
                                    $('.mailer-smtp').closest('.form-group').hide();
                                    break;                    
                            }
                        }

                        $(document).ready(function() {             
                            checkMailFields();
                        });
                    </script>",
            ),
            /* site setup => sendmail path */
            array(
                'form_id'     => 'site_setup',
                'id'          => 'sendmail_path',
                'element'     => 'text',
                'label'       => $this->_('Sendmail Path'),
                'description' => $this->_('Enter the unix path for the sendmail app (available in phpinfo())'),
                'required'    => array('mailer', 'sendmail', true),
                'attributes'  => array(
                    'class' => 'mailer-sendmail form-control input-medium',
                ),
            ),
            /* site setup => smtp related fields */
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_host',
                'element'    => 'text',
                'label'      => $this->_('SMTP Host'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_port',
                'element'    => 'text',
                'label'      => $this->_('SMTP Port'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_username',
                'element'    => 'text',
                'label'      => $this->_('SMTP Username'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'smtp_password',
                'element'     => 'text',
                'label'       => $this->_('SMTP Password'),
                'description' => $this->_('Enter your SMTP login details in case you choose to use SMTP as the system emails handler.<br>'
                        . '<b>Important</b>: you only need enter a username and a password if you SMTP server requires authentication.'
                        . 'If the server doesn\'t require authentication, please leave these fields empty because otherwise the SMTP server can return an error and no emails will be sent.<br>'
                        . 'If you are unsure of your SMTP server\'s host name and port, please leave the Host and Port fields empty and the software will try to retrieve them for you.'),
                'attributes'  => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'maintenance_mode',
                'element'      => 'checkbox',
                'label'        => $this->_('Maintenance Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable maintenance mode. With maintenance mode enabled, '
                        . 'only logged in administrators will be able to access the front end of the site.'),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'disable_installer',
                'element'      => 'checkbox',
                'label'        => $this->_('Disable Installer'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to disable the install module. The installer module should only be enabled when '
                        . 'you need to upgrade the software.'),
            ),
            /**
             * --------------
             * USER SETTINGS
             * --------------
             */
            /**
             * ++++++++++++++
             * REGISTRATION & VERIFICATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('User Registration'),
                'id'           => 'registration_type',
                'element'      => 'radio',
                'label'        => $this->_('Select Registration Type'),
                'multiOptions' => array(
                    'quick' => array(
                        $translate->_('Quick'),
                        $translate->_('Only the username, email address and password fields will appear on the registration page.'),
                    ),
                    'full'  => array(
                        $translate->_('Full'),
                        $translate->_('This form will include all registration fields, address, date of birth, phone number and any available custom fields.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkFormFields()
                        {
                            if ($('input:radio[name=\"registration_type\"]:checked').val() == 'full') {
                                $('.full-registration-field').closest('.form-group').show();
                            }
                            else {
                                $('input[name=\"min_reg_age\"]').val('');
                                $('.full-registration-field').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'min_reg_age',
                'element'     => 'text',
                'label'       => $this->_('Minimum Registration Age'),
                'suffix'      => $this->_('years'),
                'description' => $this->_('Enter the minimum age required for users to be able to register to your site, or leave empty to disable this functionality.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini full-registration-field',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'payment_methods_registration',
                'element'      => 'checkbox',
                'label'        => $this->_('Direct Payment Gateways Fields'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish to display the setup fields for your enabled direct payment gateways on the registration page.'),
            ),
            /**
             * ++++++++++++++
             * USER VERIFICATION - UNIFIED SELLER/BUYER VERIFICATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('User Verification'),
                'id'           => 'user_verification',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable User Verification'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the user verification feature.<br>'
                        . '<b>Note</b>: Even if you disable user verification, you will still be able to set the status of your '
                        . 'users to verified from the Users Management page. Users will however not be able to verify their '
                        . 'accounts themselves.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkVerificationFields()
                        {
                            if ($('input:checkbox[name=\"user_verification\"]').is(':checked')) {
                                $('[name=\"seller_verification_mandatory\"]').closest('.form-group').show();
                                $('[name=\"buyer_verification_mandatory\"]').closest('.form-group').show();
                                $('[name=\"user_verification_address\"]').closest('.form-group').show();
                                $('[name=\"user_verification_refund\"]').closest('.form-group').show();
                                $('input:text[name=\"user_verification_fee\"]').closest('.form-group').show();
                                $('input:text[name=\"user_verification_recurring\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"seller_verification_mandatory\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"buyer_verification_mandatory\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"user_verification_address\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"user_verification_refund\"]').prop('checked', false).closest('.form-group').hide();
                                $('input:text[name=\"user_verification_fee\"]').val('').closest('.form-group').hide();
                                $('input:text[name=\"user_verification_recurring\"]').val('').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkVerificationFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkVerificationFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'seller_verification_mandatory',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory For Selling'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If mandatory seller verification is enabled, users will need to get verified in order to be able to list items on your website.'),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'buyer_verification_mandatory',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory for Buying'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If mandatory buyer verification is enabled, users will need to get verified in order to be able to bid on/purchase items on your website.'),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'user_verification_address',
                'element'      => 'checkbox',
                'label'        => $this->_('Require Address Registration'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If the above field is checked, users will be required to complete the address registration step to get verified.'),
            ),
            array(
                'form_id'    => 'registration_verification',
                'table'      => 'fees',
                'id'         => 'user_verification_fee',
                'element'    => 'text',
                'label'      => $this->_('Verification Fee'),
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
                'required'   => ($this->getData('user_verification')) ? true : false,
                'validators' => array(
                    'Numeric',
                    array('GreaterThan', array(0, true)),
                ),
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'user_verification_recurring',
                'element'     => 'text',
                'prefix'      => $this->_('recurring every'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('You can set up a one time or recurring verification fee. If you wish the verification fee to be a one time fee enter 0 in the recurring field.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'user_verification_refund',
                'element'      => 'checkbox',
                'label'        => $this->_('Refund Verification Fee'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If the checkbox above is checked, the verification fee will be credited to the user\'s account after payment.<br>'
                        . 'The user\'s account will need to run in account mode for this feature to apply.'),
            ),

            /**
             * ++++++++++++++
             * REGISTRATION TERMS & CONDITIONS LINK
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('Terms and Conditions / Privacy Policy Link'),
                'id'           => 'enable_registration_terms',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Registration Terms & Conditions Link'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to require users to agree to the site\'s terms and/or privacy when registering.'),
            ),

            array(
                'form_id'     => 'registration_verification',
                'id'          => 'registration_terms_link',
                'element'     => 'text',
                'label'       => $this->_('Terms and Conditions Link'),
                'description' => $this->_('Enter the url of the terms and conditions page (relative url).'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => $this->getData('enable_registration_terms') ? true : false,
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'registration_privacy_link',
                'element'     => 'text',
                'label'       => $this->_('Privacy Policy Link'),
                'description' => $this->_('Enter the url of the privacy policy page (relative url).'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => $this->getData('enable_registration_terms') ? true : false,
                'validators'  => array(
                    'NoHtml',
                ),
            ),

            /**
             * ++++++++++++++
             * USER ACCOUNT SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'account_settings',
                'id'           => 'payment_mode',
                'element'      => 'radio',
                'label'        => $this->_('Choose Payment Option'),
                'multiOptions' => array(
                    'live'    => array(
                        $translate->_('Live (Pay as You Go)'),
                        $translate->_('Choose this option if you want your users to pay for site fees immediately.'),
                    ),
                    'account' => array(
                        $translate->_('Account Mode'),
                        $translate->_('Choose this option in order for your site\'s users to pay for site fees periodically. '
                            . 'All fees they owe will be added in their account balance.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkAccountSettingsFormFields()
                        {
                            if ($('input:radio[name=\"payment_mode\"]:checked').val() == 'live' &&
                                $('input:radio[name=\"user_account_type\"]:checked').val() == 'global') {

                                $('.account-mode-field').closest('.form-group').hide();
                            }
                            else {
                                $('input[name=\"min_reg_age\"]').val('');
                                $('.account-mode-field').closest('.form-group').show();
                            }
                        }

                        $(document).ready(function() {
                            checkAccountSettingsFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkAccountSettingsFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'user_account_type',
                'element'      => 'radio',
                'label'        => $this->_('User Account Type'),
                'multiOptions' => array(
                    'global'   => array(
                        $translate->_('Global'),
                        $translate->_('Choose this option if you want all accounts to run using the default payment option.'),
                    ),
                    'personal' => array(
                        $translate->_('Personal'),
                        $translate->_('Choose this option if you want to be able to choose the payment option for each user account, from the users management page.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'    => 'account_settings',
                'subtitle'   => $this->_('Account Mode Settings'),
                'id'         => 'signup_credit',
                'element'    => 'text',
                'label'      => $this->_('Signup Credit'),
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'validators' => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'maximum_debit',
                'element'     => 'text',
                'label'       => $this->_('Maximum Debit'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter the maximum debit an account is allowed to have before being suspended.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'min_invoice_value',
                'element'     => 'text',
                'label'       => $this->_('Minimum Credit Amount'),
                'prefix'      => $settings['currency'],
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'description' => $this->_('Enter the minimum payment amount that a user can credit his account balance with.'),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'payment_reminder_email',
                'element'      => 'checkbox',
                'label'        => $this->_('Payment Notification Emails'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check the above checkbox if you wish to send automatic notification emails to accounts that have exceeded their debit limit.'),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'suspend_over_limit_accounts',
                'element'      => 'checkbox',
                'label'        => $this->_('Suspend Accounts over Limit'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check the above checkbox if you wish to suspend accounts that have the balance over the maximum debit limit.'),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'suspension_days',
                'element'     => 'text',
                'label'       => $this->_('Cron Invoice Suspension'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'description' => $this->_('(Optional) enter the number of days after which an account that has been sent an automatic payment notification email will be suspended. '
                        . 'This setting will only apply if you have selected not to suspend accounts that have exceeded their debit limit.'),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'rebill_expired_subscriptions',
                'element'      => 'checkbox',
                'label'        => $this->_('Re-bill Expired Subscriptions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check the above checkbox if you wish to bill expired subscriptions automatically from the users\' balances (works only when in account mode).'),
            ),
            /**
             * ++++++++++++++
             * USER SIGNUP CONFIRMATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'signup_settings',
                'id'           => 'signup_settings',
                'element'      => 'radio',
                'label'        => $this->_('User Signup Confirmation'),
                'multiOptions' => array(
                    0 => array(
                        $translate->_('No Confirmation Required'),
                        $translate->_('Check the above box if user accounts should be activated immediately, with no confirmation required.'),
                    ),
                    1 => array(
                        $translate->_('Email Address Verification'),
                        $translate->_('Check the above box if you wish to enable email address confirmation. In this case, users will '
                            . 'need to click the link from the registration confirmation email they receive when registering in order to activate their account.'),
                    ),
                    2 => array(
                        $translate->_('Admin Approval'),
                        $translate->_('Check the above box if you wish for the admin to manually activate each user from the users management page.<br>'
                            . 'Users will also need to confirm their email address like on the "email address confirmation" option.'),
                    ),
                ),
                'description'  => $this->_('Select the signup confirmation settings which should apply on your website.'),
            ),

//            /**
//             * ++++++++++++++
//             * ABOUT ME/PROFILE PAGE
//             * ++++++++++++++
//             */
//            array(
//                'form_id'      => 'profile_page',
//                'id'           => 'profile_page',
//                'element'      => 'checkbox',
//                'label'        => $this->_('Enable About Me/Profile Page'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => $this->_('Check the above checkbox to enable users to create an about me/profile page on the website.'),
//            ),
            /**
             * ++++++++++++++
             * ENABLE PRIVATE REPUTATION COMMENTS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'private_reputation_comments',
                'id'           => 'private_reputation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Private Reputation Comments'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to make the reputation comments private/not available for other users to read.<br>'
                        . 'In this case, only the reputation score will be viewable publicly.'),
            ),
            /**
             * DISABLE REPUTATION
             */
            array(
                'form_id'      => 'users_reputation',
                'id'           => 'enable_reputation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Reputation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to enable the reputation module.'),
            ),
            /**
             * MOD:- FACEBOOK LOGIN
             */
            array(
                'form_id'      => 'facebook_login',
                'id'           => 'enable_facebook_login',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Facebook Login'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to enable the Facebook login option on your website.'),
            ),
            array(
                'form_id'    => 'facebook_login',
                'id'         => 'facebook_app_id',
                'element'    => 'text',
                'label'      => $this->_('Facebook App ID'),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'facebook_login',
                'id'          => 'facebook_app_secret',
                'element'     => 'text',
                'label'       => $this->_('Facebook App Secret'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'description' => $this->_('In order to use the Facebook Login option, you will first need to create a new website app at: <br>'
                        . '<a href="http://developers.facebook.com/setup" target="_blank">http://developers.facebook.com/setup</a><br>'
                        . 'When prompted to choose a category, select "Apps for pages".<br>'
                        . 'After you have successfully created the app, please copy the App ID and App Secret in the fields above.'),
            ),
            /**
             * ++++++++++++++
             * TIME AND DATE SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'time_date',
                'id'           => 'timezone',
                'element'      => 'select',
                'label'        => $this->_('Time Zone'),
                'multiOptions' => $this->getTimezones()->getMultiOptions(),
                'description'  => $this->_('Select your site\'s time zone.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
            ),
            array(
                'form_id'      => 'time_date',
                'id'           => 'date_format',
                'element'      => 'radio',
                'label'        => $this->_('Date Format'),
                'multiOptions' => array(
                    '%m/%d/%Y %H:%M:%S' => array(
                        'mm/dd/yyyy h:m:s',
                        $translate->_('Example:') . ' ' . $this->getView()->date(time(), '%m/%d/%Y %H:%M:%S'),
                    ),
                    '%d.%m.%Y %H:%M:%S' => array(
                        'dd.mm.yyyy h:m:s',
                        $translate->_('Example:') . ' ' . $this->getView()->date(time(), '%d.%m.%Y %H:%M:%S'),
                    ),
                ),
                'description'  => $this->_('Select a format for displaying dates and date/time combinations on your website.'),
                'required'     => true,
            ),
            /**
             * ++++++++++++++
             * USER DEFINED LANGUAGES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'user_languages',
                'id'           => 'user_languages',
                'element'      => 'checkbox',
                'label'        => $this->_('Multi Language Support'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If this setting is enabled, visitors browsing your site will be able to select a language in which the site will be displayed.<br>'
                        . 'This setting will only apply if your site is available in multiple languages (not available by default).'),
            ),
            /**
             * ++++++++++++++
             * SEO SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_title',
                'element'     => 'text',
                'label'       => $this->_('Meta Title'),
                'description' => $this->_('(Highly Recommended) Add a meta title for your home page.'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_description',
                'element'     => 'textarea',
                'label'       => $this->_('Meta Description'),
                'description' => $this->_('(Highly Recommended) This meta description tells the search engines what your site is about. '
                        . 'Your description should have between 70 and 155 characters (including spaces).'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'rows'  => '4',
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_data',
                'element'     => '\\Ppb\\Form\\Element\\MultiKeyValue',
                'label'       => $this->_('Other Tags'),
                'description' => $this->_('(Optional) Enter any additional meta tags that you might want to add to your site.<br>'
                        . 'Format: name (keywords, robots, etc) - content (string)'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'seo_settings',
                'id'           => 'mod_rewrite_urls',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Search Engine Friendly URLs'),
                'description'  => sprintf(
                    $this->_('The mod_rewrite apache extension should be loaded in order for search engine friendly URLs to work. '
                        . 'There are alternatives to this extension if running '
                        . '<a target="_blank" href="http://blog.martinfjordvald.com/2011/02/nginx-primer-2-from-apache-to-nginx/">ngnix</a> or '
                        . '<a target="_blank" href="http://www.micronovae.com/ModRewrite/ModRewrite.html">Microsoft IIS</a>.<br>'
                        . '<em>mod_rewrite extension status:</em> %s'),
                    ((\Ppb\Utility::checkModRewrite()) ?
                        '<span class="label label-success">' . $this->_('Enabled') . '</span>' :
                        '<span class="label label-warning">' . $this->_('Disabled / Check Failed') . '</span>')),
                'multiOptions' => array(
                    1 => null
                ),
            ),
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'home_page_html',
                'element'     => 'textarea',
                'label'       => $this->_('Home Page Custom HTML'),
                'description' => $this->_('(Recommended for SEO) Add custom html to your home page. '
                        . 'You should add one &lt;h1&gt; tag that best describes your website and at least one &lt;h2&gt; tag with secondary descriptions.'),
                'attributes'  => array(
                    'class' => 'form-control textarea-code',
                    'rows'  => 12,
                ),
            ),

//            array(
//                'form_id' => 'seo_settings',
//                'id' => 'enable_sitemap',
//                'element' => 'checkbox',
//                'label'        => $this->_('Enable XML Sitemap'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => $this->_('If this setting is enabled, visitors browsing your site will be able to select a language in which the site will be displayed.<br>'
//                        . 'This setting will only apply if your site is available in multiple languages (not available by default).'),
//            ),
            /**
             * ++++++++++++++
             * CRON JOBS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'cron_jobs',
                'id'           => 'cron_job_type',
                'element'      => 'radio',
                'label'        => $this->_('Cron Jobs Setup'),
                'multiOptions' => array(
                    'server'      => array(
                        $translate->_('Run cron jobs from your server\'s control panel'),
                        sprintf($translate->_('Please add ONE of the following lines and set it to run every minute:<br>'
                            . 'curl -s %1$s/cron.php' . '<br>'
                            . 'wget -q %1$s/cron.php' . '<br><br>'
                            . 'Important: Add a cron job with one of the commands below to purge unused images - the cron job should run once per hour:<br>'
                            . 'curl -s %1$s/cron.php?command=purge-unused-uploaded-files 2>&1' . '<br>'
                            . 'wget -q %1$s/cron.php?command=purge-unused-uploaded-files' . '<br><br>'
                            . 'Optional: Add a cron job with one of the commands below to update the currency exchange rates - the cron job should run daily:<br>'
                            . 'curl -s %1$s/cron.php?command=update-currency-exchange-rates 2>&1' . '<br>'
                            . 'wget -q %1$s/cron.php?command=update-currency-exchange-rates'
                        ), $settings['site_path']),
                    ),
                    'application' => array(
                        $translate->_('Run cron jobs from within the application'),
                        $translate->_('Cron jobs will be run automatically each time the site is accessed. Use only if you dont '
                            . 'have access to the cron tab application on your server.'),
                    )
                ),
            ),
            /**
             * ++++++++++++++
             * PRIVATE SITE/SINGLE SELLER
             * ++++++++++++++
             */
            array(
                'form_id'      => 'private_site',
                'id'           => 'private_site',
                'element'      => 'checkbox',
                'label'        => $this->_('Private Site/Single Seller'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('Enable this feature if you want to be able to select which users are allowed to list on your site.<br>'
                        . 'You can select which users will have selling privileges from the Users Management page.'),
            ),
            /**
             * ++++++++++++++
             * PREFERRED SELLERS FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'preferred_sellers',
                'id'           => 'preferred_sellers',
                'element'      => 'checkbox',
                'label'        => $this->_('Preferred Sellers Feature'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('Enable this feature if you want to give certain users listing/sale fee reductions.'),
            ),
            array(
                'form_id'     => 'preferred_sellers',
                'id'          => 'preferred_sellers_expiration',
                'element'     => 'text',
                'label'       => $this->_('Expires after'),
                'description' => $this->_('(Optional) Enter the number of days after which the preferred seller status will expire.'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'     => 'preferred_sellers',
                'id'          => 'preferred_sellers_reduction',
                'element'     => 'text',
                'label'       => $this->_('Reduction'),
                'description' => $this->_('Enter the reduction percentage that will be applied.'),
                'suffix'      => '%',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'      => 'preferred_sellers',
                'id'           => 'preferred_sellers_apply_sale',
                'element'      => 'checkbox',
                'label'        => $this->_('Apply To Sale Fees'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('Check the above checkbox if you wish to apply the preferred seller reduction to sale fees.'),
            ),
            /**
             * SITE INVOICES SETTINGS [ Header - Footer ]
             */
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_address',
                'element'     => 'textarea',
                'label'       => $this->_('Invoice Address'),
                'description' => $this->_('Enter the address that will appear on site invoices.'),
                'attributes'  => array(
                    'rows'  => '8',
                    'class' => 'form-control textarea-code',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_header',
                'element'     => 'textarea',
                'label'       => $this->_('Invoice Header'),
                'description' => $this->_('Add a custom html header for site invoices, or leave empty if you wish for the site logo will be displayed.'),
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control textarea-code',
                ),
            ),
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_footer',
                'element'     => 'textarea',
                'label'       => $this->_('Invoice Footer'),
                'description' => $this->_('Add a custom html footer for site invoices.'),
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control textarea-code',
                ),
            ),
            /**
             * ++++++++++++++
             * ADDRESS DISPLAY FORMAT
             * ++++++++++++++
             */
            array(
                'form_id'      => 'address_display_format',
                'id'           => 'address_display_format',
                'element'      => 'radio',
                'label'        => $this->_('Address Display Format'),
                'multiOptions' => array(
                    'default'         => array(
                        $translate->_('Default'),
                        $translate->_('Address, City, Post/Zip Code, County/State, Country'),
                    ),
                    'alternate'   => array(
                        $translate->_('Alternate'),
                        $translate->_('Address, City, County/State, Post/Zip Code, Country'),
                    ),
                ),
                'description'  => $this->_('Choose how addresses should be displayed on the website (on sales, invoices, etc.).'),
            ),
            /**
             * ++++++++++++++
             * GOOGLE ANALYTICS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'google_analytics',
                'id'          => 'google_analytics_code',
                'element'     => 'textarea',
                'label'       => $this->_('Google Analytics Code'),
                'description' => $this->_('If you have a Google Analytics account that you want to use for your site, you can add the tracking code that '
                        . 'Google provides in the field above.'),
                'attributes'  => array(
                    'rows'  => '16',
                    'class' => 'form-control textarea-code',
                ),
            ),
            /**
             * ++++++++++++++
             * ALLOW BUYER TO COMBINE PURCHASES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'combine_purchases',
                'id'           => 'buyer_create_invoices',
                'element'      => 'checkbox',
                'label'        => $this->_('Buyer can Combine Purchases'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('If this setting is enabled, buyers can combine purchased items from the same seller into a single invoice.<br>'
                        . '<b>Important</b>: Only non-invoiced items can be combined.'),
            ),
            /**
             * ++++++++++++++
             * FEATURED LISTINGS BOXES
             * ++++++++++++++
             */
            array(
                'form_id'     => 'home_page_appearance',
                'id'          => 'hp_listings_imgsize',
                'element'     => 'text',
                'label'       => $this->_('Image Size'),
                'description' => $this->_('Enter the size of the listings images.'),
                'required'    => true,
                'suffix'      => $this->_('pixels'),
                'validators'  => array(
                    'Digits',
                    array('GreaterThan', array(30, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * DISPLAY SECTIONS SETTINGS
             */
            /* HOME PAGE FEATURED */
            array(
                'form_id'     => 'home_page_appearance',
                'subtitle'    => $this->_('Home Page Featured'),
                'id'          => 'hpfeat_nb',
                'element'     => 'text',
                'label'       => $this->_('Listings'),
                'description' => $this->_('Enter the maximum number of home page featured listings that will be displayed or leave empty to disable.'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(24, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'hpfeat_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'hpfeat_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'hpfeat_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            /* RECENTLY LISTED */
            array(
                'form_id'    => 'home_page_appearance',
                'subtitle'   => $this->_('Recently Listed'),
                'id'          => 'recent_nb',
                'element'     => 'text',
                'label'      => $this->_('Listings'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'recent_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'recent_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'recent_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),

            /* ENDING SOON */
            array(
                'form_id'    => 'home_page_appearance',
                'subtitle'   => $this->_('Ending Soon'),
                'id'          => 'ending_nb',
                'element'     => 'text',
                'label'      => $this->_('Listings'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'ending_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'ending_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'ending_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),

            /* POPULAR */
            array(
                'form_id'    => 'home_page_appearance',
                'subtitle'   => $this->_('Popular'),
                'id'          => 'popular_nb',
                'element'     => 'text',
                'label'      => $this->_('Listings'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'popular_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'popular_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'home_page_appearance',
                'id'           => 'popular_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),


//            array(
//                'form_id' => 'home_page_listings',
//                'id' => 'reverse_hpfeat_nb',
//                'element' => 'text',
//                'label' => 'Home Page Featured Reverse Auctions',
//                'description' => 'Enter the maximum number of items that will be displayed in this section or leave empty to disable.',
//                'required' => false,
//                'validators' => array(
//                    'Digits',
//                ),
//                'attributes' => array(
//                    'class' => 'input-mini',
//                ),
//            ),
//            array(
//                'form_id' => 'home_page_listings',
//                'id' => 'reverse_recent_nb',
//                'element' => 'text',
//                'label' => 'Recently Listed Reverse Auctions',
//                'description' => 'Enter the maximum number of items that will be displayed in this section or leave empty to disable.',
//                'required' => false,
//                'validators' => array(
//                    'Digits',
//                ),
//                'attributes' => array(
//                    'class' => 'input-mini',
//                ),
//            ),
//            array(
//                'form_id' => 'home_page_listings',
//                'id' => 'reverse_wanted_nb',
//                'element' => 'text',
//                'label' => 'Recently Listed Wanted Ads',
//                'description' => 'Enter the maximum number of items that will be displayed in this section or leave empty to disable.',
//                'required' => false,
//                'validators' => array(
//                    'Digits',
//                ),
//                'attributes' => array(
//                    'class' => 'input-mini',
//                ),
//            ),
            /**
             * ++++++++++++++
             * CATEGORY PAGES FEATURED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'category_featured',
                'id'          => 'catfeat_nb',
                'element'     => 'text',
                'label'       => $this->_('Number of Featured Items'),
                'description' => $this->_('Leave empty or enter 0 to disable (applies to all the fields below as well).'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(24, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            /**
             * ++++++++++++++
             * DISPLAY FREE FEES ON FRONT END
             * ++++++++++++++
             */
            array(
                'form_id'      => 'display_free_fees',
                'id'           => 'display_free_fees',
                'element'      => 'checkbox',
                'label'        => $this->_('Display Free Fees on User End'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish for free fees to be displayed on the front end.'),
            ),
            /**
             * ++++++++++++++
             * CUSTOM START/END TIME OPTIONS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'custom_start_end_times',
                'id'           => 'enable_custom_start_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Custom Start Time'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the custom start time option for listings.'),
            ),
            array(
                'form_id'      => 'custom_start_end_times',
                'id'           => 'enable_custom_end_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Custom End Time'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the custom end time option for listings.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS SEARCH SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_title',
                'element'      => 'checkbox',
                'label'        => $this->_('By Title'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('This setting cannot be disabled, searching by listing titles is mandatory.'),
                'attributes'   => array(
                    'checked'  => true,
                    'disabled' => true,
                ),
            ),
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_subtitle',
                'element'      => 'checkbox',
                'label'        => $this->_('By Subtitle'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow searching for keywords in listing subtitles.'),
            ),
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_description',
                'element'      => 'checkbox',
                'label'        => $this->_('By Description'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow searching for keywords in listing descriptions.'),
            ),
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_category_name',
                'element'      => 'checkbox',
                'label'        => $this->_('By Category Names'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow searching for keywords in category names.'),
            ),
            /**
             * ++++++++++++++
             * CURRENCY SETTINGS
             * ++++++++++++++
             */
            array(
                'subtitle'     => $translate->_('Current Display Format:') . ' <b>' . $this->getView()->amount(3999) . '</b>',
                'form_id'      => 'currency_settings',
                'id'           => 'currency',
                'element'      => 'select',
                'label'        => $this->_('Default Currency'),
                'multiOptions' => $this->getCurrencies()->getMultiOptions(),
                'description'  => sprintf(
                    $translate->_('Select the site\'s default currency.<br>'
                        . '<b>Important</b>: Please <a href="%s">click here</a> to define which currencies will be available on the site.'),
                    $this->_view->url(array('module' => 'admin', 'controller' => 'tables', 'action' => 'index', 'table' => 'currencies'))),
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'      => 'currency_settings',
                'id'           => 'currency_format',
                'element'      => 'radio',
                'label'        => $this->_('Amount Display Format'),
                'multiOptions' => array(
                    1 => array(
                        $translate->_('US Format: 9,999.95'),
                    ),
                    2 => array(
                        $translate->_('EU Format: 9.999,95'),
                    ),
                ),
                'description'  => $this->_('Select the amount display format that will be applied for when displaying currency amounts on your website.'),
                'required'     => true,
            ),
            array(
                'form_id'     => 'currency_settings',
                'id'          => 'currency_decimals',
                'element'     => 'text',
                'label'       => $this->_('Decimal Digits'),
                'description' => $this->_('Enter the number of decimal digits that will be shown when displaying a currency amount.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'currency_settings',
                'id'           => 'currency_position',
                'element'      => 'radio',
                'label'        => $this->_('Symbol Position'),
                'multiOptions' => array(
                    1 => array(
                        $translate->_('Symbol before amount:') . ' ' . $settings['currency'] . ' 199',
                    ),
                    2 => array(
                        $translate->_('Amount before symbol:') . ' 199 ' . $settings['currency'],
                    ),
                ),
                'description'  => $this->_('Select the amount display format that will be applied for when displaying currency amounts on your website.'),
                'required'     => true,
            ),
            /**
             * ++++++++++++++
             * TITLE CHARACTER LENGTH
             * ++++++++++++++
             */
            array(
                'form_id'     => 'character_length',
                'id'          => 'character_length',
                'element'     => 'text',
                'label'       => $this->_('Title Character Length'),
                'description' => $this->_('Enter the maximum character length allowed for the listing title field.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTINGS APPROVAL
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_approval',
                'id'           => 'enable_listings_approval',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Listings Approval'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish for listings to require admin approval before they are displayed '
                        . 'on your website. Listings will require approval after being edited as well.'),
            ),
            /**
             * ++++++++++++++
             * SUBTITLE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_subtitle',
                'id'           => 'enable_subtitle',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Listing Subtitle'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish to allow sellers to add subtitles to their listings.'),
            ),
            /**
             * ++++++++++++++
             * IMAGES SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_max',
                'element'     => 'text',
                'label'       => $this->_('Number of Images'),
                'description' => $this->_('Enter the maximum number of images that can be added to a listing.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'mandatory_images',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory Images'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to require for at least one image to be added when a listing is created.'),
            ),
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => $this->_('Enter the maximum size an uploaded image can have.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'crop_images',
                'element'      => 'checkbox',
                'label'        => $this->_('Crop to Aspect Ratio'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish for uploaded images to be cropped when thumbnails are generated.'),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'remote_uploads',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Remote Images'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow adding images from remote locations by entering the direct link.'),
            ),
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_watermark',
                'element'     => 'text',
                'label'       => $this->_('Watermark Text'),
                'description' => $this->_('Enter a watermark text that will be applied to uploaded images, or leave empty for no watermark.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => false,
            ),

            /**
             * ++++++++++++++
             * MEDIA UPLOAD SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'media_upload',
                'id'          => 'videos_max',
                'element'     => 'text',
                'label'       => $this->_('Number of Videos'),
                'description' => $this->_('Enter the maximum number of videos that can be added to a listing. <br>'
                        . '<b>Important</b>: To disable this feature, enter 0 in the above field.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'media_upload',
                'id'          => 'videos_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => $this->_('Enter the maximum size an uploaded video can have.<br>'
                        . '<b>Note</b>: The maximum allowed size of a file that can be uploaded on your server is <b>' . ini_get('upload_max_filesize') . 'B</b>. <br>'
                        . 'If you wish to have this setting increased, you will need to contact your hosting provider.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'media_upload',
                'id'           => 'embedded_code',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Embedded Code'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow adding remote videos using embedded code. <br>'
                        . '<b>Eg</b>: For YouTube use the code provided by accessing "share" -> "embed" on any youtube video.'),
            ),
            /**
             * ++++++++++++++
             * DIGITAL DOWNLOADS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_max',
                'element'     => 'text',
                'label'       => $this->_('Digital Downloads'),
                'description' => $this->_('Enter the maximum number of downloadable files that can be added to a listing. <br>'
                        . '<b>Important</b>: To disable this feature, enter 0 in the above field.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_folder',
                'element'     => 'text',
                'label'       => $this->_('Digital Downloads Folder'),
                'description' => $translate->_('Please enter a folder relative to your document root, where the files will be stored.<br>'
                        . 'Your document root path is:') . ' ' . $basePath,
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),

            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => $this->_('Enter the maximum size an uploaded file can have.<br>'
                        . '<b>Note</b>: The maximum allowed size of a file that can be uploaded on your server is <b>' . ini_get('upload_max_filesize') . 'B</b>. <br>'
                        . 'If you wish to have this setting increased, you will need to contact your hosting provider.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_disclaimer',
                'element'     => 'textarea',
                'label'       => $this->_('Downloads Disclaimer'),
                'description' => $this->_('Enter a disclaimer paragraph which will be shown to users that will be downloading files from the website before proceeding with the download.'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'rows'  => '6',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * SALE TRANSACTION FEE REFUNDS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'sale_fee_refunds',
                'id'           => 'enable_sale_fee_refunds',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Sale Transaction Fee Refunds'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish to allow your users to request refunds for sale transaction fees.<br>'
                        . 'The refunded amounts will be credited to the site account balances of the users.'),
            ),
            array(
                'form_id'     => 'sale_fee_refunds',
                'id'          => 'sale_fee_refunds_range',
                'element'     => '\Ppb\Form\Element\Range',
                'label'       => $this->_('Interval'),
                'description' => $this->_('Enter the interval when the payer is able to request a refund for a sale transaction fee, or leave both fields empty if you don\'t want to set an interval.'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * SOCIAL NETWORK LINKS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'social_network_links',
                'id'           => 'enable_social_network_links',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Social Network Links'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish for social network links to be enabled throughout your website.'),
            ),
            /**
             * ++++++++++++++
             * COOKIE USAGE CONFIRMATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'cookie_usage',
                'id'           => 'enable_cookie_usage_confirmation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Cookie Usage Confirmation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the cookie usage confirmation option.<br>'
                        . 'If this feature is enabled, users will be notified that the site uses cookies and will need to agree in order to hide the message.'),
            ),
            array(
                'form_id'     => 'cookie_usage',
                'id'          => 'cookie_usage_message',
                'element'     => 'textarea',
                'label'       => $this->_('Cookie Usage Confirmation Message'),
                'description' => $this->_('Enter the cookie confirmation message that will be displayed.'),
                'required'    => ($this->getData('enable_cookie_usage_confirmation')) ? true : false,
                'attributes'  => array(
                    'rows'  => '3',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * GOOGLE RECAPTCHA
             * ++++++++++++++
             */
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'enable_recaptcha',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable reCAPTCHA'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the Google reCAPTCHA plugin. <br>'
                        . 'To enable the plugin for your site, you will need to create an account '
                        . '<a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">here</a>.'),
            ),
            array(
                'form_id'    => 'google_recaptcha',
                'id'         => 'recaptcha_public_key',
                'element'    => 'text',
                'label'      => $this->_('reCAPTCHA Public Key'),
                'required'   => ($this->getData('enable_recaptcha')) ? true : false,
                'validators' => array(
                    'NoHtml',
                ),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'    => 'google_recaptcha',
                'id'         => 'recaptcha_private_key',
                'element'    => 'text',
                'label'      => $this->_('reCAPTCHA Private Key'),
                'required'   => ($this->getData('enable_recaptcha')) ? true : false,
                'validators' => array(
                    'NoHtml',
                ),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'subtitle'     => $this->_('reCAPTCHA Usage'),
                'id'           => 'recaptcha_registration',
                'element'      => 'checkbox',
                'label'        => $this->_('Registration Process'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable reCAPTCHA for the registration process.'),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'recaptcha_contact_us',
                'element'      => 'checkbox',
                'label'        => $this->_('Contact Us Page'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable reCAPTCHA for the contact us page.'),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'recaptcha_email_friend',
                'element'      => 'checkbox',
                'label'        => $this->_('Email Listing to Friend Page'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable reCAPTCHA for the email listing to friend page.'),
            ),
            /**
             * ++++++++++++++
             * BCC EMAILS TO ADMIN
             * ++++++++++++++
             */
            array(
                'form_id'      => 'bcc_emails',
                'id'           => 'bcc_emails',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable BCC Emails to Admin'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => sprintf(
                    $this->_('Check the above checkbox in order for emails sent between site users, together with sale notification emails to be sent '
                        . 'to the main admin email address as well (%s).'), $settings['admin_email']),
            ),
            /**
             * ++++++++++++++
             * RECENTLY VIEWED LISTINGS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'recently_viewed_listings',
                'id'           => 'enable_recently_viewed_listings',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Recently Viewed Listings Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the recently viewed listings box, which will appear for users '
                        . 'by default in the footer of your website.'),
            ),
            array(
                'form_id'     => 'recently_viewed_listings',
                'id'          => 'enable_recently_viewed_listings_expiration',
                'element'     => 'text',
                'label'       => $this->_('Expiration Time'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('Enter the number of hours after a listing will be removed from the recently viewed table.'),
                'required'    => $this->getData('enable_recently_viewed_listings') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * BULK LISTER
             * ++++++++++++++
             */
            array(
                'form_id'      => 'bulk_lister',
                'id'           => 'enable_bulk_lister',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Bulk Lister'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the bulk lister tool. The bulk lister will parse CSV files.'),
            ),
            /**
             * ++++++++++++++
             * AUCTIONS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'auctions_settings',
                'id'           => 'enable_auctions',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Auctions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the listing of auctions on your website.'),
                'required'     => (!$settings['enable_products']) ? true : false,
            ),
            array(
                'form_id'     => 'auctions_settings',
                'subtitle'    => $this->_('Auctions Editing Time Limit'),
                'id'          => 'auctions_editing_hours',
                'element'     => 'text',
                'label'       => $this->_('Time Limit'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('If the remaining duration of an auction will be less than the above set time limit, the seller will not be allowed to edit it.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Auctions Sniping Feature'),
                'id'           => 'enable_auctions_sniping',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If this feature is enabled, the duration of an auction will be extended if a bid is placed when the auction is about to close. '),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'auctions_sniping_minutes',
                'element'     => 'text',
                'label'       => $this->_('Sniping Duration'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('If the remaining duration of an auction will be less than the above set duration, the time will be extended to the above setting.'),
                'required'    => $this->getData('enable_auctions_sniping') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Bid Retraction'),
                'id'           => 'enable_bid_retraction',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to allow bidders to retract their bids from auctions.'),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'bid_retraction_hours',
                'element'     => 'text',
                'label'       => $this->_('Bid Retraction Limit'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('Enter the minimum required time left on an auction for the bid retraction feature to be allowed.'),
                'required'    => $this->getData('enable_auctions_sniping') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Change Auction Duration when a Bid is Placed'),
                'id'           => 'enable_change_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you want for the auction duration to be changed after the first bid has been placed.'),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'change_duration_days',
                'element'     => 'text',
                'label'       => $this->_('New Duration'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('If the duration left on the auction is over the value above, then it will be automatically reset to the value above.'),
                'required'    => $this->getData('enable_change_duration') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
//            array(
//                'form_id'      => 'auctions_settings',
//                'subtitle'     => $this->_('Second Chance Purchasing'),
//                'id'           => 'second_chance_purchasing',
//                'element'      => 'checkbox',
//                'label'        => $this->_('Enable Feature'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => $this->_('With second chance purchasing, sellers will be able to manually select a winner if the '
//                        . 'automatically appointed winner didn\'t complete the purchase.<br>'
//                        . '<b>Important</b>: This feature will be available for standard auctions not marked as paid only.'),
//            ),
//            array(
//                'form_id'     => 'auctions_settings',
//                'id'          => 'second_chance_purchasing_days',
//                'element'     => 'text',
//                'label'       => $this->_('Enter Interval'),
//                'suffix'      => 'days',
//                'description' => $this->_('You can set a number of days after which the "Second Chance" feature will become available '
//                        . 'for sold items, or enter 0 if you wish for this feature to be available right away.'),
//                'validators'  => array(
//                    'Digits',
//                ),
//                'attributes'  => array(
//                    'class' => 'form-control input-mini',
//                ),
//            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Close Auctions Before End Time'),
                'id'           => 'close_auctions_end_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If this setting is enabled, sellers will be able to close their auctions early even if there is a high bid placed. <br>'
                        . 'By default, auctions can be closed ahead of the closing date only if there are no bids or if the high bid is '
                        . 'lower than the reserve price.'),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Proxy Bidding'),
                'id'           => 'proxy_bidding',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If this setting is enabled, bidders will be able to place a maximum bid on an auction, but the active bid set will be the '
                        . 'minimum amount required for them to be high bidders.<br>'
                        . '<a href="http://en.wikipedia.org/wiki/Proxy_bid" target="_blank">Click here</a> for more information on this feature.'),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Limit Number of Bids / Offers per User'),
                'id'           => 'enable_limit_bids',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If you enable this feature, your sellers will be able to limit the number of bids and/or offers a bidder can '
                        . 'place on an auction (proxy bids are not taken into consideration).'),
            ),
            /**
             * ++++++++++++++
             * PRODUCTS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'products_settings',
                'id'           => 'enable_products',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Products'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the listing of products (items with fixed price) on your website.'),
                'required'     => (!$settings['enable_auctions']) ? true : false,
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Unlimited Duration'),
                'id'           => 'enable_unlimited_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Unlimited Duration'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If unlimited duration is enabled, sellers will be able to list products without a closing date.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'id'           => 'force_unlimited_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Force Unlimited Duration'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('With this option enabled, sellers will only be able to list products with no closing date.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Shopping Cart'),
                'id'           => 'enable_shopping_cart',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Shopping Cart'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the shopping cart feature.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'id'           => 'shopping_cart_applies',
                'element'      => 'radio',
                'label'        => $this->_('Shopping Cart Applies'),
                'multiOptions' => array(
                    'global'         => array(
                        $translate->_('Globally'),
                        $translate->_('The shopping cart will be used for all products listed on the site.'),
                    ),
                    'store_owners'   => array(
                        $translate->_('Store Owners'),
                        $translate->_('The shopping cart will be used for products listed by store owners only.')
                    ),
                    'store_listings' => array(
                        $translate->_('Store Listings'),
                        $translate->_('The shopping cart will be used only for products listed in stores.'),
                    ),
                ),
                'description'  => $this->_('Select when the shopping cart module will be used.'),
            ),
            array(
                'form_id'     => 'products_settings',
                'id'          => 'pending_sales_listings_expire_hours',
                'element'     => 'text',
                'label'       => $this->_('Reserve Stock'),
//                'prefix'      => $this->_('after'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('Enter the duration for which products added in a shopping cart have their stock reserved, or leave empty to disable this feature.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Force Payment'),
                'id'           => ($settings['enable_products']) ? 'enable_force_payment' : false,
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Force Payment'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If enabled, a sale will be completed only when the sale is marked as paid.'),
            ),
            array(
                'form_id'     => 'products_settings',
                'id'          => 'force_payment_limit',
                'element'     => 'text',
                'label'       => $this->_('Force Payment Time Limit'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('Enter the time limit after which unpaid sales are reverted.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * BUY OUT FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'buy_out',
                'id'           => 'enable_buyout',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Buy Out'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable buy out feature for auctions.'),
                'required'     => (!$settings['enable_auctions'] && !$settings['enable_make_offer']) ? true : false,
            ),
            array(
                'form_id'      => 'buy_out',
                'id'           => 'always_show_buyout',
                'element'      => 'checkbox',
                'label'        => $this->_('Always Show Buy Out Button'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If this setting is enabled, when listing an auction with buy out enabled, the option will remain active even if there are bids above the reserve price posted.'),
            ),
            /**
             * ++++++++++++++
             * MAKE OFFER FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'make_offer',
                'id'           => 'enable_make_offer',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Make Offer'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the make offer feature for auctions and products.'),
            ),
            array(
                'form_id'      => 'make_offer',
                'id'           => 'show_make_offer_ranges',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Offer Ranges'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('With this option enabled, the allowed offer ranges set by the seller will be displayed on the listing details page.'),
            ),
            /**
             * ++++++++++++++
             * ITEMS SWAPPING FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'items_swapping',
                'id'           => 'enable_swap',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Items Swapping Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the items swapping feature for auctions and products.'),
            ),
            /**
             * ++++++++++++++
             * SHIPPING SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_shipping',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Shipping Module'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the shipping module on the site.'),
                'attributes'   => array(
                    'id'      => 'enable_shipping',
                    'onclick' => 'javascript:checkShippingFields();',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkShippingFields() {
                            if ($('#enable_shipping').is(':checked')) {  
                                $('.shipping-options').closest('.form-group').show();
                            }
                            else {
                                $('.shipping-options').prop('checked', false).closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {             
                            checkShippingFields();
                        });
                    </script>",
            ),
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_pickups',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Pick-ups Option'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow sellers to enable the pick-up option for their items.'),
                'attributes'   => array(
                    'class' => 'shipping-options',
                ),
            ),
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_returns',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Returns Option'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow sellers to specify a returns policy for their items.'),
                'attributes'   => array(
                    'class' => 'shipping-options',
                ),
            ),
            /**
             * ++++++++++++++
             * AUTO RELISTS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'auto_relists',
                'id'           => 'auto_relist',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Auto Relists'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the auto relists feature.'),
            ),
            array(
                'form_id'     => 'auto_relists',
                'id'          => 'max_auto_relists',
                'element'     => 'text',
                'label'       => $this->_('Maximum Auto Relists Allowed'),
                'description' => $this->_('Enter the maximum number of auto relists that can be entered when a listing is created.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auto_relists',
                'id'           => 'relist_method',
                'element'      => 'select',
                'label'        => $this->_('Relist Method'),
                'description'  => $this->_('Select the method of relisting that will be applied for your listings:<br>'
                        . '- New: a new record will be created, and the old listing will be marked deleted<br>'
                        . '- Same: the same listing will be re-opened, and any sales, bids etc will be removed'),
                'multiOptions' => array(
                    'new'  => 'New',
                    'same' => 'Same',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-small',
                ),
            ),
            /**
             * ++++++++++++++
             * MARKED DELETED LISTINGS REMOVAL
             * ++++++++++++++
             */
            array(
                'form_id'      => 'marked_deleted',
                'id'           => 'marked_deleted_listings_removal',
                'element'      => 'checkbox',
                'label'        => $this->_('Automatic Marked Deleted Listings Removal'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the automatic removal of marked deleted listings.<br>'
                        . '<b>Note</b>: The process will be run using the cron service.'),
            ),
            /**
             * ++++++++++++++
             * CLOSED LISTINGS DELETION
             * ++++++++++++++
             */
            array(
                'form_id'     => 'closed_listings_deletion',
                'id'          => 'closed_listings_deletion_days',
                'element'     => 'text',
                'label'       => $this->_('Closed Listings Deletion'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('Enter a duration in days after which closed listings should be automatically marked deleted, or leave empty to disable this feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * USERS MESSAGING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'users_messaging',
                'id'           => 'enable_messaging',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Messaging'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the users messaging module.<br>'
                        . '<b>Note</b>: All messages sent through the site can be read/managed by the administrator from the Tools -> Messages page.'),
            ),
            array(
                'form_id'      => 'users_messaging',
                'id'           => 'enable_public_questions',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Public Questions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('To enable the posting of public questions on listings, you can check the checkbox above.<br>'
                        . 'By default, only private questions on listings will be allowed.'),
            ),
            /**
             * ++++++++++++++
             * ADDITIONAL CATEGORY LISTING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'additional_category_listing',
                'id'           => 'addl_category_listing',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the listing of items in an additional category.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS COUNTERS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_counters',
                'id'           => 'category_counters',
                'element'      => 'checkbox',
                'label'        => $this->_('Category Counters'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the display of category counters on selected pages. <br>'
                        . 'Counters are updated only using the cron job. If they are out of sync, please use the initialization tool below.'),
            ),
//            array(
//                'form_id'      => 'listings_counters',
//                'id'           => 'search_counters',
//                'element'      => 'checkbox',
//                'label'        => $this->_('Search Filter Counters'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => $this->_('Check the above checkbox to enable the display of listings counters on the search browse pages. The counters will '
//                        . 'display for categories and any custom fields for which this setting can be applied.<br>'
//                        . '<b>Important</b>: This functionality may increase your server load on larger sites, VPS or dedicated hosting is highly recommended.'),
//            ),
            array(
                'form_id'      => 'listings_counters',
                'id'           => 'hide_empty_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Hide Empty Categories/Options'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to hide categories or filtering options that do not contain any listings.'),
            ),
            array(
                'form_id'     => 'listings_counters',
                'id'          => 'init_category_counters',
                'element'     => 'button',
                'label'       => $this->_('Initialize Counters'),
                'value'       => $this->_('Initialize'),
                'description' => sprintf(
                    $translate->_('Click on the button above to initialize your site\'s category counters.'
                            . '<div>There are a total of <span id="category-total-listings">%s</span> listings to be counted.</div>'), $totalListings)
                    . '<div id="category-counters-progress" class="text-info"></div>',
                'attributes'  => array(
                    'class' => 'btn btn-default',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTINGS TERMS & CONDITIONS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_terms_box',
                'id'           => 'listing_terms_box',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Listing Terms & Conditions Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to display the listing terms and conditions box on the listing setup process.'),
            ),
            array(
                'form_id'     => 'listing_terms_box',
                'id'          => 'listing_terms_content',
                'element'     => 'textarea',
                'label'       => $this->_('Content'),
                'description' => $this->_('Enter the terms and conditions that will be displayed in the box.'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * USERS PHONE NUMBERS ON SUCCESSFUL SALES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'user_phone_numbers',
                'id'           => 'sale_phone_numbers',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to show the phone numbers of users when displaying their full addresses.'),
            ),
            /**
             * ++++++++++++++
             * SELLER'S OTHER ITEMS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'other_items_seller',
                'id'           => 'other_items_seller',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the display of the other items from the seller box on the listing details pages.'),
            ),

//            /**
//             * ++++++++++++++
//             * REVERSE AUCTIONS
//             * ++++++++++++++
//             */
//            array(
//                'form_id'      => 'reverse_auctions_settings',
//                'id'           => 'enable_reverse_auctions',
//                'element'      => 'checkbox',
//                'label'        => 'Enable Reverse Auctions',
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => 'Check the above checkbox to display the listing of reverse auctions on your website.<br>
//                    <b>Explanation</b><br>
//                    In a reverse auction, the roles of the buyer and seller are inversed. <br>
//                    Sellers can make a single bid on a reverse auction, the bid amount however can be edited at any time until the auction closes.<br>
//                    After the auction has closed, the buyer will manually award the winning bid.',
//            ),
//            /**
//             * ++++++++++++++
//             * WANTED ADS
//             * ++++++++++++++
//             */
//            array(
//                'form_id'      => 'wanted_ads_settings',
//                'id'           => 'enable_wanted_ads',
//                'element'      => 'checkbox',
//                'label'        => 'Enable Wanted Ads',
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => 'Check the above checkbox to display the listing of wanted ads on your website.',
//            ),
//            /**
//             * ++++++++++++++
//             * FIRST BIDDER AUCTIONS
//             * ++++++++++++++
//             */
//            array(
//                'form_id'      => 'first_bidder_auctions_settings',
//                'id'           => 'enable_first_bidder_auctions',
//                'element'      => 'checkbox',
//                'label'        => 'Enable First Bidder Auctions',
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => 'Check the above checkbox to display the listing of first bidder auctions on your website.<br>
//                    <b>Explanation</b><br>
//                    In a first bidder auction, the bid decreases automatically periodically, with an amount set by the poster.<br>
//                    The first user who bids on the auction will win. Users can only bid the current active bid amount, which is set automatically.',
//            ),
            /**
             * ++++++++++++++
             * STORES SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'enable_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'disabled'     => ($settings['enable_products']) ? false : true,
                'description'  => $this->_('Check the above checkbox to enable the stores module on your website.<br>'
                        . '<b>Important</b>: Only products can be listed in store. If the listing of products is disabled, stores will be disabled as well.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'hide_empty_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Hide Empty Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order for stores with no items to be hidden when accessing the stores browse page.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'store_only_mode',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Store Only Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the store only mode functionality. <br>'
                        . 'With this mode enabled, users will need to open a store in order to be able to list items.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'custom_stores_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Custom Stores Categories'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable allow your users to create their own custom categories.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'enable_auctions_in_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Auctions in Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow your users to list auctions in their stores. '
                    . 'By default, only products can be listed in stores.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'stores_force_list_in_both',
                'element'      => 'checkbox',
                'label'        => $this->_('Disable List In Select Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to disable the "List In" selector. '
                    . 'All listings in this case will be listed in both the site and store.'),
            ),
            /**
             * ++++++++++++++
             * TAX SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'enable_tax_fees',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Tax on Site Fees'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable tax for site fees.'),
            ),
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'tax_fees_type',
                'element'      => 'select',
                'label'        => $this->_('Tax Type'),
                'description'  => $this->_('Select the tax that will be applied for site fees.'),
                'multiOptions' => $this->getTaxTypes()->getMultiOptions(),
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'enable_tax_listings',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Sellers to Apply Tax'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to allow sellers to apply tax on their listings.'),
            ),
            /**
             * --------------
             * SITE FEES MANAGEMENT
             * --------------
             */
            array(
                'form_id'      => 'fees_category',
                'id'           => 'category_id',
                'element'      => 'select',
                'label'        => $this->_('Select Category'),
                'multiOptions' => $this->getCategories()->getMultiOptions("parent_id IS NULL AND custom_fees='1'", null,
                        $translate->_('Default')),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                    'id'    => 'category-selector',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).ready(function() { 
                            $('#category-selector').change(function() { 
                                var categoryId = $(this).val();
                                var action = $(this).closest('form').attr('action');
                                var url = action.replace(/(\/category_id)(\/[0-9]+)/g, '');
                                
                                $(location).attr('href', url + '/category_id/' + categoryId);
                            });
                        });
                    </script>",
            ),
            /**
             * ++++++++++++++
             * USER SIGN-UP
             * ++++++++++++++
             */
            array(
                'form_id'     => 'signup',
                'id'          => Fees::SIGNUP,
                'element'     => 'text',
                'label'       => $this->_('User Signup Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter an amount in the box above if you wish to charge your users for signing up on the site.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * SALE FEE PAYER RADIO BUTTON
             * ++++++++++++++
             */
            array(
                'form_id'      => 'sale',
                'id'           => 'sale_fee_payer',
                'element'      => 'radio',
                'label'        => $this->_('Paid By'),
                'multiOptions' => array(
                    'buyer'  => array(
                        $translate->_('Buyer'),
                    ),
                    'seller' => array(
                        $translate->_('Seller'),
                    ),
                ),
                'description'  => $this->_('Select which party would pay for the sale transaction fee.'),
            ),
            /**
             * ++++++++++++++
             * TIERS FEES TABLES
             * ++++++++++++++
             */
            array(
                'form_id'  => 'tiers',
                'id'       => 'id',
                'element'  => 'hidden',
                'multiple' => true,
            ),
            array(
                'form_id'  => 'tiers',
                'id'       => 'delete',
                'element'  => 'checkbox',
                'multiple' => true,
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'amount',
                'element'    => 'text',
                'label'      => $this->_('Fee Amount'),
                'multiple'   => true,
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
//                'validators' => array(
//                    'Numeric',
//                ),
            ),
            array(
                'form_id'      => 'tiers',
                'id'           => 'calculation_type',
                'element'      => 'select',
                'label'        => $this->_('Calculation Type'),
                'multiple'     => true,
                'multiOptions' => array(
                    'flat'    => $settings['currency'],
                    'percent' => '%',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-small',
                    'size'  => 1,
                ),
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'tier_from',
                'element'    => 'text',
                'label'      => $this->_('Range From'),
                'multiple'   => true,
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
//                'validators' => array(
//                    'Numeric',
//                ),
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'tier_to',
                'element'    => 'text',
                'label'      => $this->_('Range To'),
                'multiple'   => true,
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
//                'validators' => array(
//                    'Numeric',
//                ),
            ),
            /**
             * ++++++++++++++
             * HOME PAGE FEATURED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'hpfeat',
                'id'          => Fees::HPFEAT,
                'element'     => 'text',
                'label'       => $this->_('Home Page Featured Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing home page featured items.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * CATEGORY PAGES FEATURED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'catfeat',
                'id'          => Fees::CATFEAT,
                'element'     => 'text',
                'label'       => $this->_('Category Pages Featured Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing category pages featured items.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * HIGHLIGHTED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'highlighted',
                'id'          => Fees::HIGHLIGHTED,
                'element'     => 'text',
                'label'       => $this->_('Highlighted Listing Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when enabling the highlighted listing feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
//            /**
//             * ++++++++++++++
//             * BOLD LISTINGS [UNUSED]
//             * ++++++++++++++
//             */
//            array(
//                'form_id'     => 'bold',
//                'id'          => Fees::BOLD,
//                'element'     => 'text',
//                'label'       => $this->_('Bold Listing Fee'),
//                'prefix'      => $settings['currency'],
//                'description' => $this->_('Enter a fee that will apply when enabling the bold listing feature.'),
//                'attributes'  => array(
//                    'class' => 'form-control input-mini',
//                ),
//                'validators'  => array(
//                    'Numeric',
//                ),
//            ),
            /**
             * ++++++++++++++
             * LISTING SUBTITLE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'subtitle',
                'id'          => Fees::SUBTITLE,
                'element'     => 'text',
                'label'       => $this->_('Listing Subtitle Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when adding a subtitle for a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTING IMAGES
             * ++++++++++++++
             */
            array(
                'form_id'     => 'images',
                'id'          => Fees::IMAGES,
                'element'     => 'text',
                'label'       => $this->_('Images Upload Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will be charged for each image uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'     => 'images',
                'id'          => Fees::NB_FREE_IMAGES,
                'element'     => 'text',
                'label'       => $this->_('Free Images'),
                'description' => $this->_('Enter the number of free images that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * MEDIA UPLOAD
             * ++++++++++++++
             */
            array(
                'form_id'     => 'media',
                'id'          => Fees::MEDIA,
                'element'     => 'text',
                'label'       => $this->_('Media Upload Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when adding media to a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'     => 'media',
                'id'          => Fees::NB_FREE_MEDIA,
                'element'     => 'text',
                'label'       => $this->_('Free Media Items'),
                'description' => $this->_('Enter the number of free media items that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * DIGITAL DOWNLOADS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'digital_downloads_fee',
                'id'          => Fees::DIGITAL_DOWNLOADS,
                'element'     => 'text',
                'label'       => $this->_('Digital Downloads Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when creating a listing having digital download option enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            array(
                'form_id'     => 'digital_downloads_fee',
                'id'          => Fees::NB_FREE_DOWNLOADS,
                'element'     => 'text',
                'label'       => $this->_('Free Digital Downloads'),
                'description' => $this->_('Enter the number of free digital downloads that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * ADDITIONAL CATEGORY LISTING
             * ++++++++++++++
             */
            array(
                'form_id'     => 'addl_category',
                'id'          => Fees::ADDL_CATEGORY,
                'element'     => 'text',
                'label'       => $this->_('Additional Category Listing Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item in more than one category.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * BUY OUT FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'buyout',
                'id'          => Fees::BUYOUT,
                'element'     => 'text',
                'label'       => $this->_('Buy Out Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item with buy out enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * RESERVE PRICE FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'reserve_price',
                'id'          => Fees::RESERVE,
                'element'     => 'text',
                'label'       => $this->_('Reserve Price Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when enabling reserve price on an auction.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * MAKE OFFER FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'make_offer_fee',
                'id'          => Fees::MAKE_OFFER,
                'element'     => 'text',
                'label'       => $this->_('Make Offer Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item with make offer enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            /**
             * ++++++++++++++
             * ITEM SWAPPING FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'item_swap',
                'id'          => Fees::ITEM_SWAP,
                'element'     => 'text',
                'label'       => $this->_('Item Swapping Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when an item is swapped.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
        );
    }

}

