<?php

/**
 * 
 * PHP Pro Bid $Id$ YZqKr6537xUuJGl6xadD6bQTKNsvSS6IeO5q4wJQIiVhw8E6dVwyVgtH37AzLZkMI8Ytn7X71zcU4tiWJzls7A==
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.4
 */
/**
 * recently viewed listings table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\RecentlyViewedListings as RecentlyViewedListingsTable,
    Cube\Db\Expr;

class RecentlyViewedListings extends AbstractService
{

    /**
     * 
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new RecentlyViewedListingsTable());
    }

    /**
     * 
     * create or update an recently viewed listings row
     * 
     * @param array $data
     * @return \Ppb\Service\Offers
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $this->_table->select()
                    ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {

            $data['updated_at'] = new Expr('now()');
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }
}

