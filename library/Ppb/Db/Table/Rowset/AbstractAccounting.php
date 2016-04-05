<?php

/**
 *
 * PHP Pro Bid $Id$ 1oOUhAp/oWhP7HkY0PWaPCac1BGnj827SIhaaMNE2ug=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * accounting table rowset class
 * @7.5 added currency method, to get the currency of the rowset - required in case the default currency has been changed
 */

namespace Ppb\Db\Table\Rowset;

use Ppb\Db\Table\Row\AbstractAccounting as AbstractAccountingRow;

abstract class AbstractAccounting extends AbstractRowset
{

    /**
     *
     * calculate the amount w/o tax for the selected rowset
     *
     * @return float
     */
    public function amountNoTax()
    {
        $amount = 0;

        /** @var \Ppb\Db\Table\Row\AbstractAccounting $row */
        foreach ($this->_rows as $row) {
            $amount += $row->amountNoTax();
        }

        return $amount;
    }

    /**
     *
     * calculate the total amount for the selected rowset
     *
     * @return float
     */
    public function totalAmount()
    {
        $amount = 0;

        /** @var \Ppb\Db\Table\Row\AbstractAccounting $row */
        foreach ($this->_rows as $row) {
            $amount += $row->totalAmount();
        }

        return $amount;
    }
    /**
     *
     * calculate the tax amount for the selected rowset
     *
     * @return float
     */
    public function taxAmount()
    {
        return $this->totalAmount() - $this->amountNoTax();
    }

    /**
     *
     * get rowset currency
     *
     * @return string|null
     */
    public function currency()
    {
        /** @var \Ppb\Db\Table\Row\AbstractAccounting $row */
        $row = $this->_rows[0];

        if ($row instanceof AbstractAccountingRow) {
            return $row->getData('currency');
        }

        return null;
    }

}

