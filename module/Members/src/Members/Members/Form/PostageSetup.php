<?php

/**
 *
 * PHP Pro Bid $Id$ bXLg48LWGz3BmQePccK2SawUVWXWU1v8A74N4EUYmlU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * postage setup form
 */

namespace Members\Form;

use Ppb\Form,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Service;

class PostageSetup extends Form\AbstractBaseForm
{

    const BTN_SUBMIT = 'postage_setup';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save Settings',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);
        $settings = $this->getSettings();

        $freePostage = $this->createElement('checkbox', ShippingModel::SETUP_FREE_POSTAGE);
        $freePostage->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_FREE_POSTAGE])
            ->setSubtitle('Free Postage')
            ->setMultiOptions(
                array(1 => null))
            ->setAttributes(array(
                'class' => 'field-changeable',
            ))
            ->setBodyCode("
                    <script type=\"text/javascript\">
                    
                        function checkFormFields()
                        {
                            if ($('input:checkbox[name=\"free_postage\"]').is(':checked')) {
                                $('input:text[name=\"free_postage_amount\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"free_postage_amount\"]').val('').closest('.form-group').hide();
                            }
                            
                            if ($('input:radio[name=\"postage_type\"]:checked').val() == '" . ShippingModel::POSTAGE_TYPE_FLAT . "') { 
                                $('.shipping-carriers').prop('checked', false).closest('.form-group').hide();
                                $('input:radio[name=\"weight_uom\"]').closest('.form-group').hide();
                                
                                if ($('input:radio[name=\"shipping_locations\"]:checked').val() == '" . ShippingModel::POSTAGE_LOCATION_CUSTOM . "') {
                                    $('input:text[name=\"postage_flat_first\"]').closest('.form-group').hide();
                                    $('input:text[name=\"postage_flat_addl\"]').closest('.form-group').hide();
                                    $('.input-flat-rates').show();
                                }
                                else {
                                    $('input:text[name=\"postage_flat_first\"]').closest('.form-group').show();
                                    $('input:text[name=\"postage_flat_addl\"]').closest('.form-group').show();
                                    $('.input-flat-rates').hide();
                                }
                                
                            }
                            else if ($('input:radio[name=\"postage_type\"]:checked').val() == '" . ShippingModel::POSTAGE_TYPE_CARRIERS . "') { 
                                $('input:text[name=\"postage_flat_first\"]').val('').closest('.form-group').hide();
                                $('input:text[name=\"postage_flat_addl\"]').val('').closest('.form-group').hide();
                                $('.shipping-carriers').closest('.form-group').show();
                                $('input:radio[name=\"weight_uom\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"postage_flat_first\"]').val('').closest('.form-group').hide();
                                $('input:text[name=\"postage_flat_addl\"]').val('').closest('.form-group').hide();
                                $('.shipping-carriers').prop('checked', false).closest('.form-group').hide();
                                $('input:radio[name=\"weight_uom\"]').closest('.form-group').hide();
                            }
                            
                            if ($('input:radio[name=\"shipping_locations\"]:checked').val() == '" . ShippingModel::POSTAGE_LOCATION_CUSTOM . "') {
                                $('.location-groups').closest('.form-group').show();
                                
                                if ($('input:radio[name=\"postage_type\"]:checked').val() == '" . ShippingModel::POSTAGE_TYPE_FLAT . "') {
                                    $('.input-flat-rates').show();
                                }
                                else {
                                    $('.input-flat-rates').hide();
                                }
                            }
                            else {
                                $('.location-groups').closest('.form-group').hide();
                            }
                        }
    
                        $(document).ready(function() {
                            checkFormFields();
                        });
                        
                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });
                    </script>");
        $this->addElement($freePostage);

        $freePostageAmount = $this->createElement('text', ShippingModel::SETUP_FREE_POSTAGE_AMOUNT);
        $freePostageAmount->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_FREE_POSTAGE_AMOUNT])
            ->setPrefix($settings['currency'])
            ->setDescription('You can offer free postage on invoices for which the total amount exceeds the value you enter in the above field.')
            ->setAttributes(array(
                'class' => 'form-control input-mini',
            ))
            ->setValidators(array(
                'Numeric',
                array('GreaterThan', array(0, true))));
        $this->addElement($freePostageAmount);

        $shippingCarriersService = new Service\Table\ShippingCarriers();
        $shippingCarriersRowset = $shippingCarriersService->fetchAll('enabled = 1');

        $isShippingCarriers = count($shippingCarriersRowset);

        $postageTypeOptions = array(
            ShippingModel::POSTAGE_TYPE_ITEM => array(
                $translate->_('Item Based'),
                $translate->_('The postage costs will need to be entered for each individual listing.')
            ),
            ShippingModel::POSTAGE_TYPE_FLAT => array(
                $translate->_('Flat Rates'),
                $translate->_('With this option, you will enter an amount for the first item in an invoice, '
                    . 'and another for the remaining items in the invoice.<br>'
                    . 'The same values will apply to all the postage locations you have set up.'),
            ),
        );

        $availableShippingCarriers = array();
        if ($isShippingCarriers) {
            foreach ($shippingCarriersRowset as $row) {
                $availableShippingCarriers[$row['name']] = $translate->_($row['desc']);
            }

            $postageTypeOptions[ShippingModel::POSTAGE_TYPE_CARRIERS] = array(
                $translate->_('Shipping Carriers'),
                sprintf(
                    $translate->_('Your users will receive automatic quotes from the following shipping carriers:<br>%s'),
                    implode(', ', $availableShippingCarriers)));
        }

        $postageType = $this->createElement('radio', ShippingModel::SETUP_POSTAGE_TYPE);
        $postageType->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_POSTAGE_TYPE])
            ->setSubtitle('Postage Settings')
            ->setDescription('Choose the postage calculation type that will apply to all your sold items.')
            ->setMultiOptions($postageTypeOptions)
            ->setAttributes(array(
                'class' => 'field-changeable',
            ))
            ->setRequired();
        $this->addElement($postageType);

        $postageFlatFirst = $this->createElement('text', ShippingModel::SETUP_POSTAGE_FLAT_FIRST);
        $postageFlatFirst->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_POSTAGE_FLAT_FIRST])
            ->setPrefix($settings['currency'])
            ->setAttributes(array(
                'class' => 'form-control input-mini'));
        $this->addElement($postageFlatFirst);

        $postageFlatAddl = $this->createElement('text', ShippingModel::SETUP_POSTAGE_FLAT_ADDL);
        $postageFlatAddl->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_POSTAGE_FLAT_ADDL])
            ->setDescription('Enter the rates that will apply for the first and for the additional items in an invoice')
            ->setPrefix($settings['currency'])
            ->setAttributes(array(
                'class' => 'form-control input-mini'));
        $this->addElement($postageFlatAddl);

        if (count($availableShippingCarriers) > 0) {
            $shippingCarriers = $this->createElement('checkbox', ShippingModel::SETUP_SHIPPING_CARRIERS);
            $shippingCarriers->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_SHIPPING_CARRIERS])
                ->setDescription('Choose the shipping carriers that you want to use.')
                ->setMultiOptions($availableShippingCarriers)
                ->setAttributes(array(
                    'class' => 'shipping-carriers'
                ));
            $this->addElement($shippingCarriers);
        }

        $weightUom = $this->createElement('radio', ShippingModel::SETUP_WEIGHT_UOM);
        $weightUom->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_WEIGHT_UOM])
            ->setDescription('Select the weight unit of measurement that you will be using for your listings.')
            ->setMultiOptions(ShippingModel::$weightUom)
            ->setValue(key(ShippingModel::$weightUom))
            ->setRequired();
        $this->addElement($weightUom);

        $shippingLocations = $this->createElement('radio', ShippingModel::SETUP_SHIPPING_LOCATIONS);
        $shippingLocations->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_SHIPPING_LOCATIONS])
            ->setSubtitle('Shipping Locations Settings')
            ->setMultiOptions(array(
                ShippingModel::POSTAGE_LOCATION_DOMESTIC  => array(
                    $translate->_('Domestic'),
                    $translate->_('Choose this option if you will only ship in the country where your account is registered, and have the same rates for all locations.')
                ),
                ShippingModel::POSTAGE_LOCATION_WORLDWIDE => array(
                    $translate->_('Worldwide'),
                    $translate->_('Choose this option if you will ship worldwide, and have the same rates for all locations')
                ),
                ShippingModel::POSTAGE_LOCATION_CUSTOM    => array(
                    $translate->_('Custom'),
                    $translate->_('Choose this option in order to create custom shipping locations groups.')
                )
            ))
            ->setAttributes(array(
                'class' => 'field-changeable',
            ))
            ->setRequired();
        $this->addElement($shippingLocations);

        $locationsService = new Service\Table\Relational\Locations();
        $locationGroups = new Form\Element\FlatRatesLocationGroups(ShippingModel::SETUP_LOCATION_GROUPS);

        $locationGroups->setLabel(ShippingModel::$postageSetupFields[ShippingModel::SETUP_LOCATION_GROUPS])
            ->setDescription('Enter the location groups where you will ship your items to.<br>'
                . 'If item based shipping is used, each group will accept different shipping rates.')
            ->setAttributes(array(
                'class'       => 'location-groups form-control input-medium',
                'placeholder' => $translate->_('Enter Name'),
            ))
            ->setChznMultiOptions($locationsService->getMultiOptions())
            ->setMultiple();

        $this->addElement($locationGroups);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}