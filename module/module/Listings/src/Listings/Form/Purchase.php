<?php

/**
 *
 * PHP Pro Bid $Id$ YUN5gA3I4YBHPdQU1wRozcxHjkkBiNVz3bIhfzWk9+M=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing bid / buy / make offer form
 */

namespace Listings\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Cube\Controller\Front,
    Ppb\Service\UsersAddressBook,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Listing as ListingModel;

class Purchase extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit_purchase';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Proceed',
    );

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\Listing $listing listing model
     * @param \Ppb\Db\Table\Row\User    $buyer   buyer user model
     * @param string                    $type    the type of form to be created (bid|buy|offer)
     * @param string                    $action  the form's action
     */
    public function __construct(ListingModel $listing, UserModel $buyer = null, $type = 'bid', $action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();
        $request = Front::getInstance()->getRequest();

        $title = null;
        $amountLabel = null;

        switch ($type) {
            case 'bid':
                $title = $translate->_('Confirm Bid');
                $amountLabel = $translate->_('Bid Amount');
                break;
            case 'buy':
                $title = $translate->_('Confirm Purchase');
                break;
            case 'offer':
                $title = $translate->_('Confirm Offer');
                $amountLabel = $translate->_('Offer Amount');
                break;
        }
        $this->setTitle($title);

        if ($listing->getData('listing_type') == 'product') {
            $customFields = $listing->getCustomFields();

            $addBodyCode = false;

            if (count($customFields) > 0) {
                foreach ($customFields as $customField) {
                    if ($customField['product_attribute']) {
                        $multiOptions = array();
                        if (!empty($customField['multiOptions'])) {
                            $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                            $multiOptions = array_filter(array_combine($multiOptions['key'], $multiOptions['value']));
                            $multiOptions = @array_intersect_key($multiOptions, array_flip($customField['value']));
                        }

                        if (count($multiOptions) > 0) {
                            $productAttributeElement = $this->createElement('select', 'product_attributes[' . $customField['id'] . ']');
                            $productAttributeElement->setLabel($customField['label'])
                                ->setAttributes(array(
                                    'class' => 'form-control input-default',
                                ))
                                ->setHideDefault()
                                ->setRequired()
                                ->setMultiOptions($multiOptions);

                            if (!empty($listing['stock_levels']) && !$addBodyCode) {
                                $addBodyCode = true;
                                $stockLevels = \Ppb\Utility::unserialize($listing['stock_levels']);

                                $view = $this->getView();
                                array_walk($stockLevels, function (&$array) use (&$listing, &$view) {
                                    $price = $listing['buyout_price'] + $array['price'];
                                    // @version 7.6: workaround to properly display product attributes on listing preview
                                    $array['options'] = \Ppb\Utility::unserialize($array['options']);
                                    $array['quantity'] = $listing->getAvailableQuantity(null, $array['options']);
                                    $array['priceDisplay'] = $view->amount($price, $listing['currency']);
                                });

                                $stockLevels = array_filter($stockLevels, function (&$array) {
                                    return ($array['quantity'] > 0);
                                });

                                /** @var \Ppb\Db\Table\Row\User $seller */
                                $seller = $listing->findParentRow('\Ppb\Db\Table\Users');

                                $lowStockThreshold = (($lowStockThreshold = $seller->getGlobalSettings('quantity_low_stock')) > 0) ?
                                    $lowStockThreshold : 1;

                                $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();
                                $productAttributeElement->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/phpjs/array_intersect_assoc.js"></script>')
                                    ->setBodyCode("<script type=\"text/javascript\">
                                        var array = $.parseJSON('" . json_encode($stockLevels) . "');

                                        function setDisabledOptions(element, selected) {
                                            var value = element.val();
                                            var name = element.prop('name');
                                            var id = parseInt($(element).prop('name').replace('product_attributes[', '').replace(']', ''));

                                            var search = selected;

                                            var newSelection = false;

                                            $('option', element).each(function () {
                                                search[id] = $(this).val();

                                                var exists = arrayCheck(search);

                                                if (!exists) {
                                                    if ($(this).prop('selected')) {
                                                        newSelection = true;
                                                    }
                                                    $(this).prop('disabled', true).removeProp('selected');
                                                }
                                                else {
                                                    $(this).prop('disabled', false);
                                                }
                                            });


                                            if (newSelection) {
                                                var enabledOption = $('option:not([disabled])', element).first();
                                                enabledOption.prop('selected', true);
                                                value = enabledOption.val();
                                            }

                                            selected[id] = value;

                                            var nextElement = element.closest('.form-group').next().find('select[name^=\"product_attributes\"]');

                                            if (nextElement.length > 0) {
                                                setDisabledOptions(nextElement, selected);
                                            }
                                        }

                                        function arrayCheck(search) {
                                            var exists = false;

                                            for (var i in array) {
                                                var src = array_intersect_assoc(search, array[i].options);

                                                if (JSON.stringify(src) == JSON.stringify(search)) {
                                                    exists = true;
                                                }
                                            }

                                            return exists;
                                        }

                                        function updateQuantityPriceDisplay(elements) {
                                            var msg = {
                                                inStock: '" . $translate->_('In Stock') . "',
                                                lowStock: '" . $translate->_('Low Stock') . "',
                                                outOfStock: '" . $translate->_('Out of Stock') . "'
                                            };

                                            var quantityDescription = " . intval($seller->getGlobalSettings('quantity_description')) . ";
                                            var lowStockThreshold = " . $lowStockThreshold . ";

                                            selected = {};
                                            $(elements).each(function() {
                                                var id = parseInt($(this).prop('name').replace('product_attributes[', '').replace(']', ''));
                                                selected[id] = $(this).val();
                                            });

                                            for (var i in array) {
                                                if (JSON.stringify(array[i].options) == JSON.stringify(selected)) {
                                                    var availableQuantity = array[i].quantity;
                                                    var priceDisplay = array[i].priceDisplay;

                                                    if (quantityDescription) {
                                                        if ((availableQuantity > lowStockThreshold) || (availableQuantity = -1)) {
                                                            availableQuantity = msg.inStock;
                                                        }
                                                        else if (availableQuantity > 0) {
                                                            availableQuantity = msg.lowStock;
                                                        }
                                                        else {
                                                            availableQuantity = msg.outOfStock;
                                                        }
                                                    }

                                                    $('#product-price').text(priceDisplay);
                                                    $('#quantity-available').text(availableQuantity);
                                                }
                                            }
                                        }


                                        $(document).ready(function () {
                                            var elements = $('[name^=\"product_attributes\"]');
                                            var selected = {};

                                            var element = elements.first();
                                            setDisabledOptions(element, selected);
                                            updateQuantityPriceDisplay(elements);

                                            elements.on('change', function () {
                                                var selected = {};

                                                setDisabledOptions($(this), selected);
                                                updateQuantityPriceDisplay(elements);
                                            });
                                        });
                                    </script>");
                            }

                            $this->addElement($productAttributeElement);
                        }

                    }
                }
            }
        }

        if (in_array($type, array('bid', 'offer'))) {
            $amount = $this->createElement('text', 'amount');
            $amount->setLabel($amountLabel)
                ->setAttributes(array(
                    'placeholder' => $listing['currency'],
                    'class'       => 'form-control input-mini',
                ))
                ->addValidator(new Validate\Numeric())
                ->setRequired();


            if ($type == 'bid') {
                $amount->addValidator(
                    new Validate\GreaterThan(array($listing->minimumBid(), true, true)));
            }
            else if ($type == 'offer') {
                $amount->setSuffix('/ item');
                $amount->addValidator(
                    new Validate\GreaterThan(array($listing['make_offer_min'], true, true)));
                if ($listing['make_offer_max'] > 0) {
                    $amount->addValidator(
                        new Validate\LessThan(array($listing['make_offer_max'], true)));
                }
            }

            $this->addElement($amount);
        }


        if (in_array($type, array('buy'))) {
            $settings = $this->getSettings();

            if ($settings['enable_shipping'] && $buyer instanceof UserModel) {
                $usersAddressBook = new UsersAddressBook();
                $multiOptions = $usersAddressBook->getMultiOptions($buyer);

                $shippingAddress = $this->createElement('select', 'shipping_address_id');
                $shippingAddress->setLabel('Select Shipping Address')
                    ->setAttributes(array(
                        'class'    => 'form-control',
                        'onchange' => 'this.form.submit();'
                    ))
                    ->setMultiOptions($multiOptions)
                    ->setHideDefault()
                    ->setRequired();

                $this->addElement($shippingAddress);

                $quantity = $request->getParam('quantity');

                /** @var \Ppb\Form\Element\PostageMethod $postageMethod */
                $postageMethod = $this->createElement('\Ppb\Form\Element\PostageMethod', 'postage_id');
                $postageMethod->setLabel('Select Postage Method')
                    ->setAttributes(array(
                        'class' => 'form-control input-medium'
                    ))
                    ->setShippingModel(array($listing['id'] => $quantity), $buyer['country'], $buyer['zip_code'])
                    ->setRequired();

                $this->addElement($postageMethod);

                if ($listing[\Ppb\Model\Shipping::FLD_INSURANCE] > 0) {
                    $insuranceCheckbox = $this->createElement('checkbox', 'apply_insurance');
                    $insuranceCheckbox->setLabel('Apply Insurance')
                        ->setMultiOptions(
                            array(1 => null));

                    $this->addElement($insuranceCheckbox);
                }
            }

            $voucherCode = $this->createElement('hidden', 'voucher_code');
            $this->addElement($voucherCode);
        }

        // @version 7.6: the quantity field will appear for products only, but on both the make offer and buy out forms
        if ($listing['listing_type'] == 'product') {
            $availableQuantity = $listing->getAvailableQuantity(null, $request->getParam('product_attributes'));

            $quantity = $this->createElement('text', 'quantity');
            $quantity->setLabel('Enter Quantity')
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ))
                ->setRequired()
                ->addValidator(
                    new Validate\LessThan(array($availableQuantity, true)))
                ->addValidator(
                    new Validate\GreaterThan(array(1, true, true)));
            $this->addElement($quantity);
        }

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/purchase.phtml');
    }


    /**
     *
     * set form data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        parent::setData($data);

        if (!empty($data['product_attributes'])) {
            /** @var \Cube\Form\Element $element */
            foreach ($this->_elements as $element) {
                $elementName = $element->getName();
                if (0 === strpos($elementName, 'product_attributes')) {
                    $id = preg_replace("/[^0-9]/", "", $elementName);
                    if (!empty($data['product_attributes'][$id])) {
                        $element->setData($data['product_attributes'][$id]);
                    }
                }
            }
        }

        return $this;
    }
}