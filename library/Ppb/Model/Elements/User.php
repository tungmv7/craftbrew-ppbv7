<?php

/**
 *
 * PHP Pro Bid $Id$ tEjlngvwvLF7KFwpv3OUuoP3ePAms+STun7lqkZ8qH8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * we have the following sub-forms:
 *
 * basic - username, password, email address
 * advanced - date of birth, custom fields
 * address - address related fields (address book table)
 * user - user account details (gateway settings, bank details, etc)
 */
namespace Ppb\Model\Elements;

use Cube\Validate,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table,
    Ppb\Service\Users as UsersService,
    Ppb\Service\Table\PaymentGateways as PaymentGatewaysService,
    Ppb\Service\Table\StoresSubscriptions as StoresSubscriptionsService;

class User extends AbstractElements
{
    /**
     *
     * form id
     *
     * @var array
     */
    protected $_formId = array();

    /**
     *
     * user object
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;


    /**
     *
     * class constructor
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->setUser();

        $this->_formId = (array)$formId;
    }

    /**
     *
     * get user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     *
     * set user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return \Ppb\Model\Elements\User
     */
    public function setUser(UserModel $user = null)
    {
        if ($user === null) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');
            if (!$user instanceof UserModel) {
                $user = null;
            }
        }

        $this->_user = $user;

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
        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        if (($user = $this->getUser()) instanceof UserModel) {
            $userId = $user->getData('id');
            $storeActive = $user->getData('store_active');
            $isSeller = $user->canList();
        }
        else {
            $userId = null;
            $storeActive = false;
            $isSeller = false;
        }

        $countries = $this->getLocations()->getMultiOptions();

        $country = ($this->getData('country') === null) ? $this->getFirstElement($countries) : $this->getData('country');
        $states = $this->getLocations()->getMultiOptions(
            $country);


        $customFields = $this->getCustomFields()->getFields(
            array(
                'type'   => 'user',
                'active' => 1,
            ))->toArray();


        /* create validators */
        $usernameAlpha = new Validate\Alphanumeric();
        $usernameAlpha->setMessage("'%s' contains prohibited characters.");


        $usernameNoRecordExists = new Validate\Db\NoRecordExists(array(
            'table' => new Table\Users(),
            'field' => 'username',
        ));
        $usernameNoRecordExists->setMessage($translate->_("The %s '%value%' is not available."));


        $emailNoRecordExists = new Validate\Db\NoRecordExists(array(
            'table' => new Table\Users(),
            'field' => 'email',
        ));
        $emailNoRecordExists->setMessage($translate->_("The %s '%value%' has already been registered."));


        $agreeTermsValidator = new Validate\NotEmpty();
        $agreeTermsValidator->setMessage('You must greater than 18 years old and agree to our terms and conditions in order to complete the registration.');

        $birthDateValidator = new Validate\LessThan();
        $birthDateValidator->setMaxValue(date('Y-m-d', time() - ($settings['min_reg_age'] * 365 * 86400)))
            ->setMessage(sprintf($translate->_('You must be at least %s years old in order to be able to register.'),
                $settings['min_reg_age']));

        $paymentGatewaysService = new PaymentGatewaysService();

        $gateways = $paymentGatewaysService->getData($userId, null, true);

        $gatewayFields = array();

