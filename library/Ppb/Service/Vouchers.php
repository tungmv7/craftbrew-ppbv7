<?php

/**
 *
 * PHP Pro Bid $Id$ prGXPi2Bx0EFyJCFBGmC2+Gm6BHbHE+y126uXfs9GBg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * vouchers table service class
 * - percentage vouchers apply on each fee row specifically
 * - flat amount vouchers apply on the total (not on each row)
 */


namespace Ppb\Service;

use Cube\Db\Expr,
    Ppb\Db\Table;

class Vouchers extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\Vouchers());
    }

    /**
     *
     * create or update a newsletter
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

            $data['updated_at'] = new Expr('now()');
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
     * find voucher by code and owner
     * (if null get an admin voucher)
     *
     * @param string $voucherCode
     * @param int    $userId
     *
     * @return \Ppb\Db\Table\Row\Voucher|null
     */
    public function findBy($voucherCode, $userId = null)
    {
        $select = $this->_table->select()
            ->where('code = ?', strval($voucherCode));

        if ($userId) {
            $select->where('user_id = ?', $userId);
        }
        else {
            $select->where('user_id is null');
        }

        return $this->_table->fetchRow($select);
    }

    /**
     *
     * prepare listing data for when saving to the table
     * if listing is scheduled, 'closed' = 1
     *
     * important: the daylight saving changes will automatically be calculated when setting the end time!
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        if ($data['uses_remaining'] == '') {
            $data['uses_remaining'] = null;
        }

        if ($data['expiration_date'] == '') {
            $data['expiration_date'] = null;
        }

        return parent::_prepareSaveData($data);
    }

    /**
     *
     * delete a voucher row from the table
     *
     * @param int $id     the id of the row to be deleted
     * @param int $userId the id of owner of the row
     *
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

