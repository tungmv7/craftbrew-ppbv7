<?php

/**
 *
 * PHP Pro Bid $Id$ fmhPPOQ0PqK4WdjMLqox6QBCg6uOKEVn1cgZUVDVX0Q=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * bids table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service,
        Cube\Db\Expr;

class Bid extends AbstractRow
{
    /**
     * bid statuses
     */

    const STATUS_HIGH_BID = 0;
    const STATUS_OUTBID = 1;

    /**
     *
     * allowed bid statuses
     *
     * @var array
     */
    public static $statuses = array(
        self::STATUS_HIGH_BID => 'High Bid',
        self::STATUS_OUTBID   => 'Outbid',
    );

    /**
     *
     * check if the logged in user can retract the bid
     * (only the poster can retract it, and only high bids can be retracted)
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @return bool
     */
    public function canRetract(Listing $listing = null)
    {
        $settings = $this->getSettings();

        if ($settings['enable_bid_retraction']) {
            if ($listing === null) {
                /** @var \Ppb\Db\Table\Row\Listing $listing */
                $listing = $this->findParentRow('\Ppb\Db\Table\Listings');
            }

            if (
                    !$listing->getData('closed') &&
                    (strtotime($listing->getData('end_time')) > (time() + ($settings['bid_retraction_hours'] * 60 * 60)))
            ) {
                $user = $this->getUser();

                if ($this->getData('user_id') == $user['id'] && !$this->getData('outbid')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * retract bid
     * theory of operation:
     * - first thing, all bids from the same user will have the maximum
     *   bid column updated to be equal with the amount field
     * - the bid will be deleted
     * - we retrieve the next bid, we delete it and place it again
     *   so that the new high bid has its amount set correctly
     *
     * @return bool
     */
    public function retract()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->findParentRow('\Ppb\Db\Table\Listings');

        if ($this->canRetract($listing)) {
            $adapter = $this->getTable()->getAdapter();

            $this->getTable()->update(
                array(
                    'maximum_bid' => new Expr('amount')
                ),
                array(
                    $adapter->quoteInto('listing_id = ?', $listing['id']),
                    $adapter->quoteInto('user_id = ?', $this->getData('user_id')),
                    $adapter->quoteInto('outbid = ?', 1)
                ));

            $bid = clone $this;

            $this->delete();

            $bidsService = new Service\Bids();
            $highBid = $bidsService->fetchAll(
                $bidsService->getTable()->select()
                        ->where('listing_id = ?', $listing['id'])
                        ->order('id DESC')
            )->getRow(0);

            if ($highBid instanceof Bid) {
                $userId = $highBid->getData('user_id');
                $data = array(
                    'amount' => $highBid->getData('maximum_bid')
                );

                $highBid->delete();

                $listing->placeBid($data, 'bid', $userId);
            }

            // send mail notification to seller TODO: call not ideal
            $mail = new \Listings\Model\Mail\OwnerNotification();
            $mail->bidRetraction($listing, $bid)->send();

            return true;
        }

        return false;
    }
}

