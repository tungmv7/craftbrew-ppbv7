<?php

/**
 *
 * PHP Pro Bid $Id$ pw4JZI77Vz5pZE6Ta5a1CTuGDAvNsQhA9M5mp9F1GpQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * accounting table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Form\Element\Range,
        Ppb\Service;

class Accounting extends AbstractAccounting
{
    /**
     * refund flags
     */
    const REFUND_ALLOWED = 'allowed';
    const REFUND_REQUESTED = 'requested';
    const REFUND_REFUNDED = 'refunded';
    const REFUND_DECLINED = 'declined';

    /**
     *
     * invoice details page caption
     *
     * @return string
     */
    public function caption()
    {
        $translate = $this->getTranslate();

        return ($this->getData('amount') > 0) ? $translate->_('Debit') : $translate->_('Credit');
    }

    /**
     *
     * check if a refund can be requested for the selected accounting row
     *
     * refunds can be requested by the payer only,
     * and only if the refund requests admin settings are met
     *
     * @return bool
     */
    public function canRequestRefund()
    {
        $settings = $this->getSettings();

        if ($settings['enable_sale_fee_refunds']) {
            $days = (time() - strtotime($this->getData('created_at'))) / (60 * 60 * 24);

            $range = \Ppb\Utility::unserialize($settings['sale_fee_refunds_range']);
            $from = isset($range[Range::RANGE_FROM]) ? doubleval($range[Range::RANGE_FROM]) : 0;
            $to = isset($range[Range::RANGE_TO]) ? doubleval($range[Range::RANGE_TO]) : null;

            $user = $this->getUser();

            if (
                    ($from == 0 || $from < $days) &&
                    ($to == 0 || $to > $days) &&
                    $this->getData('user_id') == $user['id'] &&
                    $this->getData('refund_flag') == self::REFUND_ALLOWED
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the logged in user can process a refund request (admin)
     *
     * @return bool
     */
    public function canProcessRefund()
    {
        if ($this->getData('refund_flag') == self::REFUND_REQUESTED) {
            $user = $this->getUser();

            if ($user->getData('role') == 'Admin') {

                return true;
            }
        }

        return false;
    }

    /**
     *
     * the payer makes a refund request
     *
     * @return bool
     */
    public function makeRefundRequest()
    {
        if ($this->canRequestRefund()) {
            $this->save(array(
                'refund_flag' => self::REFUND_REQUESTED,
            ));

            // MAIL REFUND REQUEST ADMIN NOTIFICATION
            return true;
        }

        return false;
    }

    /**
     *
     * admin accepts the refund request and credits the payer's account balance
     *
     * @param bool $override override settings
     * @return bool
     */
    public function acceptRefundRequest($override = false)
    {
        if ($this->canProcessRefund() || $override) {
            $this->save(array(
                'refund_flag' => self::REFUND_REFUNDED,
            ));

            // MAIL REFUND ACCEPTED USER NOTIFICATION

            // add credit to payer account balance
            /** @var \Ppb\Db\Table\Row\User $user */
            $user = $this->findParentRow('\Ppb\Db\Table\Users');
            $user->save(array(
                'balance' => ($user['balance'] - $this->getData('amount'))
            ));

            $name = sprintf('Sale Transaction Refund -  "%s"', $this->displayName());

            $settings = $this->getSettings();

            $accountingService = new Service\Accounting();
            $accountingService->save(array(
                'name'     => $name,
                'amount'   => (-1) * $this->getData('amount'),
                'user_id'  => $this->getData('user_id'),
                'currency' => $settings['currency'],
            ));

            return true;
        }

        return false;
    }

    /**
     *
     * admin rejects the refund request
     *
     * @return bool
     */
    public function rejectRefundRequest()
    {
        if ($this->canProcessRefund()) {
            $this->save(array(
                'refund_flag' => self::REFUND_DECLINED,
            ));

            // MAIL REFUND REJECTED USER NOTIFICATION

            return true;
        }

        return false;
    }

}

