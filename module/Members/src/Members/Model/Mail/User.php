<?php

/**
 *
 * PHP Pro Bid $Id$ tEjlngvwvLF7KFwpv3OUuoP3ePAms+STun7lqkZ8qH8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * user related emails generator class
 */

namespace Members\Model\Mail;

use Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Accounting as AccountingModel,
    Ppb\Db\Table\Row\Sale as SaleModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Model\BaseMail,
    Ppb\View\Helper,
    Ppb\Service,
    Cube\Crypt,
    Cube\Controller\Front;

class User extends BaseMail
{

    /**
     *
     * crypt object
     *
     * @var \Cube\Crypt
     */
    protected $_crypt;

    public function __construct(array $data = null)
    {
        parent::__construct($data);

        $this->_view->setHelper('date', new Helper\Date($this->_settings['date_format']))
            ->setHelper('userDetails', new \Members\View\Helper\UserDetails())
            ->setHelper('saleOptions', new \Listings\View\Helper\SaleOptions())
            ->setHelper('productAttributes', new Helper\ProductAttributes());
    }

    /**
     *
     * set crypt object
     *
     * @param \Cube\Crypt $crypt
     *
     * @return $this
     */
    public function setCrypt(Crypt $crypt)
    {
        $this->_crypt = $crypt;

        return $this;
    }

    /**
     *
     * get crypt object
     *
     * @return \Cube\Crypt
     */
    public function getCrypt()
    {
        if (!$this->_crypt instanceof Crypt) {
            $options = Front::getInstance()->getOption('session');

            $crypt = new Crypt();
            $crypt->setKey($options['secret']);

            $this->setCrypt($crypt);
        }

        return $this->_crypt;
    }

    /**
     *
     * message received notification
     *
     * @param int $messageId the id of the message that has been created
     *
     * @return $this
     */
    public function messageReceived($messageId)
    {
        $messagingService = new Service\Messaging();
        /** @var \Ppb\Db\Table\Row\Message $message */
        $message = $messagingService->findBy('id', $messageId);

        /** @var \Ppb\Db\Table\Row\User $sender */
        $sender = $message->findParentRow('\Ppb\Db\Table\Users', 'Sender');
        /** @var \Ppb\Db\Table\Row\User $receiver */
        $receiver = $message->findParentRow('\Ppb\Db\Table\Users', 'Receiver');

        $this->setData(array(
            'username' => $sender['username'],
            'title'    => $message['title'],
            'message'  => $message['content'],
            'link'     => $message->link(),
        ));

        $translate = $this->_mail->getTranslate();

        $subject = (!empty($message['title'])) ?
            sprintf($translate->_('New Message: %s'), $message['title']) : $this->_('New Message Received');

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($receiver->getData('email'))
            ->setSubject($subject);

        $this->_view->headerMessage = $this->_('New Message Received');
        $this->_view->clearContent()
            ->process('emails/message-received.phtml');

        $this->setSend(
            $receiver->emailMessagingNotifications());
        $this->_view->process('partials/emails/notifications-preferences.phtml');

        return $this;
    }

