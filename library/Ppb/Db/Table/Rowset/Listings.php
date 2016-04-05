<?php

/**
 *
 * PHP Pro Bid $Id$ sh8rfeWNnVPvr+T8WeKHn8TeGfj1TYAY214qoRbU3nY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listings table rowset class
 */

namespace Ppb\Db\Table\Rowset;

use Cube\Db\Expr,
    Ppb\Service;

class Listings extends AbstractRowset
{

    /**
     * email notifications keys
     */
    const EMAIL_CLOSED = 'listingsClosed';
    const EMAIL_SUSPENDED = 'listingsSuspended';
    const EMAIL_RELISTED = 'listingsRelisted';
    const EMAIL_APPROVED = 'listingsApproved';

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Listing';

    /**
     *
     * automatic flag
     *
     * @var bool
     */
    protected $_automatic = false;

    /**
     *
     * admin flag
     *
     * @var bool
     */
    protected $_admin = false;

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * output messages
     *
     * @var array
     */
    protected $_messages = array();


    /**
     *
     * get listings service
     *
     * @return \Ppb\Service\Listings
     */
    public function getListings()
    {
        if (!$this->_listings instanceof Service\Listings) {
            $this->setListings(
                new Service\Listings());
        }

        return $this->_listings;
    }

    /**
     *
     * set listings service
     *
     * @param \Ppb\Service\Listings $listings
     *
     * @return $this
     */
    public function setListings(Service\Listings $listings)
    {
        $this->_listings = $listings;

        return $this;
    }

    /**
     *
     * get users service
     *
     * @return \Ppb\Service\Users
     */
    public function getUsers()
    {
        if (!$this->_users instanceof Service\Users) {
            $this->setUsers(
                new Service\Users());
        }

        return $this->_users;
    }

    /**
     *
     * set users service
     *
     * @param \Ppb\Service\Users $users
     *
     * @return $this
     */
    public function setUsers(Service\Users $users)
    {
        $this->_users = $users;

        return $this;
    }

    /**
     *
     * set admin flag
     *
     * @param boolean $admin
     *
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->_admin = $admin;

        return $this;
    }

    /**
     *
     * get admin flag
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->_admin;
    }

    /**
     *
     * set automatic flag
     *
     * @param boolean $automatic
     *
     * @return $this
     */
    public function setAutomatic($automatic)
    {
        $this->_automatic = $automatic;

        return $this;
    }

    /**
     *
     * get automatic flag
     *
     * @return boolean
     */
    public function getAutomatic()
    {
        return $this->_automatic;
    }

    /**
     *
     * add single message
     *
     * @param string $message
     *
     * @return $this
     */
    public function addMessage($message)
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            $message = $translate->_($message);
        }

        $this->_messages[] = $message;

        return $this;
    }

    /**
     *
     * get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     *
     * proxy to class methods
     *
     * @param string $methodName
     * @param bool   $admin
     * @param bool   $automatic
     *
     * @return array
     */
    public function changeStatus($methodName = null, $admin = false, $automatic = false)
    {
        if (method_exists($this, $methodName)) {
            $this->setAdmin($admin)
                ->setAutomatic($automatic);

            $this->$methodName();
        }

        return $this->getMessages();
    }

    /**
     *
     * open listings from the selected rowset
     * only scheduled items can be opened, ended items can only be relisted
     *
     * @return $this
     */
    public function open()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if (strtotime($listing['start_time']) > time() && $listing['closed'] == 1) {
                $params['closed'] = 0;
                $params['start_time'] = new Expr('now()');

                $listing->save($params);
            }
        }

        return $this;
    }

    /**
     *
     * close listings from the selected rowset
     * open items with end time > current time can be closed
     * send emails when listings have been closed (single email per user)
     *
     * @return $this
     */
    public function close()
    {
        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $listing->close($this->_automatic);
            if ($listing->getClosedFlag() === true) {
                $emails[$listing['user_id']][] = $listing;
            }
        }

        return $this;
    }

    /**
     *
     * relist listings from the selected rowset
     * closed ended items can be relisted
     * send emails when listings have been relisted (single email per user)
     *
     * @return $this
     */
    public function relist()
    {
        $usersService = $this->getUsers();
        $listingsService = $this->getListings();
        $settings = $this->getSettings();

        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $relist = false;
            if (!$this->_automatic) {
                $relist = true;
            }
            else if ($settings['auto_relist'] && $listing['nb_relists'] > 0) {
                if (!$listing['auto_relist_sold']) {
                    if (!$listing->countDependentRowset('\Ppb\Db\Table\SalesListings')) {
                        $relist = true;
                    }
                }
                else {
                    $relist = true;
                }
            }

            if ($relist) {
                $listingId = $listing->relist($this->_automatic);

                $newListing = $listingsService->findBy('id', $listingId, false, true);
                if (!$this->_admin) {
                    $message = $newListing->processPostSetupActions();
                    $this->addMessage($message);
                }
                else {
                    $newListing->updateActive();
                    $newListing->save(array(
                        'approved' => 1,
                    ));
                }

                $emails[$listing['user_id']][] = $newListing;
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsRelisted()
                ->send();
        }

        return $this;
    }


    /**
     *
     * list selected drafts / bulk items - it will work like relisting same
     *
     * @return $this
     */
    public function draftsList()
    {
        $listingsService = $this->getListings();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $listingId = $listing->setRelistMethod('same')
                ->relist();

            $newListing = $listingsService->findBy('id', $listingId, false, true);

            if (!$this->_admin) {
                $message = $newListing->processPostSetupActions();
                $this->addMessage($message);
            }
            else {
                $newListing->updateActive();
                $newListing->save(array(
                    'approved' => 1,
                ));
            }
        }

        return $this;
    }

    /**
     *
     * activate listings from the selected rowset
     * only suspended & approved items can be activated
     *
     * @return $this
     */
    public function activate()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['active'] != 1 && $listing['approved'] == 1) {
                $listing->updateActive(1);
            }
        }

        return $this;
    }

    /**
     *
     * approve listings from the selected rowset
     * unapproved items can be approved
     * send emails when listings are approved by admin (single email per user)
     *
     * @return $this
     */
    public function approve()
    {
        $usersService = $this->getUsers();
        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['approved'] == 0) {
                $listing->updateApproved(1);
                $emails[$listing['user_id']][] = $listing;
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsApproved()
                ->send();
        }

        return $this;
    }

    /**
     *
     * suspend listings from the selected rowset
     * active items can be suspended
     * send emails when listings are suspended by admin (single email per user)
     *
     * @return $this
     */
    public function suspend()
    {
        $usersService = $this->getUsers();
        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['active'] == 1 && $listing['approved'] == 1) {
                $listing->updateActive(-1);

                if ($this->_admin) {
                    $emails[$listing['user_id']][] = $listing;
                }
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsSuspended()
                ->send();
        }

        return $this;
    }

    /**
     *
     * remove marked deleted status from marked deleted items
     *
     * @return $this
     */
    public function undelete()
    {
        $this->save(array(
            'deleted' => 0,
        ));

        return $this;
    }

    /**
     *
     * delete all rows from the rowset individually
     * mark deleted (if user) or delete (if admin) any item
     *
     * @return $this
     */
    public function delete()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $listing->delete($this->_admin);
        }

        return $this;
    }

}

