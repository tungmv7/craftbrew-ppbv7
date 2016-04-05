<?php

/**
 *
 * PHP Pro Bid $Id$ ggl4kT/w+JOC3wuhHOmi4duKuqc59HL8IVbLDHcruak=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * this model is used for creating the cart checkout page, specifically the address
 * and user registration sections
 */
namespace Ppb\Model\Elements\User;

use Ppb\Model\Elements\User,
        Ppb\Db\Table\Row\User as UserModel,
        Ppb\Service\UsersAddressBook;

class CartCheckout extends User
{
    /**
     * billing address fields prefix
     */
    const PRF_BLG = 'blg_';
    /**
     * shipping address fields prefix
     */
    const PRF_SHP = 'shp_';

    /**
     *
     * populated with the available addresses from the address book
     *
     * @var array
     */
    protected $_addressMultiOptions = array();

    /**
     *
     * set address multi options
     *
     * @return $this
     */
    public function setAddressMultiOptions()
    {
        if ($this->_user instanceof UserModel) {
            $usersAddressBook = new UsersAddressBook();
            $addressMultiOptions = $usersAddressBook->getMultiOptions($this->_user, '<br>', true);

            if (count($addressMultiOptions) > 0) {

                $addressMultiOptions[0] = array(
                    'title'      => $this->getTranslate()->_('New address'),
                    'locationId' => null,
                    'postCode'   => null,
                );
            }

            $this->_addressMultiOptions = $addressMultiOptions;
        }

        return $this;
    }

    /**
     *
     * set user
     *
     * @param \Ppb\Db\Table\Row\User $user
     * @return $this
     */
    public function setUser(UserModel $user = null)
    {
        parent::setUser($user);

        if ($this->_user instanceof UserModel) {
            $this->setAddressMultiOptions();
        }

        return $this;
    }

    /**
     *
     * get address multi options
     *
     * @return array
     */
    public function getAddressMultiOptions()
    {
        return $this->_addressMultiOptions;
    }

