<?php

/**
 *
 * PHP Pro Bid $Id$ ecNN1frC0W/LXcXRGIh9yJtGdVQ3zdybXyem4OA0wE0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * FedEx Web Services integration
 */

namespace Ppb\Model\Shipping\Carrier;

use Ppb\Model\Shipping as ShippingModel;

class FedExWebServices extends AbstractCarrier
{
    /**
     * shipping carrier name
     */

    const NAME = 'FedExWebServices';
    const WSDL_PATH = '/../../../../External/FedEx/wsdl/RateService_v16.wsdl';

    /**
     * form elements (carrier module settings)
     */
    const ACCOUNT_NUMBER = 'account_number';
    const METER_NUMBER = 'meter_number';
    const API_KEY = 'fedexws_key';
    const PASSWORD = 'fedexws_pwd';

    /**
     * shipping carrier specific constants
     */
    const WEIGHT_UOM = 'LB';
    const DIMENSIONS_UOM = 'IN';
    const DROPOFF_TYPE = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...  
    const PACKAGING_TYPE = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...

    /**
     * currency
     */
    const CURRENCY = 'USD';

    /**
     *
     * FedEx WS carrier description
     *
     * @var string
     */
    protected $_description = 'FedExWebServices Description';

    /**
     *
     * package dimensions (in inches)
     *
     * @var array
     */
    protected $_dimensions = array(
        self::W => 10,
        self::H => 7,
        self::L => 25,
    );

    public function __construct()
    {
        parent::__construct(self::NAME, self::CURRENCY, ShippingModel::UOM_LBS);
    }

    /**
     *
     * get FedEx Web Services setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'form_id'    => self::NAME,
                'id'         => self::ACCOUNT_NUMBER,
                'element'    => 'text',
                'label'      => $this->_('Account Number'),
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => self::NAME,
                'id'         => self::METER_NUMBER,
                'element'    => 'text',
                'label'      => $this->_('Meter Number'),
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => self::NAME,
                'id'         => self::API_KEY,
                'element'    => 'text',
                'label'      => $this->_('API Key'),
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => self::NAME,
                'id'         => self::PASSWORD,
                'element'    => 'text',
                'label'      => $this->_('Password'),
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    /**
     *
     * get price method - gets the price of a selected method,
     * or outputs a list of available methods for the selected input data
     *
     * @param string $methodName (optional) method name
     *
     * @return bool|float|array      returns an array of methods, the price for the specified method or false if the price cannot be calculated
     *                                  or false if the price cannot be calculated
     *                                  if there is an error, the $_error variable will be set
     */
    public function getPrice($methodName = null)
    {
        $result = array();

        ini_set("soap.wsdl_cache_enabled", "0");

        $client = new \SoapClient(__DIR__ . self::WSDL_PATH, array('trace' => 1));

        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key'      => $this->_data[self::API_KEY],
                    'Password' => $this->_data[self::PASSWORD],
                ),
            ),
            'ClientDetail'            => array(
                'AccountNumber' => $this->_data[self::ACCOUNT_NUMBER],
                'MeterNumber'   => $this->_data[self::METER_NUMBER],
            ),
            'TransactionDetail'       => array(
                'CustomerTransactionId' => ' *** Rate Request using PHP ***'
            ),
            'Version'                 => array(
                'ServiceId'    => 'crs',
                'Major'        => '16',
                'Intermediate' => '0',
                'Minor'        => '0',
            ),
            'ReturnTransitAndCommit'  => true,
            'RequestedShipment'       => array(
                'DropoffType'               => self::DROPOFF_TYPE,
                'ShipTimestamp'             => date('c'),
                'ServiceType'               => $methodName, // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
                'PackagingType'             => self::PACKAGING_TYPE,
//                'RateRequestTypes'          => 'ACCOUNT',
//                'RateRequestTypes' => 'LIST',
                'Shipper'                   => array(
                    'Address' => array(
                        'PostalCode'  => $this->getSourceZip(),
                        'CountryCode' => strtoupper($this->getSourceCountry())
                    ),
                ),
                'Recipient'                 => array(
                    'Address' => array(
                        'PostalCode'  => $this->getDestZip(),
                        'CountryCode' => strtoupper($this->getDestCountry()),
                    ),
                ),
                'PackageCount'              => '1',
                'RequestedPackageLineItems' => array(
                    'SequenceNumber'    => 1,
                    'GroupPackageCount' => 1,
                    'Weight'            => array(
                        'Value' => $this->getWeight(),
                        'Units' => self::WEIGHT_UOM
                    ),
                    'Dimensions'        => array(
                        'Length' => $this->_dimensions[self::L],
                        'Width'  => $this->_dimensions[self::W],
                        'Height' => $this->_dimensions[self::H],
                        'Units'  => self::DIMENSIONS_UOM
                    ),
                ),

            ),
        );

        try {
            $response = $client->getRates($request);

            if (!in_array($response->HighestSeverity, array('FAILURE', 'ERROR', 'WARNING'))) {
                $rateReply = $response->RateReplyDetails;

                if ($methodName !== null) {
                    $this->setCurrency(
                        $rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency);

                    return doubleval($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount);
                }

                foreach ($rateReply as $rateOption) {
                    $ratedShipmentDetails = (is_array($rateOption->RatedShipmentDetails)) ?
                        $rateOption->RatedShipmentDetails[0] : $rateOption->RatedShipmentDetails;

                    $price = $ratedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount;

                    $result[] = array(
                        'code'     => $rateOption->ServiceType,
                        'name'     => ucwords(strtolower(str_replace('_', ' ', $rateOption->ServiceType))),
                        'price'    => $price,
                        'currency' => $ratedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency,
                    );
                }
            }
            else {
                $notifications = $response->Notifications;
                if (isset($notifications->Message)) {
                    $this->setError($notifications->Message);
                }
                else if (isset($notifications[0]->Message)) {
                    $this->setError($notifications[0]->Message);
                }
                else {
                    $this->setError('An unknown error has occurred.');
                }

                return false;
            }
        } catch (\SoapFault $exception) {
            $this->setError($exception->getMessage());

//            $this->setError('An unknown error has occurred.');

            return false;
        }

        return $result;
    }

}

