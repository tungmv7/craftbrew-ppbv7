<?php

/**
 *
 * PHP Pro Bid $Id$ 2ndew8p0deLDyYin8EjfXAjImuy5NHoIkonH9IsLtFA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing user notifications emails generator class
 */

namespace Listings\Model\Mail;

use Ppb\Model\BaseMail,
    Ppb\Db\Table\Row\Offer as OfferModel,
    Ppb\Db\Table\Row\Listing as ListingModel;

class UserNotification extends BaseMail
{

    /**
     *
     * offer declined notification
     * notifies the user that has posted the offer, because the receiver has declined it.
     *
     * @param ListingModel $listing
     * @param OfferModel   $offer
     *
     * @return $this
     */
    public function offerDeclined(ListingModel $listing, OfferModel $offer)
    {
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $offer->findParentRow('\Ppb\Db\Table\Users', 'User');

        $this->setData(array(
            'listing' => $listing,
            'offer'   => $offer,
            'link'    => array('module' => 'members', 'controller' => 'offers', 'action' => 'details', 'id' => $offer['topic_id']),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - Offer Declined'), $listing['name']));

        $this->_view->headerMessage = $this->_('Offer Declined');
        $this->_view->clearContent()
            ->process('emails/offer-declined.phtml');

        $this->setSend(
            $user->emailOffersNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * offer accepted notification
     * notifies the user that has posted the offer, because the receiver has accepted it.
     *
     * @param ListingModel $listing
     * @param OfferModel   $offer
     *
     * @return $this
     */
    public function offerAccepted(ListingModel $listing, OfferModel $offer)
    {
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $offer->findParentRow('\Ppb\Db\Table\Users', 'User');

        $this->setData(array(
            'listing' => $listing,
            'offer'   => $offer,
            'link'    => array('module' => 'members', 'controller' => 'offers', 'action' => 'details', 'id' => $offer['topic_id']),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - Offer Accepted'), $listing['name']));

        $this->_view->headerMessage = $this->_('Offer Accepted');
        $this->_view->clearContent()
            ->process('emails/offer-accepted.phtml');

        $this->setSend(
            $user->emailOffersNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * offer withdrawn seller notification
     * notifies the user that has received the offer, because the poster has withdrawn it.
     *
     * @param ListingModel $listing
     * @param OfferModel   $offer
     *
     * @return $this
     */
    public function offerWithdrawn(ListingModel $listing, OfferModel $offer)
    {
        /** @var \Ppb\Db\Table\Row\User $receiver */
        $receiver = $offer->findParentRow('\Ppb\Db\Table\Users', 'Receiver');
        /** @var \Ppb\Db\Table\Row\User $poster */
        $poster = $offer->findParentRow('\Ppb\Db\Table\Users', 'User');

        $this->setData(array(
            'listing' => $listing,
            'offer'   => $offer,
            'poster'  => $poster,
            'link'    => array('module' => 'members', 'controller' => 'offers', 'action' => 'details', 'id' => $offer['topic_id']),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($receiver->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - Offer Withdrawn'), $listing['name']));

        $this->_view->headerMessage = $this->_('Offer Withdrawn');
        $this->_view->clearContent()
            ->process('emails/offer-withdrawn.phtml');

        $this->setSend(
            $receiver->emailOffersNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }


    /**
     *
     * new offer (cash/swap) notification
     * we notify the receiver, because its the poster that posts it.
     * will work for counter-offers as well
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @param \Ppb\Db\Table\Row\Offer   $offer
     *
     * @return $this
     */
    public function newOffer(ListingModel $listing, OfferModel $offer)
    {
        /** @var \Ppb\Db\Table\Row\User $receiver */
        $receiver = $offer->findParentRow('\Ppb\Db\Table\Users', 'Receiver');
        /** @var \Ppb\Db\Table\Row\User $poster */
        $poster = $offer->findParentRow('\Ppb\Db\Table\Users', 'User');

        $this->setData(array(
            'listing' => $listing,
            'offer'   => $offer,
            'poster'  => $poster,
            'link'    => array('module' => 'members', 'controller' => 'offers', 'action' => 'details', 'id' => $offer['topic_id']),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($receiver->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - New Offer Posted'), $listing['name']));

        $this->_view->headerMessage = $this->_('New Offer Notification');
        $this->_view->clearContent()
            ->process('emails/new-offer.phtml');

        $this->setSend(
            $receiver->emailOffersNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

}