        foreach ($gateways as $gateway) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className();
                foreach ((array)$gatewayModel->getElements() as $element) {
                    if (isset($gateway[$element['id']])) {
                        $element['value'] = $gateway[$element['id']];
                    }
                    $gatewayFields[] = $element;
                }
            }
        }

        $storesSubscriptions = new StoresSubscriptionsService();

        $categoriesSelect = $this->getCategories()->getTable()->select()
            ->where('parent_id is null');

        if ($userId) {
            $categoriesSelect->where('user_id is null OR user_id = ?', $userId);
        }
        else {
            $categoriesSelect->where('user_id is null');
        }

        $array = array(
            array(
                'form_id'  => 'global',
                'id'       => 'id',
                'element'  => 'hidden',
                'bodyCode' => "
                    <script type=\"text/javascript\">
                        function checkFormFields() {
                            if ($('input:radio[name=\"business_account\"]:checked').val() == '1') {
                                $('input:text[name=\"company_name\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"company_name\"]').val('').closest('.form-group').hide();
                            }

                            if ($('input:radio[name=\"quantity_description\"]:checked').val() == '1') {
                                $('input:text[name=\"quantity_low_stock\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"quantity_low_stock\"]').val('').closest('.form-group').hide();
                            }

                            if ($('input:checkbox[name=\"vacation_mode\"]').is(':checked')) {
                                $('input[name=\"vacation_mode_return_date\"]').closest('.form-group').show();
                            }
                            else {
                                $('input[name=\"vacation_mode_return_date\"]').val('').closest('.form-group').hide();
                            }

                            if ($('input:radio[name=\"disable_emails\"]:checked').val() == '1') {
                                $('[name=\"disable_seller_notifications\"]').closest('.form-group').hide();
                                $('[name=\"disable_offers_notifications\"]').closest('.form-group').hide();
                                $('[name=\"disable_messaging_notifications\"]').closest('.form-group').hide();
                            }
                            else {
                                $('[name=\"disable_seller_notifications\"]').closest('.form-group').show();
                                $('[name=\"disable_offers_notifications\"]').closest('.form-group').show();
                                $('[name=\"disable_messaging_notifications\"]').closest('.form-group').show();
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
                'form_id'      => 'user',
                'id'           => 'business_account',
                'element'      => 'radio',
                'label'        => $this->_('Account Type'),
                'multiOptions' => array(
                    0 => $translate->_('Personal'),
                    1 => $translate->_('Business'),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id' => 'address',
                'id'      => 'address_id',
                'element' => 'hidden',
            ),
            array(
                'form_id'     => 'address',
                'subtitle'    => $this->_('Address'),
                'id'          => 'name',
                'element'     => '\\Ppb\\Form\\Element\\FullName',
                'label'       => $this->_('Name'),
                'required'    => true,
                'description' => $this->_('Enter your full name.'),
                'attributes'  => array(
                    'class'       => 'form-control input-default',
                    'placeholder' => array(
                        'first' => $translate->_('First Name'),
                        'last'  => $translate->_('Last Name'),
                    ),
                ),
                'fieldLabels' => array(
                    'first' => $translate->_('First Name'),
                    'last'  => $translate->_('Last Name'),
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'address',
                'id'          => 'address',
                'element'     => 'text',
                'label'       => $this->_('Address'),
                'required'    => true,
                'description' => $this->_('Enter your address.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'address',
                'id'          => 'city',
                'element'     => 'text',
                'label'       => $this->_('City'),
                'required'    => true,
                'description' => $this->_('Enter the city you live in.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'address',
                'id'           => 'country',
                'element'      => 'select',
                'label'        => $this->_('Country'),
                'multiOptions' => $countries,
                'required'     => true,
                'description'  => $this->_('Enter the country you live in.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function ChangeState() {
                            var countryId = $('[name=\"country\"]').val();
                            $.post(
                                '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'select-location')) . "',
                                {
                                    id: $('[name=\"country\"]').val(),
                                    name: 'state'
                                },
                                function (data) {
                                    var div = $('[name=\"state\"]').closest('div');
                                    $('[name=\"state\"]').remove();
                                    div.prepend(data);
                                }
                            );
                        }

                        $(document).on('change', '[name=\"country\"]', function() {
                            ChangeState();
                        });
                    </script>"
            ),
            array(
                'form_id'      => 'address',
                'id'           => 'state',
                'element'      => (count($states) > 0) ? 'select' : 'text',
                'label'        => $this->_('State/County'),
                'multiOptions' => $states,
                'required'     => true,
                'description'  => $this->_('Enter the state/county you live in.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'address',
                'id'          => 'zip_code',
                'element'     => 'text',
                'label'       => $this->_('Zip/Post Code'),
                'required'    => true,
                'description' => $this->_('Enter your zip/post code.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'address',
                'id'          => 'phone',
                'element'     => 'text',
                'label'       => $this->_('Phone'),
                'description' => $this->_('Enter your phone number.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Phone',
                ),
            ),
            array(
                'form_id'     => 'advanced',
                'id'          => 'birthdate',
                'element'     => ($settings['min_reg_age'] > 0) ? '\\Ppb\\Form\\Element\\Date' : false,
                'label'       => $this->_('Date of Birth'),
                'description' => $this->_('Enter your birthdate.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    $birthDateValidator,
                ),
                'customData'  => array(
                    'formData' => array(
                        'dateFormat'  => 'yy-mm-dd',
                        'changeMonth' => true,
                        'changeYear'  => true,
                        'yearRange'   => '1915:2015',
                        'maxDate'     => 'new Date()',
                    ),
                ),
            ),
            array(
                'form_id'     => 'user',
                'subtitle'    => $this->_('Additional Information'),
                'id'          => 'company_name',
                'element'     => 'text',
                'label'       => $this->_('Company Name'),
                'description' => $this->_('Enter your company\'s name.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => ($this->getData('business_account') == 1) ? true : false,
            ),
            array(
                'form_id'     => 'user',
                'id'          => 'bank_details',
                'element'     => 'textarea',
                'label'       => $this->_('Bank Details'),
                'description' => $this->_('Enter your bank account details (optional).'),
                'attributes'  => array(
                    'rows'  => 6,
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'user',
                'id'          => 'sale_invoices_content',
                'element'     => 'textarea',
                'label'       => $this->_('Sales Invoices Custom Content'),
                'description' => $this->_('Enter any custom content you might want to add on your sale invoices.'),
                'attributes'  => array(
                    'rows'  => 4,
                    'class' => 'form-control',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => array('basic', 'admin'),
                'subtitle'    => $this->_('Account Details'),
                'id'          => 'username',
                'element'     => 'text',
                'label'       => $this->_('Username'),
                'description' => $this->_('Choose a username for your account.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    $usernameAlpha,
                    $usernameNoRecordExists,
                ),
            ),
            array(
                'form_id'      => 'admin',
                'id'           => 'role',
                'element'      => 'select',
                'label'        => 'Role',
                'description'  => $this->_('Choose a role for the account.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'multiOptions' => UsersService::getAdminRoles(),
            ),
            array(
                'form_id'     => array('basic', 'admin'),
                'id'          => 'email',
                'element'     => 'text',
                'label'       => $this->_('Email'),
                'description' => $this->_('Enter your email address.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Email',
                    $emailNoRecordExists,
                ),
            ),
            array(
                'form_id'     => array('basic', 'admin'),
                'id'          => 'password',
                'element'     => 'password',
                'label'       => $this->_('Password'),
                'description' => $this->_('Create a password for your account.'),
                'suffix'      => $this->_('(minimum 6 characters)'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    array('StringLength', array(6, null)),
                ),
            ),
            array(
                'form_id'     => array('basic', 'admin'),
                'id'          => 'password_confirm',
                'element'     => 'password',
                'label'       => $this->_('Confirm Password'),
                'description' => $this->_('Type your password again to confirm.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id' => array('basic'),
                'id'      => 'recaptcha',
                'element' => ($settings['enable_recaptcha'] && $settings['recaptcha_registration']) ? '\\Ppb\\Form\\Element\\ReCaptcha' : false,
                'label'   => 'Captcha Code',
            ),
            array(
                'form_id'      => 'basic',
                'id'           => 'newsletter_subscription',
                'element'      => 'checkbox',
                'multiOptions' => array(
                    1 => $translate->_('Subscribe To Newsletter'),
                ),
            ),
            array(
                'form_id'      => 'basic',
                'id'           => 'agree_terms',
                'element'      => ($settings['enable_registration_terms']) ? 'checkbox' : false,
                'label'        => $this->_('Terms and Conditions'),
                'multiOptions' => array(
                    1 => sprintf(
                        $translate->_('I am greater than 18 years old. <br/>I have read and agree to the site\'s '
                            . '<a href="%s" target="_blank">Terms and Conditions</a> and <a href="%s" target="_blank">Privacy Policy</a>.'),
                        $this->getView()->url($settings['registration_terms_link']),
                        $this->getView()->url($settings['registration_privacy_link'])),
                ),
                'validators'   => array(
                    $agreeTermsValidator,
                ),
            ),
            /**
             * --------------
             * STORE SETUP
             * --------------
             */
            array(
                'form_id'      => 'store_setup',
                'subtitle'     => $this->_('Store Subscription'),
                'id'           => 'store_subscription_id',
                'element'      => (!$storeActive || $this->getData('store_subscription_id')) ? 'radio' : 'hidden',
                'label'        => $this->_('Choose Subscription'),
                'description'  => $this->_('Choose a subscription for your store.'),
                'multiOptions' => $storesSubscriptions->getMultiOptions(),
                'required'     => (!$storeActive || $this->getData('store_subscription_id')) ? true : false,
                'attributes'   => ($storeActive ?
                    array('onchange' => 'javascript:storeSubscriptionChangeAlert();') : array()),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function storeSubscriptionChangeAlert() {
                            bootbox.alert('" . $translate->_('Warning: changing your store subscription type will disable your current active subscription!') . "')
                        }
                    </script > ",
            ),
            array(
                'form_id'     => 'store_setup',
                'subtitle'    => $this->_('Store Settings'),
                'id'          => 'store_name',
                'element'     => 'text',
                'label'       => $this->_('Store Name'),
                'description' => $this->_('Enter the name of your store.'),
                'required'    => true,
                'validators'  => array(
                    'NoHtml',
                    array('StringLength', array(null, 255)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'store_setup',
                'id'          => 'store_description',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Store Description'),
                'description' => $this->_('Enter a description for your store.'),
                'required'    => true,
                'attributes'  => array(
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'store_setup',
                'id'          => 'store_logo_path',
                'element'     => '\\Ppb\\Form\\Element\\MultiUpload',
                'label'       => $this->_('Store Logo'),
                'description' => $this->_('Upload a logo for your store.'),
                'required'    => true,
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Logo'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 2000000,
                        'uploadLimit'   => 1,
                    ),
                ),
            ),
            array(
                'form_id'     => 'store_setup',
                'id'          => 'store_category_id',
                'element'     => '\\Ppb\\Form\\Element\\Category',
                'label'       => $this->_('Store Category'),
                'description' => $this->_('Select a category where your store would be best included in.'),
                'attributes'  => array(
                    'data-no-refresh' => 'true'
                ),
                'required'    => true,
            ),
            array(
                'form_id'     => 'store_setup',
                'id'          => 'store_meta_description',
                'element'     => 'textarea',
                'label'       => $this->_('Store Meta Description'),
                'description' => $this->_('(Recommended) This meta description will tell search engine details about your store. '
                    . 'Your description should be no longer than 155 characters (including spaces).'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'rows'  => '4',
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'      => 'store_setup',
                'id'           => 'store_categories',
                'subtitle'     => $this->_('Custom Categories'),
                'element'      => '\\Ppb\\Form\\Element\\ChznSelect',
                'label'        => $this->_('Select Categories'),
                'description'  => $this->_('Choose which categories you want to use for your store, or leave empty to use the site\'s default categories.'),
                'multiOptions' => $this->getCategories()->getMultiOptions($categoriesSelect),
                'attributes'   => array(
                    'style'            => 'width: 350px;',
                    'data-placeholder' => $translate->_('Choose Categories...'),
                ),
                'multiple'     => true,
            ),
            /**
             * --------------
             * STORE PAGES
             * --------------
             */
            array(
                'form_id'     => 'store_pages',
                'subtitle'    => 'Store Pages',
                'id'          => 'store_about',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('About Page'),
                'description' => $this->_('(optional) Enter content for the store about page.'),
                'attributes'  => array(
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'store_pages',
                'id'          => 'store_shipping_information',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Shipping Information'),
                'description' => $this->_('(optional) Enter content for the store shipping information page.'),
                'attributes'  => array(
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'store_pages',
                'id'          => 'store_company_policies',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Company Policies'),
                'description' => $this->_('(optional) Enter content for the store company policies page.'),
                'attributes'  => array(
                    'class' => 'form-control',
                ),
            ),

            /**
             * --------------
             * GLOBAL SETTINGS
             * --------------
             */
            array(
                'form_id'      => 'global_settings',
                'id'           => 'enable_public_questions',
                'element'      => ($settings['enable_public_questions']) ? 'checkbox' : false,
                'label'        => $this->_('Accept Public Questions'),
                'description'  => $this->_('Check the above checkbox to allow site users to post public questions on your listings.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'enable_force_payment',
                'element'      => ($settings['enable_force_payment']) ? 'checkbox' : false,
                'label'        => $this->_('Enable Force Payment'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => sprintf(
                    $translate->_('If this option is enabled, sales will automatically cancelled unless marked as paid within %s minutes.'),
                    $settings['force_payment_limit']),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'quantity_description',
                'element'      => ($settings['enable_products']) ? 'radio' : false,
                'label'        => $this->_('Products Quantity Display'),
                'multiOptions' => array(
                    0 => $translate->_('Numbers'),
                    1 => $translate->_('Text (In Stock, Low Stock, Out of Stock)'),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('Select how to display the quantity field for products.'),
            ),
            array(
                'form_id'     => 'global_settings',
                'id'          => 'quantity_low_stock',
                'element'     => 'text',
                'label'       => $this->_('Low Stock Threshold'),
                'description' => $this->_('If the quantity of an item is lower than this value, the "low stock" message will be displayed.'),
                'required'    => ($this->getData('quantity_description') == 1) ? true : false,
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                    array('GreaterThan', array(1, true)),
                ),

            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'enable_tax',
                'element'      => ($settings['enable_tax_listings']) ? 'checkbox' : false,
                'label'        => $this->_('Enable Tax on Listings'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you wish to be able to apply tax for your listings.'),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'tax_type',
                'element'      => ($settings['enable_tax_listings']) ? 'select' : false,
                'label'        => $this->_('Tax Type'),
                'description'  => $this->_('Select the tax that will be applied for your listings.'),
                'multiOptions' => $this->getTaxTypes()->getMultiOptions(),
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
            ),


            array(
                'form_id'      => 'global_settings',
                'id'           => 'visitors_counter',
                'element'      => 'checkbox',
                'label'        => $this->_('Visitors Counter'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to display the "Item Viewed" box on the listings details pages'),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'listing_watched_by_box',
                'element'      => 'checkbox',
                'label'        => $this->_('Display "Listing watched by" Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to display the "Listing watched by" box on the listing details pages.'),
            ),
            array(
                'form_id'     => 'global_settings',
                'id'          => 'limit_bids_per_user',
                'element'     => ($settings['enable_auctions'] && $settings['enable_limit_bids']) ? 'text' : false,
                'label'       => $this->_('Limit Number of Bids / Offers per User'),
                'description' => $this->_('Enter a positive value if you want to limit the number of bids (without proxy bids) and offers a user can make on an auction, '
                    . 'or leave empty to disable this feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'show_make_offer_ranges',
                'element'      => ($settings['enable_make_offer'] && $settings['show_make_offer_ranges']) ? 'checkbox' : false,
                'label'        => $this->_('Show Offer Ranges'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('With this option enabled, the accepted offer ranges you set on listings that have "Make Offer" enabled will be displayed.'),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'automatic_digital_downloads',
                'element'      => ($settings['digital_downloads_max']) ? 'radio' : false,
                'label'        => $this->_('Automatic Download Links Activation'),
                'multiOptions' => array(
                    0  => $translate->_('Yes'),
                    -1 => $translate->_('No'),
                ),
                'description'  => $this->_('Select if you wish to automatically activate download links when sales are paid for using direct payment methods.<br>'
                    . 'Links can also be activated manually from the "My Sales" page.'),
            ),
            array(
                'form_id'      => 'global_settings',
                'id'           => 'vacation_mode',
                'element'      => 'checkbox',
                'label'        => $this->_('Vacation Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('If the checkbox above is checked, a message will appear on all your listings which will let visitors know that you are currently on vacation.'),
            ),
            array(
                'form_id'     => 'global_settings',
                'id'          => 'vacation_mode_return_date',
                'element'     => '\\Ppb\\Form\\Element\\Date',
                'label'       => $this->_('Return Date'),
                'description' => $this->_('(Optional) Enter a vacation return date.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'customData'  => array(
                    'formData' => array(
                        'dateFormat' => 'yy-mm-dd',
                        'minDate'    => 'new Date()'
                    ),
                ),
            ),
            /**
             * --------------
             * EMAIL NOTIFICATIONS
             * --------------
             */
            array(
                'form_id'      => 'email_notifications',
                'id'           => 'disable_emails',
                'element'      => 'radio',
                'label'        => $this->_('Email Notifications'),
                'description'  => $this->_('Choose whether to receive email notifications from the website.<br>'
                    . 'Important emails regarding account status and sale/purchase notifications would still be sent.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'multiOptions' => array(
                    0 => $translate->_('Enabled'),
                    1 => $translate->_('Disabled'),
                ),
            ),
            array(
                'form_id'      => 'email_notifications',
                'id'           => 'disable_seller_notifications',
                'element'      => ($isSeller) ? 'radio' : false,
                'label'        => $this->_('Seller Notifications'),
                'description'  => $this->_('These notifications include closed, relisted, no sale, '
                    . 'as well as approved and suspended listings notifications.'),
                'multiOptions' => array(
                    0 => $translate->_('Enabled'),
                    1 => $translate->_('Disabled'),
                ),
            ),
            array(
                'form_id'      => 'email_notifications',
                'id'           => 'disable_offers_notifications',
                'element'      => ($settings['enable_make_offer']) ? 'radio' : false,
                'label'        => $this->_('Offers Notifications'),
                'description'  => $this->_('These notifications include offers module related email notifications: '
                    . 'new offer, counter offer, offer accepted, offer declined, offer withdrawn.'),
                'multiOptions' => array(
                    0 => $translate->_('Enabled'),
                    1 => $translate->_('Disabled'),
                ),
            ),
            array(
                'form_id'      => 'email_notifications',
                'id'           => 'disable_messaging_notifications',
                'element'      => 'radio',
                'label'        => $this->_('Messaging Notifications'),
                'description'  => $this->_('These notifications include emails sent to notify users on new messages '
                    . 'received on the website.'),
                'multiOptions' => array(
                    0 => $translate->_('Enabled'),
                    1 => $translate->_('Disabled'),
                ),
            ),
            /**
             * --------------
             * FORGOT USERNAME / PASSWORD FORMS
             * --------------
             */
            array(
                'form_id'     => 'forgot-password',
                'id'          => 'username',
                'element'     => 'text',
                'label'       => $this->_('Username'),
                'description' => $this->_('Enter your username.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    $usernameAlpha,
                ),
            ),
            array(
                'form_id'     => array('forgot-username', 'forgot-password'),
                'id'          => 'email',
                'element'     => 'text',
                'label'       => $this->_('Email'),
                'description' => $this->_('Enter the email address you have used for registering your account.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Email',
                ),
            ),
        );

        // add custom registration fields
        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = 'advanced';
            $customFields[$key]['id'] = 'custom_field_' . $customField['id'];

            if (in_array($customField['element'], array('text', 'select', 'textarea'))) {
                $attributes = unserialize($customField['attributes']);
//                array_push($attributes['key'], 'class');
//                array_push($attributes['value'], 'form-control');
                $customFields[$key]['attributes'] = serialize($attributes);
            }
        }
        array_splice($array, 10, 0, $customFields);

        // add payment gateways related fields (direct payment)
        foreach ($gatewayFields as $key => $gatewayField) {
            $gatewayFields[$key]['form_id'] = 'payment-gateways';

            if (empty($gatewayField)) {
                unset($gatewayFields[$key]);
            }
        }
        array_splice($array, (19 + count($customFields)), 0, $gatewayFields);


        return $array;
    }

}


