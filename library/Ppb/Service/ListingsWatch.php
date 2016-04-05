<?php

/**
 *
 * PHP Pro Bid $Id$ wgmNCGGyCATf9DsHTJQKDfH+blKz6IyY3pottmC//J0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * listings watch table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
    Ppb\Db\Table\ListingsWatch as ListingsWatchTable;

class ListingsWatch extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ListingsWatchTable());
    }

    /**
     *
     * create or update a row in the listings watch table
     *
     * @param array $data
     *
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
     * delete data from the table
     *
     * @param int|array $listingIds the id of the listing(s)
     * @param int       $userId     the id of the user that is watching the listing
     * @param string    $userToken  user token cookie
     *
     * @return int returns the number of affected rows
     */
    public function delete($listingIds, $userId, $userToken)
    {
        $adapter = $this->_table->getAdapter();

        if (!is_array($listingIds)) {
            $listingIds = array($listingIds);
        }

        $where[] = $adapter->quoteInto('listing_id IN (?)', $listingIds);

        if ($userId !== null) {
            $where[] = 'user_token = "' . $userToken . '" OR user_id = "' . $userId . '"';
        }
        else {
            $where[] = $adapter->quoteInto('user_token = ?', $userToken);
        }

        return $this->_table->delete($where);
    }
}

