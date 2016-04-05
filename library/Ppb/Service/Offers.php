<?php

/**
 * 
 * PHP Pro Bid $Id$ 7MwoNQ7Npsoav4GNW/w+FRoIBuYAY8vB4es857XfV6Q=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.6
 */
/**
 * offers table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Offers as OffersTable,
    Cube\Db\Expr;

class Offers extends AbstractService
{

    /**
     * 
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new OffersTable());
    }

    /**
     * 
     * create or update an offer on a listing
     * offers can be updated, but only if they have a 'pending' status.
     * 
     * @param array $data
     *
     * @return int  the id of the created/edited offer row
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

            $id = $row['id'];
        }
        else {
            $data['created_at'] = new Expr('now()');
            $id = $this->_table->insert($data);

            if (!isset($data['topic_id'])) {
                $row = $this->findBy('id', $id);
                $row->save(array(
                    'topic_id' => $id,
                ));
            }
        }

        return $id;
    }
}

