<?php

/**
 *
 * PHP Pro Bid $Id$ K9XX3eTUECwOh4n55QE6tdfVoZxrJa5mvgM8j0ZExgQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * transactions table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Transactions as TransactionsTable,
        Cube\Db\Expr;

class Transactions extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new TransactionsTable());
    }

    /**
     *
     * create or update a transaction
     *
     * @param array $data
     * @return int          the id of the inserted/updated row
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
            $this->_table->insert($data);

            $id = $this->_table->lastInsertId();
        }

        return $id;
    }

}

