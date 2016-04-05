<?php

/**
 *
 * PHP Pro Bid $Id$ vczOEJasrBSlO0BTfRYcEogTYg3phIm7Bzl2MKvZk3U=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * store subscriptions table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\StoresSubscriptions as StoresSubscriptionsTable,
    Cube\Controller\Front;

class StoresSubscriptions extends AbstractServiceTable
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
            $this->setDefaultCurrency($settings['currency']);
        }

        $this->setTable(
            new StoresSubscriptionsTable());
    }

    /**
     *
     * set the default currency variable
     *
     * @param string $currency
     *
     * @return \Ppb\Service\Table\StoresSubscriptions
     */
    public function setDefaultCurrency($currency)
    {
        $this->_defaultCurrency = $currency;

        return $this;
    }

    /**
     *
     * get all store subscriptions
     * to be used for the store subscription selector
     *
     * @return array
     */
    public function getMultiOptions()
    {
        $data = array();

        $translate = $this->getTranslate();

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $subscriptions = $this->fetchAll();

        foreach ($subscriptions as $subscription) {
            /** @var \Ppb\Db\Table\Row\StoreSubscription $subscription */
            $data[$subscription['id']] = $translate->_($subscription['name']);

            /** @var \Cube\View $view */
            if ($view->isHelper('storeSubscription')) {
                $data[$subscription['id']] .= ' - ' . $view->storeSubscription($subscription)->description();
            }
        }

        return $data;
    }

    /**
     *
     * get all table columns needed to generate the
     * stores subscriptions table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Subscription Name'),
                'element_id' => 'name',
            ),
            array(
                'label'      => $this->_('Price'),
                'class'      => 'size-small',
                'element_id' => 'price',
            ),
            array(
                'label'      => $this->_('# Listings'),
                'class'      => 'size-mini',
                'element_id' => 'listings',
            ),
            array(
                'label'      => $this->_('Recurring [days]'),
                'class'      => 'size-mini',
                'element_id' => 'recurring_days',
            ),
            array(
                'label'      => $this->_('Featured Store'),
                'class'      => 'size-mini',
                'element_id' => 'featured_store',
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
     * get all form elements that are needed to generate the
     * stores subscriptions table in the admin area
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
                'id'         => 'name',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'id'         => 'price',
                'element'    => 'text',
                'prefix'     => $this->_defaultCurrency,
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'listings',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'recurring_days',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'           => 'featured_store',
                'element'      => 'checkbox',
                'multiOptions' => array(
                    1 => null,
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
     * @return \Ppb\Db\Table\Rowset\StoresSubscriptions
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'price ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     *
     * @param array $data
     * @return \Ppb\Service\Table\AbstractServiceTable
     * @throws \InvalidArgumentException
     */
    public function save(array $data)
    {
        if (!isset($data['listings'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'listings'.");
        }

        foreach ($data['listings'] as $key => $value) {
            $data['listings'][$key] = ($value <= 0) ? 1 : $value;
        }

        return parent::save($data);
    }
}

