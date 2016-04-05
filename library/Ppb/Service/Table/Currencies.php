<?php

/**
 *
 * PHP Pro Bid $Id$ /qp7MZGEyxgIGXe0MBH81AczIHbibzsKu1ZGXlhYZKY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * currencies table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\Currencies as CurrenciesTable;

class Currencies extends AbstractServiceTable
{

    /**
     *
     * default currency
     *
     * @var string
     */
    protected $_defaultCurrency;

    public function __construct()
    {
        parent::__construct();

        $settings = $this->getSettings();

        if (isset($settings['currency'])) {
            $this->setDefaultCurrency(
                $settings['currency']);
        }

        $this->setTable(
            new CurrenciesTable());
    }

    /**
     *
     * set the default currency variable
     *
     * @param string $currency
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function setDefaultCurrency($currency)
    {
        $this->_defaultCurrency = $currency;

        return $this;
    }

    /**
     *
     * get all currencies
     * to be used for the currency selector
     *
     * @param string $column
     *
     * @return array
     */
    public function getMultiOptions($column = null)
    {
        $data = array();

        $translate = $this->getTranslate();

        $rows = $this->_table->fetchAll();

        foreach ($rows as $row) {
            if ($column === null) {
                $column = 'description';
            }
            $data[(string)$row['iso_code']] = $translate->_($row[$column]);
        }

        return $data;
    }

    /**
     *
     * get all table columns needed to generate the currencies management
     * table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('ISO Code'),
                'class'      => 'size-mini',
                'element_id' => 'iso_code',
            ),
            array(
                'label'      => $this->_('Symbol'),
                'class'      => 'size-mini',
                'element_id' => 'symbol',
            ),
            array(
                'label'      => $this->_('Description'),
                'element_id' => 'description',
            ),
            array(
                'label'      => $this->_('Conversion Rate'),
                'class'      => 'size-medium',
                'element_id' => 'conversion_rate',
            ),
            array(
                'label'      => $this->_('Delete'),
                'class'      => 'size-mini',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     *
     * get all form elements that are needed to generate the currencies
     * management table in the admin area
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id'      => 'id',
                'element' => 'hidden',
            ),
            array(
                'id'         => 'iso_code',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'symbol',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'description',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'id'         => 'conversion_rate',
                'element'    => 'text',
                'prefix'     => '1 ' . $this->_defaultCurrency . ' = ',
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'id'      => 'delete',
                'element' => 'checkbox',
            ),
        );
    }

    /**
     *
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     *
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'iso_code ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * convert an amount from a currency to another
     * using iso codes
     *
     * @param float       $amount
     * @param string|null $currencyFrom iso code of the currency to be converted
     * @param string|null $currencyTo   iso code of the currency the conversion is made to
     *
     * @return float
     */
    public function convertAmount($amount, $currencyFrom = null, $currencyTo = null)
    {
        if ($currencyFrom == $currencyTo || $currencyFrom === null) {
            return $amount;
        }

        $settings = $this->getSettings();

        if ($currencyTo === null) {
            $currencyTo = $settings['currency'];
        }

        $from = $this->findBy('iso_code', $currencyFrom);
        $to = $this->findBy('iso_code', $currencyTo);

        return number_format((($to['conversion_rate'] / $from['conversion_rate']) * $amount),
            $settings['currency_decimals'], '.', '');
    }

    /**
     *
     * return the currency symbol of a currency based on the iso code
     * if a symbol is not set, will return the iso code input
     *
     * @param string $isoCode
     *
     * @return string
     */
    public function getSymbol($isoCode)
    {
        $row = $this->findBy('iso_code', $isoCode);
        if (!empty($row['symbol'])) {
            return $row['symbol'];
        }

        return $isoCode;
    }
}

