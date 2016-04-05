<?php

/**
 *
 * PHP Pro Bid $Id$ pSDcq50uuxcH1jiF8nRW5lOYPgL86IdnLnS+xYpyiZQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * payment gateways and gateways settings tables service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table,
    Ppb\Service\PaymentGatewaysSettings,
    Cube\Db\Expr;

class PaymentGateways extends AbstractServiceTable
{

    /**
     *
     * gateways settings service
     *
     * @var \Ppb\Service\PaymentGatewaysSettings
     */
    protected $_paymentGatewaysSettings;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\PaymentGateways());

        $this->setPaymentGatewaysSettings();
    }

    /**
     *
     * get payment gateways settings service
     *
     * @return \Ppb\Service\PaymentGatewaysSettings
     */
    public function getPaymentGatewaysSettings()
    {
        return $this->_paymentGatewaysSettings;
    }

    /**
     *
     * set payment gateways settings service
     *
     * @param \Ppb\Service\PaymentGatewaysSettings $paymentGatewaysSettings
     *
     * @return \Ppb\Service\Table\PaymentGateways
     */
    public function setPaymentGatewaysSettings(PaymentGatewaysSettings $paymentGatewaysSettings = null)
    {
        if (!$paymentGatewaysSettings instanceof PaymentGatewaysSettings) {
            $paymentGatewaysSettings = new PaymentGatewaysSettings();
        }

        $this->_paymentGatewaysSettings = $paymentGatewaysSettings;

        return $this;
    }

    /**
     *
     * get gateways data from the 'payment_gateways' and 'payment_gateways_settings' tables
     *
     * @param int|bool $userId     user id (when fetching direct payment gateways)
     * @param int      $gatewayIds gateway ids (to fetch data for specific gateways)
     * @param bool     $activeOnly show only active gateways
     * @param bool     $singleRow  whether to display as single row result if only one row matches the query
     *
     * @return array
     */
    public function getData($userId = null, $gatewayIds = null, $activeOnly = false, $singleRow = false)
    {
        $select = $this->_table->select();

        if ($gatewayIds !== null) {
            $select->where('id IN (?)', new Expr(implode(', ', (array)$gatewayIds)));
        }


        if ($activeOnly) {
            if ($userId !== null || $userId === true) {
                $select->where('direct_payment = ?', 1);
            }
            else {
                $select->where('site_fees = ?', 1);
            }
        }

        $select->order('order_id ASC');

        $gateways = $this->fetchAll($select)->toArray();

        foreach ($gateways as $key => $gateway) {
            $select = $this->_paymentGatewaysSettings->getTable()->select()
                ->where('gateway_id = ?', $gateway['id']);

            if ($userId !== null) {
                $select->where('user_id = ?', $userId);
            }
            else {
                $select->where('user_id IS NULL');
            }

            $gatewayParams = $this->_paymentGatewaysSettings->fetchAll($select);

            foreach ($gatewayParams as $param) {
                $gateways[$key][$param['name']] = $param['value'];
            }
        }

        return (count($gateways) == 1 && $singleRow === true) ? $gateways[0] : $gateways;
    }

    /**
     *
     * get all active gateways
     * if user id is provided, get all enabled direct payment gateways
     * >> we will always return all available direct payment gateways, even if the seller hasnt set them up,
     *    so that he can set them up later
     *
     * @param int $userId
     *
     * @return array
     */
    public function getMultiOptions($userId = null)
    {
        $data = array();

        $translate = $this->getTranslate();

        $select = $this->_table->select()
            ->order(array('order_id ASC', 'name ASC'));

        if ($userId === null) {
            $select->where('site_fees = ?', 1);
        }
        else {
            $select->where('direct_payment = ?', 1);
        }

        $rows = $this->_table->fetchAll($select);

        foreach ($rows as $row) {
            $data[(string)$row['id']] = $translate->_($row['name']);
        }

        return $data;
    }

    /**
     *
     * save data in the tables (for the 'payment_gateways_settings' table, insert if key doesnt exist or update if it does)
     *
     * @param array $data
     * @param int   $userId
     *
     * @throws \InvalidArgumentException
     * @return \Ppb\Service\Table\PaymentGateways
     */
    public function save(array $data, $userId = null)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array('id', 'site_fees', 'direct_payment');

        $params = $data;

        foreach ($data as $key => $value) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
            else {
                unset($params[$key]);
            }
        }

        foreach ($params as $key => $param) {
            $gatewayId = key($param);
            $value = $param[$gatewayId];


            $select = $this->_paymentGatewaysSettings->getTable()->select()
                ->where('name = ?', $key)
                ->where('gateway_id = ?', $gatewayId);

            if ($userId !== null) {
                $select->where('user_id = ?', $userId);
            }
            else {
                $select->where('user_id IS NULL');
            }

            $row = $this->_paymentGatewaysSettings->getTable()->fetchRow($select);
            $input = array(
                'name'       => $key,
                'value'      => $value,
                'gateway_id' => $gatewayId,
            );

            if ($userId !== null) {
                $input['user_id'] = intval($userId);
            }

            if (count($row) > 0) {
                $input['id'] = $row['id'];
            }

            $this->_paymentGatewaysSettings->save($input);
        }

        parent::save($data);

        return $this;
    }

    public function getDirectPaymentFields()
    {
        $gateways = $this->getData(true, null, true);

        $gatewayFields = array();

        foreach ($gateways as $gateway) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                /* @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className();
                $elements = $gatewayModel->getElements();
                foreach ($elements as $element) {
                    $gatewayFields[] = array(
                        'gateway_id' => $gateway['id'],
                        'name'       => $element['id'],
                    );
                }
            }
        }

        return $gatewayFields;
    }

    public function getColumns()
    {

    }

    public function getElements()
    {

    }

}

