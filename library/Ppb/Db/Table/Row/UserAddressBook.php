<?php

/**
 *
 * PHP Pro Bid $Id$ /63RbIncwP65/xg9PUn0LD0RpbVmGBG1jIeHum7ETHU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * users address book table row object model
 */

namespace Ppb\Db\Table\Row;

class UserAddressBook extends AbstractRow
{

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('address');

    /**
     *
     * check if the address is the user's primary address
     *
     * @return bool
     */
    public function isPrimary()
    {
        return (bool)$this->getData('is_primary');
    }

    /**
     *
     * set the address as primary
     *
     * @return bool
     */
    public function setPrimary()
    {
        if (!$this->isPrimary()) {
            $addresses = $this->findParentRow('\Ppb\Db\Table\Users')
                    ->findDependentRowset('\Ppb\Db\Table\UsersAddressBook');

            /** @var \Ppb\Db\Table\Row\UserAddressBook $address */
            foreach ($addresses as $address) {
                $address->save(array(
                    'is_primary' => ($this->getData('id') == $address['id']) ? 1 : 0,
                ));
            }

            return true;
        }

        return false;
    }


    /**
     *
     * check if an address can be edited (if not part of an invoice)
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canEdit()
    {
        $translate = $this->getTranslate();

        if ($this->_usedInSales() && !$this->isPrimary()) {
            return $translate->_('Cannot edit an address that is used in an invoice.');
        }

        return true;
    }

    /**
     *
     * check if an address can be deleted (if not part of an invoice or the primary address)
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canDelete()
    {
        $translate = $this->getTranslate();

        if ($this->isPrimary()) {
            return $translate->_('The primary address cannot be deleted.');
        }
        else if ($this->_usedInSales()) {
            return $translate->_('This address cannot be removed because it is used in an invoice.');
        }

        return true;
    }

    /**
     *
     * check if the address has been used in a purchase as billing and/or shipping address
     *
     * @return bool
     */
    protected function _usedInSales()
    {
        $shippingAddress = $this->findDependentRowset('\Ppb\Db\Table\Sales', 'BillingAddress');
        if (count($shippingAddress) > 0) {
            return true;
        }
        else {
            $billingAddress = $this->findDependentRowset('\Ppb\Db\Table\Sales', 'ShippingAddress');
            if (count($billingAddress) > 0) {
                return true;
            }
        }

        return false;
    }
}

