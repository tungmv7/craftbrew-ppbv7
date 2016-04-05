<?php

/**
 *
 * PHP Pro Bid $Id$ RmAxuZZ9sDSxVDM9NaBqZ1l/twhQme5M46HOMXGTRlg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * favorite stores table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
        Ppb\Db\Table\FavoriteStores as FavoriteStoresTable;

class FavoriteStores extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new FavoriteStoresTable());
    }

    /**
     *
     * create or update an advert
     *
     * @param array $data
     * @return $this
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
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }

    /**
     *
     * delete a favorite store row from the table
     *
     * @param int $id     the id of the row to be deleted
     * @param int $userId the id of owner of the row
     * @return int     returns the number of affected rows
     */
    public function delete($id, $userId = null)
    {
        $adapter = $this->_table->getAdapter();

        $where[] = $adapter->quoteInto('id = ?', $id);

        if ($userId !== null) {
            $where[] = $adapter->quoteInto('user_id = ?', $userId);
        }

        return $this->_table->delete($where);
    }
}

