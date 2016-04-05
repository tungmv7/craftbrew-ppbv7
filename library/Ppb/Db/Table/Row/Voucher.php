<?php

/**
 *
 * PHP Pro Bid $Id$ LqQT9b2iVzs2xHySxAw0hLOUnjPyg1ekV+GDYMwYoA8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * vouchers table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service;

class Voucher extends AbstractRow
{

    /**
     *
     * check if the voucher is valid
     *
     * @param int $listingId if provided, check if the listing id matches
     *
     * @return bool
     */
    public function isValid($listingId = null)
    {
        $usesRemaining = $this->getData('uses_remaining');
        $expirationDate = $this->getData('expiration_date');

        if ($listingId !== null) {
            $assignedListings = array_filter(array_map('intval', explode(',', $this->getData('assigned_listings'))));

            if (count($assignedListings) && !in_array($listingId, $assignedListings)) {
                return false;
            }
        }

        if (($usesRemaining === null || $usesRemaining > 0) &&
            ($expirationDate === null || strtotime($expirationDate) > time())
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * apply the voucher to a certain amount and return the updated amount
     * if listing id is provided, check and apply by listing id
     *
     * @param float  $amount
     * @param string $currency
     * @param int    $listingId
     *
     * @return float
     */
    public function apply($amount, $currency = null, $listingId = null)
    {
        if ($this->isValid($listingId)) {
            $reductionAmount = $this->getData('reduction_amount');
            switch ($this->getData('reduction_type')) {
                case 'flat':
                    $settings = $this->getSettings();
                    if ($currency !== null && $currency != $settings['currency']) {
                        $currenciesService = new Service\Table\Currencies();
                        $reductionAmount = $currenciesService->convertAmount($reductionAmount, $settings['currency'],
                            $currency);
                    }

                    $amount -= $reductionAmount;
                    if ($amount < 0) {
                        $amount = 0;
                    }
                    break;
                case 'percent':
                    $amount -= $amount * $reductionAmount / 100;
                    break;
            }
        }

        return $amount;
    }

    /**
     *
     * update uses remaining column
     *
     * @return $this
     */
    public function updateUses()
    {
        $usesRemaining = $this->getData('uses_remaining');

        if ($usesRemaining > 0) {
            $this->save(array(
                'uses_remaining' => ($usesRemaining - 1),
            ));
        }

        return $this;
    }

    /**
     *
     * output a description for the voucher
     *
     * @return string
     */
    public function description()
    {
        $translate = $this->getTranslate();
        return $translate->_('Voucher') . ' - ' . $this->getData('code');
    }
}

