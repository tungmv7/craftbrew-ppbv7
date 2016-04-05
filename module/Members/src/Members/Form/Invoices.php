<?php

/**
 *
 * PHP Pro Bid $Id$ dHgBr6HaUraIdeR0lY6/SA/XjjV4iBarrlf8lijKq0I=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * edit/combine invoices form
 *
 */

namespace Members\Form;

use App\Form\Tables as TablesForm,
    Ppb\Service\Table\SalesListings,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Service\UsersAddressBook,
    Ppb\Db\Table\Row\Sale;

class Invoices extends TablesForm
{

    /**
     *
     * buyer user model
     * needed to create the postage method drop down
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_buyer;

    /**
     *
     * form constructor
     *
     * @param \Ppb\Service\Table\SalesListings $serviceTable
     * @param \Ppb\Db\Table\Row\Sale           $sale                sale model: for the sale seller or admin , the price, quantity, postage amount and insurance amount can be edited
     *                                                              *the buyer can only choose the shipping method and check a checkbox whether to apply insurance or not
     * @param string                           $option              edit or combine (default is edit)
     * @param string                           $action              form action
     */
    public function __construct(SalesListings $serviceTable, Sale $sale, $option = null, $action = null)
    {
        parent::__construct($serviceTable, $action);

        $settings = $this->getSettings();

        $elements = $this->getElements();

        $isSeller = $sale->isSeller();

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $sale->findParentRow('\Ppb\Db\Table\Users', 'Buyer');
        $buyer->setAddress($sale['shipping_address_id']);

        $this->setBuyer($buyer);

        /** @var \Cube\Form\Element $element */
        foreach ($elements as $element) {
            $elementName = $element->getName();

            if ($elementName == 'quantity') {
                $data = $element->getValue();
                $this->removeElement($elementName);

                $quantity = $this->createElement('\Ppb\Form\Element\DescriptionHidden', 'quantity');
                $quantity->setMultiple()
                    ->setValue($data);

                $this->addElement($quantity);
            }

            if ($isSeller === false) {
                if ($elementName == 'price') {
                    $data = $element->getValue();
                    $this->removeElement($elementName);

                    $price = $this->createElement('\Ppb\Form\Element\PriceDescription', 'price');
                    $price->setMultiple()
                        ->setValue($data);

                    $this->addElement($price);
                }
            }
        }

        if ($sale->getData('apply_tax')) {
            $taxRate = $this->createElement(($isSeller) ? 'text' : 'hidden',
                'tax_rate')
                ->setLabel('Tax Rate')
                ->setSuffix('%')
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ));
            $this->addElement($taxRate);
        }

        if ($settings['enable_shipping']) {
            $usersAddressBook = new UsersAddressBook();
            $multiOptions = $usersAddressBook->getMultiOptions($buyer);

            $shippingAddress = $this->createElement('select', 'shipping_address_id');
            $shippingAddress->setLabel('Shipping Address')
                ->setAttributes(array(
                    'class'    => 'form-control',
                    'onchange' => 'this.form.submit();'
                ))
                ->setMultiOptions($multiOptions)
                ->setRequired();

            $this->addElement($shippingAddress);

            $postageMethod = $this->createElement('\Ppb\Form\Element\PostageMethod', 'postage_id');
            $postageMethod->setLabel('Postage Method')
                ->setAttributes(array(
                    'class' => 'form-control field-changeable',
                ))
                ->setBodyCode("
                    <script type=\"text/javascript\">
                        function checkEditInvoiceFormFields()
                        {
                            var price = $('[name=\"postage_id\"]').find(':selected').attr('data-price');
                            var postageAmount = $('[name=\"postage_amount\"]').attr('data-price');

                            var postageId = $('[name=\"postage_id\"]').find(':selected').val();

                            if (postageAmount > 0) {
                                $('[name=\"postage_amount\"]').val(postageAmount);
                            }
                            else {
                                $('[name=\"postage_amount\"]').val(price);
                            }

                            if (postageId == -1) {
                                $('.field-insurance').closest('tr').hide();
                                $('[name=\"apply_insurance\"]').prop('checked', false);
                            }
                            else {
                                $('.field-insurance').closest('tr').show();
                            }

                        }

                        $(document).ready(function() {
                            checkEditInvoiceFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkEditInvoiceFormFields();
                        });
                    </script>")
                ->setRequired();
            $this->addElement($postageMethod);

            $postageAmount = $this->createElement(($isSeller) ? 'text' : '\Ppb\Form\Element\Description',
                'postage_amount')
                ->setLabel('Postage Amount')
                ->setPrefix($sale['currency'])
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ));
            $this->addElement($postageAmount);

            $insuranceCheckbox = $this->createElement('checkbox', 'apply_insurance');
            $insuranceCheckbox->setLabel('Apply Insurance')
                ->setAttributes(array(
                    'class' => 'field-insurance',
                ))
                ->setMultiOptions(
                    array(1 => null));
            $this->addElement($insuranceCheckbox);

            $insuranceAmount = $this->createElement(($isSeller) ? 'text' : '\Ppb\Form\Element\PriceDescription',
                'insurance_amount')
                ->setLabel('Insurance Amount')
                ->setPrefix($sale['currency'])
                ->setAttributes(array(
                    'class' => 'field-insurance form-control input-mini',
                ));
            $this->addElement($insuranceAmount);
        }

        $this->addSubmitElement('Update Values', 'update_values');

        $this->setPartial('forms/invoices.phtml');
    }

    /**
     *
     * set buyer
     *
     * @param \Ppb\Db\Table\Row\User $buyer
     *
     * @return $this
     */
    public function setBuyer(UserModel $buyer)
    {
        $this->_buyer = $buyer;

        return $this;
    }

    /**
     *
     * get buyer
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getBuyer()
    {
        return $this->_buyer;
    }

    /**
     *
     * set form data and initialize postage method drop down values based on the items in the invoice
     *
     * @param array $data data to be inserted in the form fields
     * @param bool  $flip flip array values
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function setData(array $data = null, $flip = false)
    {
        if (!empty($data['shipping_address_id'])) {
            $this->_buyer->setAddress($data['shipping_address_id']);
        }

        parent::setData($data, $flip);

        if ($this->hasElement('postage_id')) {
            /** @var \Ppb\Form\Element\PostageMethod $postageMethod */
            $postageMethod = $this->getElement('postage_id');

            $input = array();

            foreach ($this->_data as $data) {
                if (is_array($data) &&
                    array_key_exists('listing_id', $data) &&
                    array_key_exists('quantity', $data)
                ) {
                    if (!empty($input[$data['listing_id']])) {
                        $input[$data['listing_id']] += $data['quantity'];
                    }
                    else {
                        $input[$data['listing_id']] = $data['quantity'];
                    }
                }
            }

            if (!empty($input)) {

                $postageMethod->setShippingModel($input, $this->_buyer['country'],
                    $this->_buyer['zip_code']);
            }
        }

        return $this;
    }

    /**
     *
     * method used as a workaround for the correct post postage amount to be retrieved when clicking update values.
     * the price will then be retrieved by the jquery component
     *
     * @param string $value
     *
     * @return $this
     */
    public function preparePostageAmountField($value)
    {
        if ($this->hasElement('postage_amount') && !empty($value)) {
            $postageAmount = $this->getElement('postage_amount');
            $postageAmount->addAttribute('data-price', $value);
        }

        return $this;
    }

}
