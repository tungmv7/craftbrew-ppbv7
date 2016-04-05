<?php

/**
 *
 * PHP Pro Bid $Id$ B2mlCyQIeJNF0zNit+3hPHCb4mD1ptuRKZQn9NvobOA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * admin related emails generator class
 */

namespace Admin\Model\Mail;

use Ppb\Model\BaseMail,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Accounting as AccountingModel,
    Cube\Controller\Request\AbstractRequest;

class Admin extends BaseMail
{

    /**
     *
     * contact form email
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     * @return $this
     */
    public function contact(AbstractRequest $request)
    {
        $name = $request->getParam('name');
        $email = $request->getParam('email');

        $this->setData(array(
            'name'    => $name,
            'email'   => $email,
            'message' => nl2br($request->getParam('message')),
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $name)
            ->setTo($this->_settings['admin_email'])
            ->setReplyTo($email, $name)
            ->setSubject('New Contact Message');

        $this->_view->headerMessage = $this->_('New Contact Message');
        $this->_view->clearContent()
            ->process('emails/contact.phtml');

        return $this;
    }

    /**
     *
     * admin listing approval notification
     *
     * @param ListingModel $listing
     * @return $this
     */
    public function listingApproval(ListingModel $listing)
    {
        $this->setData(array(
            'listing' => $listing,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_settings['admin_email'])
            ->setSubject('Listing Approval Notification');

        $this->_view->headerMessage = $this->_('Listing Approval Notification');
        $this->_view->clearContent()
            ->process('emails/listing-approval.phtml');

        return $this;
    }

    /**
     *
     * refund request admin notification
     *
     * @param AccountingModel $accounting
     * @return $this
     */
    public function refundRequest(AccountingModel $accounting)
    {
        $this->setData(array(
            'accounting' => $accounting,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_settings['admin_email'])
            ->setSubject('New Refund Request');

        $this->_view->headerMessage = $this->_('Sale Transaction Refund Request');
        $this->_view->clearContent()
            ->process('emails/refund-request.phtml');

        return $this;
    }

}

