<?php

/**
 *
 * PHP Pro Bid $Id$ q55cVz7IUMtNt05gHLWQvQPVXx0wApQNd26ltnJXR+0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing owner notifications emails generator class
 */

namespace Listings\Model\Mail;

use Ppb\Model\BaseMail,
    Ppb\Service\Listings as ListingsService,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Bid as BidModel;

class OwnerNotification extends BaseMail
{

    /**
     *
     * listings array / rowset
     *
     * @var array|\Ppb\Db\Table\Rowset\Listings
     */
    protected $_listings;

    /**
     *
     * user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * set listings array / rowset
     *
     * @param array|\Ppb\Db\Table\Rowset\Listings $listings
     *
     * @return $this
     */
    public function setListings($listings)
    {
        $this->_listings = $listings;

        $this->setData(array(
            'user'     => $this->_user,
            'listings' => $this->_listings,
        ));

        return $this;
    }

    /**
     *
     * get listings array / rowset
     *
     * @return array|\Ppb\Db\Table\Rowset\Listings
     */
    public function getListings()
    {
        return $this->_listings;
    }

    /**
     *
     * set user model
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->_user = $user;

        $this->setData(array(
            'user'     => $this->_user,
            'listings' => $this->_listings,
        ));


        return $this;
    }

    /**
     *
     * get user model
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        return $this->_user;
    }


    /**
     *
     * listings closed notification
     *
     * @return $this
     */
    public function listingsClosed()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('Listings Closed');

        $this->_view->headerMessage = $this->_('Listings Closed Notification');
        $this->_view->clearContent()
            ->process('emails/listings-closed.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * listings relisted notification
     *
     * @return $this
     */
    public function listingsRelisted()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('Listings Relisted');

        $this->_view->headerMessage = $this->_('Listings Relisted Notification');
        $this->_view->clearContent()
            ->process('emails/listings-relisted.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * listings suspended by admin notification
     *
     * @return $this
     */
    public function listingsSuspended()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('Listings Suspended');

        $this->_view->headerMessage = $this->_('Listings Suspended Notification');
        $this->_view->clearContent()
            ->process('emails/listings-suspended.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }


    /**
     *
     * listings approved by admin notification
     *
     * @return $this
     */
    public function listingsApproved()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('Listings Approved');

        $this->_view->headerMessage = $this->_('Listings Approved Notification');
        $this->_view->clearContent()
            ->process('emails/listings-approved.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * no sale seller notification
     *
     * @return $this
     */
    public function noSale()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('Listings Closed - No Sale');

        $this->_view->headerMessage = $this->_('Listings Closed - No Sale');
        $this->_view->clearContent()
            ->process('emails/no-sale.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * no sale due to under reserve seller notification
     *
     * @return $this
     */
    public function noSaleReserve()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_user->getData('email'))
            ->setSubject('No Sale - Bids Under Reserve');

        $this->_view->headerMessage = $this->_('Listings Closed - No Sale (Bids Under Reserve)');
        $this->_view->clearContent()
            ->process('emails/no-sale-reserve.phtml');

        $this->setSend(
            $this->_user->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * bid retraction seller notification
     *
     * @param ListingModel $listing
     * @param BidModel     $bid
     *
     * @return $this
     */
    public function bidRetraction(ListingModel $listing, BidModel $bid)
    {
        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $listing->findParentRow('\Ppb\Db\Table\Users');

        $this->setData(array(
            'listing' => $listing,
            'bid'     => $bid,
            'bidder'  => $bid->findParentRow('\Ppb\Db\Table\Users'),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($seller->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - Bid Retracted'), $listing['name']));

        $this->_view->headerMessage = $this->_('Bid Retraction Notification');
        $this->_view->clearContent()
            ->process('emails/bid-retraction.phtml');

        $this->setSend(
            $seller->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * new bid seller notification
     *
     * @param int                    $listingId
     * @param \Ppb\Db\Table\Row\User $bidder
     * @param                        $bidAmount
     *
     * @return $this
     */
    public function newBid($listingId, UserModel $bidder, $bidAmount)
    {
        $listingsService = new ListingsService();
        $listing = $listingsService->findBy('id', $listingId);
        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $listing->findParentRow('\Ppb\Db\Table\Users');

        $this->setData(array(
            'listing'   => $listing,
            'bidAmount' => $bidAmount,
            'bidder'    => $bidder
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($seller->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - New Bid Placed'), $listing['name']));

        $this->_view->headerMessage = $this->_('New Bid Notification');
        $this->_view->clearContent()
            ->process('emails/new-bid.phtml');

        $this->setSend(
            $seller->emailSellerNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

}