    public function getElements()
    {
        $allElements = parent::getElements();

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        // first we add the global and billing address fields, and merge with the address_id and alternate shipping checkbox fields
        $elements = array_merge(
            $this->getElementsWithFilter('form_id', array('global', 'address'), $allElements),
            array(
                array(
                    'form_id'      => 'address',
                    'id'           => 'address_id',
                    'element'      => (count($this->_addressMultiOptions) > 0) ? '\Ppb\Form\Element\SelectAddress' : 'hidden',
                    'label'        => $this->_('Select Address'),
                    'multiOptions' => $this->_addressMultiOptions,
                    'attributes'   => array(
                        'onchange' => 'this.form.submit();',
                    ),
                    'bodyCode'     => '
                    <script type="text/javascript">
                        AddressSelect();

                        $(document).on(\'change\', \'[name="' . self::PRF_BLG . 'address_id"]\', function() {
                            AddressSelect();
                        });

                        function AddressSelect() {
                            var addressId = parseInt($(\'input:radio[name="' . self::PRF_BLG . 'address_id"]:checked\').val());

                            if (addressId > 0) {
                                $(\'[name^="' . self::PRF_BLG . '"]\').closest(\'.form-group\').hide();
                            }
                            else {
                                $(\'[name^="' . self::PRF_BLG . '"]\').closest(\'.form-group\').show();
                            }
                            $(\'[name="' . self::PRF_BLG . 'address_id"]\').closest(\'.form-group\').show();
                        }
                    </script>',
                ),
                array(
                    'form_id'  => 'sale_id',
                    'id'       => 'sale_id',
                    'element'  => 'hidden',
                    'bodyCode' => '<script type="text/javascript">
                        CalculateOrderPostage();

                        $(document).on(\'change\', \'[name="alt_ship"], [name$="address_id"], [name$="zip_code"], [name$="country"]\', function() {
                            $(\'#shipping-options\').html("' . $translate->_('Please wait ...') . '");

                            setTimeout(function() {
                                CalculateOrderPostage();
                            }, 500);
                        });

                        function CalculateOrderPostage() {
                            var postCode = "";
                            var locationId = "";

                            if ($(\'input:checkbox[name="alt_ship"]\').is(\':checked\')) {
                                var selectedAddress = $(\'input:radio[name="' . self::PRF_SHP . 'address_id"]:checked\');
                                var addressId = parseInt(selectedAddress.val());

                                if (addressId > 0) {
                                    postCode = selectedAddress.attr(\'data-post-code\');
                                    locationId = selectedAddress.attr(\'data-location-id\');
                                }
                                else {
                                    postCode = $(\'[name="' . self::PRF_SHP . 'zip_code"]\').val();
                                    locationId = $(\'[name="' . self::PRF_SHP . 'country"]\').val();
                                }
                            }
                            else {
                                var selectedAddress = $(\'input:radio[name="' . self::PRF_BLG . 'address_id"]:checked\');
                                var addressId = parseInt(selectedAddress.val());

                                if (addressId > 0) {
                                    postCode = selectedAddress.attr(\'data-post-code\');
                                    locationId = selectedAddress.attr(\'data-location-id\');
                                }
                                else {
                                    postCode = $(\'[name="' . self::PRF_BLG . 'zip_code"]\').val();
                                    locationId = $(\'[name="' . self::PRF_BLG . 'country"]\').val();
                                }
                            }

                            if (postCode && locationId) {
                                $(\'#shipping-options\').calculatePostage({
                                    selector: \'.form-checkout\',
                                    postUrl: paths.calculatePostage,
                                    locationId: locationId,
                                    postCode: postCode,
                                    postageId: $(\'[name="postage_id"]\').val(),
                                    enableSelection: 1
                                });
                            }
                            else {
                                $(\'#shipping-options\').html("' . $translate->_('Please enter your shipping address.') . '");
                            }
                        }

                        $(document).on(\'change\', \'[name="blg_state"]\', function() {
                            $(this).closest(\'form\').submit();
                        });
                    </script > '
                ),
                array(
                    'form_id'      => 'shipping_checkbox',
                    'id'           => 'alt_ship',
                    'element'      => ($settings['enable_shipping']) ? 'checkbox' : false,
                    'multiOptions' => array(
                        1 => $translate->_('Ship to a different address'),
                    ),
                    'attributes'   => array(
                        'onchange' => 'javascript:displayShippingAddress()',
                    ),
                    'bodyCode'     => '<script type="text/javascript">
                        displayShippingAddress();

                        function displayShippingAddress() {
                            if ($(\'input:checkbox[name="alt_ship"]\').is(\':checked\')) {
                                $("[name^=\'' . self::PRF_SHP . '\']").closest(\'.form-group\').show();
                                $("#shipping-address-subtitle").show();
                            }
                            else {
                                $("[name^=\'' . self::PRF_SHP . '\']").closest(\'.form-group\').hide();
                                $("#shipping-address-subtitle").hide();
                            }
                        }
                    </script>'

                ),
            )
        );

        $object = $this;
        array_walk($elements, function (&$element) use (&$object) {
            $element = $object->prepareElementData($element, CartCheckout::PRF_BLG);
        });

        // if the user is not registered, add basic registration fields
        if (!$this->_user) {
            $elements = array_merge($elements, $this->getElementsWithFilter('form_id', array('basic'), $allElements));
        }

        if ($settings['enable_shipping']) {
            // now add shipping address fields
            $shippingAddressElements = $this->getElementsWithFilter('form_id', array('address'), $allElements);
            array_walk($shippingAddressElements, function (&$element) use (&$object) {
                $element = $object->prepareElementData($element, CartCheckout::PRF_SHP);
            });

            $elements = array_merge($elements, $shippingAddressElements);
        }

        return $elements;
    }

    /**
     *
     * processes an element array item
     *
     * @param array  $element
     * @param string $prefix
     * @return array
     */
    public function prepareElementData($element, $prefix)
    {
        $data = $this->getData();

        // check if we are using an existing address
        $existingAddress = $this->getData($prefix . 'address_id');

        // alter body code column in all rows
        if (!empty($element['bodyCode'])) {
            $element['bodyCode'] = str_replace(
                array(
                    "[name=\"country\"]",
                    "[name=\"state\"]",
                    "name: 'state'",
                    'ChangeState()',
                    'AddressSelect()',
                ),
                array(
                    "[name=\"" . $prefix . "country\"]",
                    "[name=\"" . $prefix . "state\"]",
                    "name: '" . $prefix . "state'",
                    $prefix . 'ChangeState()',
                    $prefix . 'AddressSelect()',
                ), $element['bodyCode']);
        }

        // generate state field to work correctly
        if ($element['id'] == 'state' && !empty($data[$prefix . "country"])) {
            $states = $this->getLocations()->getMultiOptions(
                $data[$prefix . "country"]);
            if (count($states) > 0) {
                $element['multiOptions'] = $states;
            }
            else {
                unset($element['multiOptions']);
                $element['element'] = 'text';
            }
        }

        if ($prefix == self::PRF_SHP && !$this->getData('alt_ship')) {
            $element['required'] = false;
            $element['validators'] = array();
        }

        // if we select an existing address, the address form fields will not be required and will be hidden using javascript
        if (in_array($element['form_id'], array('address'))) {
            if ($existingAddress && $element['id'] != 'address_id') {
                $element['required'] = false;
                $element['validators'] = array();
                if (!empty($element['attributes']['class'])) {
                    $element['attributes']['class'] .= ' address-field';
                }
                else {
                    $element['attributes']['class'] = 'address-field';
                }
            }

            $element['id'] = $prefix . $element['id'];
        }

        return $element;
    }
}