    /**
     *
     * notify selected user that a subscription he has active is about to expire
     *
     * @param array     $subscription
     * @param UserModel $user
     * @param int       $days
     *
     * @return $this
     */
    public function subscriptionExpirationNotification(array $subscription, UserModel $user, $days)
    {
        $this->setData(array(
            'subscription' => $subscription,
            'user'         => $user,
            'days'         => $days,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Subscription Expiration Notification');

        $this->_view->headerMessage = $this->_('Subscription Expiration Notification');
        $this->_view->clearContent()
            ->process('emails/subscription-expiration-notification.phtml');

        return $this;
    }

    /**
     *
     * notify selected user that a subscription of his has expired
     *
     * @param array     $subscription
     * @param UserModel $user
     *
     * @return $this
     */
    public function subscriptionExpired(array $subscription, UserModel $user)
    {
        $this->setData(array(
            'subscription' => $subscription,
            'user'         => $user,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Subscription Expired');

        $this->_view->headerMessage = $this->_('Subscription Expired');
        $this->_view->clearContent()
            ->process('emails/subscription-expired.phtml');

        return $this;
    }

    /**
     *
     * notify selected user that a subscription of his has been automatically renewed
     *
     * @param array     $subscription
     * @param UserModel $user
     *
     * @return $this
     */
    public function subscriptionRenewed(array $subscription, UserModel $user)
    {
        $this->setData(array(
            'subscription' => $subscription,
            'user'         => $user,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Subscription Renewed');

        $this->_view->headerMessage = $this->_('Subscription Renewed');
        $this->_view->clearContent()
            ->process('emails/subscription-renewed.phtml');

        return $this;
    }

    /**
     *
     * notify user when his maximum debit limit has been exceeded
     *
     * @param UserModel $user
     *
     * @return $this
     */
    public function accountBalanceExceeded(UserModel $user)
    {
        $this->setData(array(
            'user' => $user,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Account Balance Exceeded');

        $this->_view->headerMessage = $this->_('Account Balance Exceeded');
        $this->_view->clearContent()
            ->process('emails/account-balance-exceeded.phtml');

        return $this;
    }

    /**
     *
     * forgot username email
     *
     * @param UserModel $user
     *
     * @return $this
     */
    public function forgotUsername(UserModel $user)
    {
        $this->setData(array(
            'user' => $user,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Username Retrieval');

        $this->_view->headerMessage = $this->_('Username Retrieval');
        $this->_view->clearContent()
            ->process('emails/forgot-username.phtml');

        return $this;
    }

    /**
     *
     * forgot password email
     * will email the new password to the user
     *
     * @param UserModel $user
     * @param string    $password
     *
     * @return $this
     */
    public function forgotPassword(UserModel $user, $password)
    {
        $this->setData(array(
            'user'     => $user,
            'password' => $password,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Password Reset');

        $this->_view->headerMessage = $this->_('Login Details Recovery');
        $this->_view->clearContent()
            ->process('emails/forgot-password.phtml');

        return $this;
    }


    /**
     *
     * refund accepted user notification
     *
     * @param AccountingModel $accounting
     *
     * @return $this
     */
    public function refundAccepted(AccountingModel $accounting)
    {
        $user = $accounting->findParentRow('\Ppb\Db\Table\Users');

        $this->setData(array(
            'accounting' => $accounting,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Refund Request Accepted');

        $this->_view->headerMessage = $this->_('Refund Request Accepted');
        $this->_view->clearContent()
            ->process('emails/refund-accepted.phtml');

        return $this;
    }

    /**
     *
     * refund rejected user notification
     *
     * @param AccountingModel $accounting
     *
     * @return $this
     */
    public function refundRejected(AccountingModel $accounting)
    {
        $user = $accounting->findParentRow('\Ppb\Db\Table\Users');

        $this->setData(array(
            'accounting' => $accounting,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Refund Request Rejected');

        $this->_view->headerMessage = $this->_('Refund Request Rejected');
        $this->_view->clearContent()
            ->process('emails/refund-rejected.phtml');

        return $this;
    }

    /**
     *
     * notify seller of new sale
     *
     * @param SaleModel $sale
     * @param UserModel $seller
     *
     * @return $this
     */
    public function saleSellerNotification(SaleModel $sale, UserModel $seller)
    {
        $this->setData(array(
            'sale' => $sale,
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($seller->getData('email'))
            ->setSubject(
                sprintf($translate->_('Sale Notification - ID: #%s'), $sale['id']));

        $this->_view->headerMessage = $this->_('New Sale Notification');
        $this->_view->clearContent()
            ->process('emails/sale.phtml');

        return $this;
    }

    /**
     *
     * notify buyer of new sale
     *
     * @param SaleModel $sale
     * @param UserModel $buyer
     *
     * @return $this
     */
    public function saleBuyerNotification(SaleModel $sale, UserModel $buyer)
    {
        $this->setData(array(
            'sale' => $sale,
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($buyer->getData('email'))
            ->setSubject(
                sprintf($translate->_('Purchase Notification - ID: #%s'), $sale['id']));

        $this->_view->headerMessage = $this->_('New Purchase Notification');
        $this->_view->clearContent()
            ->process('emails/purchase.phtml');

        return $this;
    }

    public function balancePaymentReminder(UserModel $user)
    {
        $this->setData(array(
            'user' => $user,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject('Balance Payment Reminder');

        $this->_view->headerMessage = $this->_('Account Balance Payment Reminder');
        $this->_view->clearContent()
            ->process('emails/balance-payment-reminder.phtml');

        return $this;
    }

    /**
     *
     * send new listing notification for favorite store subscribers
     *
     * @param ListingModel $listing
     * @param UserModel    $user
     *
     * @return $this
     */
    public function newListingFavoriteStoreNotification(ListingModel $listing, UserModel $user)
    {
        $this->setData(array(
            'listing' => $listing,
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject(
                sprintf($translate->_('Favorite Store New Listing - ID: #%s'), $listing['id']));

        $this->_view->headerMessage = $this->_('Favorite Store Notification');
        $this->_view->clearContent()
            ->process('emails/new-listing-favorite-store.phtml');

        return $this;
    }

    public function newsletter($title, $content, $email, $username, $name = null)
    {
        $this->setData(array(
            'email'            => $email,
            'name'             => $name,
            'title'            => $title,
            'content'          => $content,
            'unsubscribe_link' => array(
                'module'     => 'members',
                'controller' => 'user',
                'action'     => 'newsletter-unsubscribe',
//                'crypt'      => $this->getCrypt()->encrypt($username),
                'email'      => $email,
                'username'   => $username,
            )
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($email, $name)
            ->setSubject($title);

        $this->_view->headerMessage = $title;
        $this->_view->clearContent()
            ->process('emails/newsletter.phtml');

        return $this;
    }

    /**
     *
     * send a generic email to an email address
     *
     * @param string $title
     * @param string $content
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    public function generic($title, $content, $email, $name = null)
    {
        $this->setData(array(
            'email'   => $email,
            'name'    => $name,
            'title'   => $title,
            'content' => $content,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($email, $name)
            ->setSubject($title);

        $this->_view->headerMessage = $title;
        $this->_view->clearContent()
            ->process('emails/generic.phtml');

        return $this;
    }
}

