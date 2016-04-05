<?php

/**
 *
 * PHP Pro Bid $Id$ dvV6aNbAc8IAld4xidam/0rwNx7mD+jBxpm3wnE0aY8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * offers table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service;

class Offer extends AbstractRow
{
    /**
     * offer statuses
     */

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_COUNTER = 'counter';

    /**
     *
     * allowed offer statuses
     *
     * @var array
     */
    public static $statuses = array(
        self::STATUS_PENDING   => 'Pending',
        self::STATUS_ACCEPTED  => 'Accepted',
        self::STATUS_DECLINED  => 'Declined',
        self::STATUS_WITHDRAWN => 'Withdrawn',
        self::STATUS_COUNTER   => 'Counter Offer',
    );

    /**
     *
     * check if the offer can be accepted
     * - must be the offer receiver
     * - the listing has to have enough quantity available
     * - the offer must have the status = pending
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return bool
     */
    public function canAccept(Listing $listing = null)
    {
        if (!$listing instanceof Listing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $this->findParentRow('\Ppb\Db\Table\Listings');
        }

        $user = $this->getUser();
        $quantity = $this->getData('quantity');

        $productAttributes = \Ppb\Utility::unserialize($this->getData('product_attributes'));
        if (
            $listing->canAcceptOffer($quantity, $productAttributes) &&
            $this->getData('receiver_id') == $user['id'] &&
            $this->getData('status') == self::STATUS_PENDING
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if counteroffer can be made to this offer
     * just marks the status flag as "counter"
     *
     * will just clone the canAccept() method
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return bool
     */
    public function canCounter(Listing $listing = null)
    {
        return $this->canAccept($listing);
    }


    /**
     *
     * check if the logged in user can withdraw the offer
     * - must be the offer poster
     * - the offer must have the status = pending
     *
     * @return bool
     */
    public function canWithdraw()
    {
        $user = $this->getUser();

        return ($this->getData('user_id') == $user['id'] &&
            $this->getData('status') == self::STATUS_PENDING) ? true : false;
    }

    /**
     *
     * check if the offer can be declined
     * - must be the offer receiver
     * - the offer must have the status = pending
     *
     * @return bool
     */
    public function canDecline()
    {
        $user = $this->getUser();

        return ($this->getData('receiver_id') == $user['id'] &&
            $this->getData('status') == self::STATUS_PENDING) ? true : false;
    }

    /**
     *
     * determines the id of the buyer in an offer row object
     *
     * @param Listing $listing
     *
     * @return int
     */
    public function getBuyerId(Listing $listing = null)
    {
        if (!$listing instanceof Listing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $this->findParentRow('\Ppb\Db\Table\Listings');
        }

        return ($this->getData('user_id') == $listing['user_id']) ?
            $this->getData('receiver_id') : $this->getData('user_id');
    }

    /**
     *
     * accept the offer and create a new sale
     * the listing is selected with the for update clause so that
     * no other transactions can update the listing while
     * this action is in progress
     *
     * @return bool
     */
    public function accept()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->findParentRow('\Ppb\Db\Table\Listings', null, $this->getTable()->select()->forUpdate());

        if ($this->canAccept($listing)) {
            $this->save(array(
                'status' => self::STATUS_ACCEPTED,
            ));

            //MAIL OFFER ACCEPTED USER NOTIFICATION
            $mail = new \Listings\Model\Mail\UserNotification();
            $mail->offerAccepted($listing, $this)->send();

            $service = new Service\Sales();
            $data = array(
                'buyer_id'  => $this->getBuyerId($listing),
                'seller_id' => $listing['user_id'],
                'listings'  => array(
                    array(
                        'listing_id'         => $listing['id'],
                        'price'              => $this->getData('amount'),
                        'quantity'           => (int)$this->getData('quantity'),
                        'product_attributes' => $this->getData('product_attributes'),
                    ),
                ),
            );
            $service->save($data);

            return true;
        }

        return false;
    }

    /**
     *
     * mark this offer as counter offered
     *
     * @return bool
     */
    public function counter()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->findParentRow('\Ppb\Db\Table\Listings');

        if ($this->canCounter($listing)) {
            $this->save(array(
                'status' => self::STATUS_COUNTER,
            ));

            return true;
        }

        return false;
    }

    /**
     *
     * decline an offer
     *
     * @return bool
     */
    public function decline()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->findParentRow('\Ppb\Db\Table\Listings');

        if ($this->canDecline()) {
            $this->save(array(
                'status' => self::STATUS_DECLINED,
            ));

            //MAIL OFFER DECLINED USER NOTIFICATION
            $mail = new \Listings\Model\Mail\UserNotification();
            $mail->offerDeclined($listing, $this)->send();

            return true;
        }

        return false;
    }


    /**
     *
     * withdraw an offer
     * (only the poster can do this)
     *
     * @return bool
     */
    public function withdraw()
    {
        if ($this->canWithdraw()) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $this->findParentRow('\Ppb\Db\Table\Listings');

            $this->save(array(
                'status' => self::STATUS_WITHDRAWN,
            ));

            //MAIL OFFER WITHDRAWN USER NOTIFICATION
            $mail = new \Listings\Model\Mail\UserNotification();
            $mail->offerWithdrawn($listing, $this)->send();

            return true;
        }

        return false;
    }

}