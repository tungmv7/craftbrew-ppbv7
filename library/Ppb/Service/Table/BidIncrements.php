<?php

/**
 * 
 * PHP Pro Bid $Id$ u1RyBR3I2oK/ScNxhlnfgAiC5sx4I98SPZ5BR1yyDoA=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * bid increments table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\BidIncrements as BidIncrementsTable;

class BidIncrements extends AbstractServiceTable
{

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new BidIncrementsTable());
    }

    /**
     * 
     * get all table columns needed to generate the 
     * bid increments management table in the admin area
     * 
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label' => $this->_('From'),
                'element_id' => 'tier_from',
                'class' => 'size-small',
            ),
            array(
                'label' => $this->_('To'),
                'element_id' => 'tier_to',
                'class' => 'size-small',
            ),
            array(
                'label' => $this->_('Increment Amount'),
                'element_id' => 'amount',
            ),
            array(
                'label' => $this->_('Delete'),
                'class' => 'size-mini',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     * 
     * get all form elements that are needed to generate the 
     * bid increments management table in the admin area
     * 
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id' => 'id',
                'element' => 'hidden',
            ),
            array(
                'id' => 'amount',
                'element' => 'text',                
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id' => 'tier_from',
                'element' => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id' => 'tier_to',
                'element' => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id' => 'delete',
                'element' => 'checkbox',
            ),
        );
    }

    /**
     * 
     * fetches all matched rows 
     * 
     * @param string|\Cube\Db\Select $where     SQL where clause, or a select object
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'tier_from ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

}

