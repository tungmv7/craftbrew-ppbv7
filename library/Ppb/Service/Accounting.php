<?php

/**
 *
 * PHP Pro Bid $Id$ pw4JZI77Vz5pZE6Ta5a1CTuGDAvNsQhA9M5mp9F1GpQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * accounting table service class
 *
 * theory of operation
 * - all listing related fees that have been debited from a user's account balance are saved in this table
 *   -> listing setup fees grouped by listing id
 *   -> sale transaction fees
 *   -> other fees that have been debited against the user's balance (like automatic subscription renewals)
 * - credits made on the user's balance
 *   -> admin balance adjustments
 *   -> sale transactions refunds
 *
 * IMPORTANT: any payments are saved directly and only in the transactions table
 */

namespace Ppb\Service;

use Ppb\Db\Table\Accounting as AccountingTable,
        Cube\Db\Expr;

class Accounting extends AbstractService
{

    /**
     *
     * listing id
     * (for listing setup fees)
     *
     * @var integer
     */
    protected $_listingId = null;

    /**
     *
     * sale id
     * (for sale transaction fees)
     *
     * @var integer
     */
    protected $_saleId = null;

    /**
     *
     * user id
     *
     * @var integer
     */
    protected $_userId = null;

    /**
     *
     * gateway id
     *
     * @var integer
     */
    protected $_gatewayId = null;

    /**
     *
     * refund flag
     *
     * @var string
     */
    protected $_refundFlag = null;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new AccountingTable());
    }

    /**
     *
     * get listing id
     *
     * @return integer
     */
    public function getListingId()
    {
        return $this->_listingId;
    }

    /**
     *
     * set listing id
     *
     * @param integer $listingId
     * @return $this
     */
    public function setListingId($listingId)
    {
        $this->_listingId = (int)$listingId;

        return $this;
    }

    /**
     *
     * set sale id
     *
     * @param int $saleId
     * @return $this
     */
    public function setSaleId($saleId)
    {
        $this->_saleId = $saleId;

        return $this;
    }

    /**
     *
     * get sale id
     *
     * @return int
     */
    public function getSaleId()
    {
        return $this->_saleId;
    }


    /**
     *
     * get user id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     *
     * set user id
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->_userId = (int)$userId;

        return $this;
    }

    /**
     *
     * get gateway id
     *
     * @return integer
     */
    public function getGatewayId()
    {
        return $this->_gatewayId;
    }

    /**
     *
     * set gateway id
     *
     * @param integer $gatewayId
     * @return $this
     */
    public function setGatewayId($gatewayId)
    {
        $this->_gatewayId = (int)$gatewayId;

        return $this;
    }

    /**
     *
     * set refund flag
     *
     * @param string $refundFlag
     * @return $this
     */
    public function setRefundFlag($refundFlag)
    {
        $this->_refundFlag = $refundFlag;

        return $this;
    }

    /**
     *
     * get refund flag
     *
     * @return string
     */
    public function getRefundFlag()
    {
        return $this->_refundFlag;
    }


    /**
     *
     * create or update a row in the accounting table
     * if amounts are positive, we have debit, and for negative amounts we have credit
     *
     * @param array $data
     * @return $this
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if ($data['amount'] != 0) {
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
        }

        return $this;
    }

    /**
     *
     * save multiple table rows at once
     *
     * @param array $data
     * @return $this
     */
    public function saveMultiple(array $data)
    {
        foreach ($data as $row) {

            $this->save($row);
        }

        return $this;
    }

    /**
     *
     * prepare accounting data for when saving to the table
     *
     * @param array $data
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        $data = parent::_prepareSaveData($data);

        if (!array_key_exists('user_id', $data) && $this->_userId !== null) {
            $data['user_id'] = $this->_userId;
        }

        if (!array_key_exists('refund_flag', $data) && $this->_refundFlag !== null) {
            $data['refund_flag'] = $this->_refundFlag;
        }

        if (!array_key_exists('listing_id', $data) && $this->_listingId !== null) {
            $data['listing_id'] = $this->_listingId;
        }

        if (!array_key_exists('sale_id', $data) && $this->_saleId !== null) {
            $data['sale_id'] = $this->_saleId;
        }

        if (!array_key_exists('gateway_id', $data) && $this->_gatewayId !== null) {
            $data['gateway_id'] = $this->_gatewayId;
        }

        return $data;
    }

}

