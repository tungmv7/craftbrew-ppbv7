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
 * abstract accounting/transactions table row object model
 */

namespace Ppb\Db\Table\Row;

abstract class AbstractAccounting extends AbstractRow
{

    /**
     *
     * accounting/transactions row invoice details link
     *
     * @return array
     */
    public function link()
    {
        $array = array(
            'action' => 'view-invoice',
        );

        $array['type'] = ($this->getData('transaction_type') == 'receipt') ? 'transactions' : 'accounting';

        if ($this->getData('listing_id')) {
            $array['listing_id'] = $this->getData('listing_id');
        }
        else {
            $array['id'] = $this->getData('id');
        }

        return $array;
    }

    /**
     *
     * only the admin and the owner can view the row
     *
     * @return bool
     */
    public function canView()
    {
        $user = $this->getUser();

        if ($user->getData('id') == $this->getData('user_id') || $user->getData('role') == 'Admin') {
            return true;
        }

        return false;
    }

    /**
     *
     * return the amount without tax
     *
     * @return float
     */
    public function amountNoTax()
    {
        return $this->getData('amount') - $this->taxAmount();
    }

    /**
     *
     * return the tax amount
     *
     * @return float
     */

    public function taxAmount()
    {
        $amount = $this->getData('amount');
        $taxRate = (1 + $this->getData('tax_rate') / 100);

        return round(($amount - ($amount / $taxRate)), 2);
    }

    /**
     *
     * return total amount
     *
     * @return float
     */
    public function totalAmount()
    {
        return $this->getData('amount');
    }

    /**
     *
     * return the name of the transaction row for display purposes
     *
     * @return string
     */
    public function displayName()
    {
        $name = \Ppb\Utility::unserialize($this->getData('name'));

        if (is_array($name)) {
            $translate = $this->getTranslate();
            $string = (null !== $translate) ? $translate->_($name['string']) : $name['string'];

            return vsprintf($string, $name['args']);
        }

        return $name;
    }

    abstract public function caption();
}

