<?php

/**
 *
 * PHP Pro Bid $Id$ tiqz+t1Y5w17PMmlYmi0nnTUK+6/47b57GUmmLndMw8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * shipping carriers and carrier settings tables service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table,
        Cube\Db\Expr;

class ShippingCarriers extends AbstractServiceTable
{

    /**
     *
     * gateways settings table
     *
     * @var \Ppb\Db\Table\ShippingCarriersSettings
     */
    protected $_shippingCarriersSettings;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\ShippingCarriers());
        $this->setShippingCarriersSettings();
    }

    /**
     *
     * set the table that will be used by the service
     *
     * @param \Ppb\Db\Table\ShippingCarriersSettings $shippingCarriersSettings
     * @return \Ppb\Service\Table\ShippingCarriers
     */
    public function setShippingCarriersSettings(Table\ShippingCarriersSettings $shippingCarriersSettings = null)
    {
        if (!$shippingCarriersSettings instanceof Table\ShippingCarriersSettings) {
            $shippingCarriersSettings = new Table\ShippingCarriersSettings();
        }

        $this->_shippingCarriersSettings = $shippingCarriersSettings;

        return $this;
    }

    /**
     *
     * get gateways data from the 'shipping_carriers' and 'shipping_carriers_settings' tables
     *
     * @param int     $carrierIds carrier ids (to fetch data for specific carriers)
     * @param bool $activeOnly show only active carriers
     * @return array
     */
    public function getData($carrierIds = null, $activeOnly = false)
    {
        $select = $this->_table->select();

        if ($carrierIds !== null) {
            $select->where('id IN (?)', new Expr(implode(', ', (array)$carrierIds)));
        }


        if ($activeOnly) {
            $select->where('enabled = ?', 1);
        }

        $carriers = $this->fetchAll($select)->toArray();

        foreach ($carriers as $key => $carrier) {
            $select = $this->_shippingCarriersSettings->select()
                    ->where('carrier_id = ?', $carrier['id']);

            $carrierParams = $this->fetchAll($select);

            foreach ($carrierParams as $param) {
                $carriers[$key][$param['name']] = $param['value'];
            }
        }

        return (count($carriers) == 1) ? $carriers[0] : $carriers;
    }

    /**
     *
     * get all active carriers
     *
     * @return array
     */
    public function getMultiOptions()
    {
        $data = array();

        $translate = $this->getTranslate();

        $select = $this->_table->select()
                ->where('enabled = ?', 1);

        $rows = $this->_table->fetchAll($select);

        foreach ($rows as $row) {
            $data[(string)$row['id']] = $translate->_($row['name']);
        }

        return $data;
    }

    /**
     *
     * save data in the tables (for the 'shipping_carriers_settings' table, insert if key doesnt exist or update if it does)
     *
     * @param array $data
     * @return \Ppb\Service\Table\ShippingCarriers
     * @throws \InvalidArgumentException
     */
    public function save(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array('id', 'enabled');

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
            $carrierId = key($param);
            $value = $param[$carrierId];

            $select = $this->_shippingCarriersSettings->select()
                    ->where('name = ?', $key)
                    ->where('carrier_id = ?', $carrierId);

            $row = $this->_shippingCarriersSettings->fetchRow($select);

            if (count($row) > 0) {
                $this->_shippingCarriersSettings->update(
                    array('value' => $value), "id = '{$row['id']}'");
            }
            else {
                $input = array(
                    'name'       => $key,
                    'value'      => $value,
                    'carrier_id' => $carrierId,
                );

                $this->_shippingCarriersSettings->insert($input);
            }
        }

        parent::save($data);

        return $this;
    }

    public function getColumns()
    {

    }

    public function getElements()
    {

    }

}

