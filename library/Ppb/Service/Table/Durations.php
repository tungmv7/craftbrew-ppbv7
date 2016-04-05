<?php

/**
 *
 * PHP Pro Bid $Id$ jES7CgFkaUVI51NJdg3WtOLzM3gyUx69ysCqEbpnY4g=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * currencies table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\Durations as DurationsTable;

class Durations extends AbstractServiceTable
{

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new DurationsTable());
    }

    /**
     *
     * get all durations
     * to be used for the listing duration selector
     *
     * @param string $listingType
     * @return array
     */
    public function getMultiOptions($listingType = null)
    {
        $data = array();

        $translate = $this->getTranslate();

        $settings = $this->getSettings();
        if ($listingType == 'product' && $settings['enable_unlimited_duration']) {
            $data[0] = $translate->_('Unlimited');
        }

        $displayOptions = ($listingType == 'product' && $settings['enable_unlimited_duration'] && $settings['force_unlimited_duration']) ?
                false : true;

        if ($displayOptions) {
            $rows = $this->fetchAll()->toArray();

            foreach ((array)$rows as $row) {
                $data[(string)$row['days']] = $translate->_($row['description']);
            }
        }

        return $data;
    }

    /**
     *
     * get all table columns needed to generate the
     * durations management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Days'),
                'class'      => 'size-mini',
                'element_id' => 'days',
            ),
            array(
                'label'      => $this->_('Description'),
                'element_id' => 'description',
            ),
            array(
                'label'      => $this->_('Order ID'),
                'class'      => 'size-mini',
                'element_id' => 'order_id',
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
     * durations management table in the admin area
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
                'id'         => 'days',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'description',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'id'         => 'order_id',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
//            array(
//                'id' => 'selected',
//                'element' => 'radio',
//            ),
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
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'order_id ASC, days ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

}

