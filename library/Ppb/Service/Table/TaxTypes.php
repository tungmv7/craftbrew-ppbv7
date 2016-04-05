<?php

/**
 *
 * PHP Pro Bid $Id$ SgG0qjxnlKxoL/9XlE6Axag2s4Mqok3tVX8GyQtZIJg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * tax types table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\TaxTypes as TaxTypesTable,
        Cube\Controller\Front;

class TaxTypes extends AbstractServiceTable
{

    /**
     *
     * locations service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new TaxTypesTable());
    }


    /**
     *
     * get locations table service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Relational\Locations) {
            $this->setLocations(
                new Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations table service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     * @return $this
     */
    public function setLocations(Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * get all tax types
     * to be used for the tax type selector
     *
     * @return array
     */
    public function getMultiOptions()
    {
        $data = array();

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $translate = $this->getTranslate();

        $taxTypes = $this->fetchAll();

        foreach ($taxTypes as $taxType) {
            /** @var \Ppb\Db\Table\Row\TaxType $taxType */
            $data[$taxType['id']] = $translate->_($taxType['name']);

            /** @var \Cube\View $view */
            if ($view->isHelper('taxType')) {
                $data[$taxType['id']] = $view->taxType($taxType)->description();
            }
        }

        return $data;
    }

    /**
     *
     * get all table columns needed to generate the
     * tax types table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Tax Name'),
                'class'      => 'size-mini',
                'element_id' => 'name',
            ),
            array(
                'label'      => $this->_('Description'),
                'class'      => 'size-medium',
                'element_id' => 'description',
            ),
            array(
                'label'      => $this->_('Rate [%]'),
                'class'      => 'size-mini',
                'element_id' => 'amount',
            ),
            array(
                'label'      => $this->_('Applies to Buyers From'),
                'element_id' => 'locations_ids',
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
     * tax types table in the admin area
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
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'description',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-default',
                ),
            ),
            array(
                'id'         => 'amount',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'             => 'locations_ids',
                'element'        => '\\Ppb\\Form\\Element\\ChznSelect',
//                'multiOptions' => $this->getLocations()->getMultiOptions(null, null, false, true), // this query is very slow
                'multiOptions'   => $this->getLocations()->getMultiOptions(
                        $this->getLocations()->getTable()->select()),
                'multiple'       => true,
                'attributes'     => array(
                    'style'            => 'width: 250px;',
                    'data-placeholder' => 'Choose Locations...',
                ),
                'customData' => array(
                    'doubleBrackets' => true,
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
     * unserializes any serialized columns
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rowset = $this->_table->fetchAll($where, $order, $count, $offset);

        foreach ($rowset as $id => $row) {
            foreach ($row as $key => $value) {
                $rowset[$id][$key] = \Ppb\Utility::unserialize($value);
            }
        }

        return $rowset;
    }
}

