<?php

/**
 *
 * PHP Pro Bid $Id$ 7MwoNQ7Npsoav4GNW/w+FRoIBuYAY8vB4es857XfV6Q=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * members module - offers management controller
 * (buyer, seller can access these actions)
 *
 * browse offers
 * delete invoices
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Members\Form,
    Ppb\Service,
    Cube\Paginator;

class Offers extends AbstractAction
{

    /**
     *
     * offers service
     *
     * @var \Ppb\Service\Offers
     */
    protected $_offers;

    /**
     *
     * offer types to display ('selling', 'buying')
     *
     * @var string
     */
    protected $_type;

    public function init()
    {
        $this->_offers = new Service\Offers();
        $this->_type = $this->getRequest()->getParam('type', 'selling');
    }

    /**
     * TODO: move these actions in a new separate Offers controller (which will have the browse, accept, decline and later counter offer actions)
     */
    public function Browse()
    {
        $filter = $this->getRequest()->getParam('filter', 'all');
        $keywords = $this->getRequest()->getParam('keywords');
        $listingId = $this->getRequest()->getParam('listing_id');

        $table = $this->_offers->getTable();
        $adapter = $table->getAdapter();
        $prefix = $table->getPrefix();

        $where = array();
        $where[] = $adapter->quoteInto('(o.user_id = ? OR o.receiver_id = ?)', $this->_user['id']);

        switch ($this->_type) {
            case 'buying':
                $where[] = $adapter->quoteInto('l.user_id != ?', $this->_user['id']);
                // listing not owner
                break;
            default:
                // listing owner
                $where[] = $adapter->quoteInto('l.user_id = ?', $this->_user['id']);
                break;
        }

        if ($listingId) {
            $where[] = $adapter->quoteInto('l.id = ?', $listingId);
        }

        if (!empty($keywords)) {
            $params = '%' . str_replace(' ', '%', $keywords) . '%';
            $where[] = $adapter->quoteInto('l.name LIKE ?', $params);
        }

        if (in_array($filter, array('pending', 'accepted', 'declined', 'withdrawn'))) {
            $where[] = $adapter->quoteInto('o.status = ?', $filter);
        }

        $where = (count($where) > 0) ? 'WHERE ' . implode(' AND ', $where) : '';

        $statement = $table->getAdapter()
            ->query("SELECT o.id
                FROM " . $prefix . "offers AS o
                INNER JOIN (
                    SELECT max(id) AS MaxID, topic_id
                    FROM " . $prefix . "offers
                    GROUP BY topic_id
                ) AS p ON o.topic_id = p.topic_id AND o.id=p.MaxID
                INNER JOIN " . $prefix . "listings AS l ON l.id=o.listing_id
                " . $where . "
                ORDER BY o.created_at DESC");

        $result = $statement->fetchAll();

        $paginator = new Paginator(
            new Paginator\Adapter\ArrayAdapter($result));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller'    => ($this->_type == 'selling') ? 'Selling' : 'Buying',
            'filter'        => $filter,
            'keywords'      => $keywords,
            'listingId'     => $listingId,
            'type'          => $this->_type,
            'paginator'     => $paginator,
            'offersService' => $this->_offers,
            'messages'      => $this->_flashMessenger->getMessages(),
            'params'        => $this->getRequest()->getParams(),
        );
    }

    public function Accept()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->accept();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been accepted."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("There was an error in accepting the offer: there is not enough "
                    . "quantity available or you are not the owner of the item."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Decline()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->decline();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been declined."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the offer cannot be declined."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Withdraw()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->withdraw();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been withdrawn."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the offer cannot be withdrawn."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Counter()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));

        $canCounter = false;
        if ($offer) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $offer->findParentRow('\Ppb\Db\Table\Listings');

            if ($offer->canCounter($listing)) {
                $canCounter = true;
            }
        }

        if (!$canCounter) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: cannot make a counteroffer for this offer."),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('browse');
        }

        $controller = ($listing->isOwner()) ? 'Selling' : 'Buying';

        $buyerId = $offer->getBuyerId($listing);

        $usersService = new Service\Users();
        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $usersService->findBy('id', $buyerId);
        $buyer->setAddress(
            $this->getRequest()->getParam('shipping_address_id'));

        $form = new \Listings\Form\Purchase($listing, $buyer, $offer['type']);

        $form->setData(array(
            'amount'             => $offer['amount'],
            'product_attributes' => \Ppb\Utility::unserialize($offer['product_attributes']),
        ));


        if ($form->isPost(
            $this->getRequest())
        ) {
            $form->setData(
                $this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $productAttributes = $this->getRequest()->getParam('product_attributes');

                $offer->counter();
                $data = array(
                    'topic_id'           => $offer['topic_id'], // IMPORTANT
                    'receiver_id'        => $offer['user_id'],
                    'quantity'           => 1,
                    'amount'             => $this->getRequest()->getParam('amount'),
                    'product_attributes' => (count($productAttributes) > 0) ? serialize($productAttributes) : null,
                );

                $message = $listing->placeBid($data, $offer['type']);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_($message),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('details', null, null, array('id' => $offer['topic_id']));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => $controller,
            'headline'   => $this->_('Make a Counter Offer'),
            'form'       => $form,
            'listing'    => $listing,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function Details()
    {
        $id = $this->getRequest()->getParam('id');

        $table = $this->_offers->getTable();

        $select = $this->_offers->getTable()->select()
            ->where('topic_id = ?', $id)
            ->where('(user_id = ? OR receiver_id = ?)', $this->_user['id'])
            ->order('created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        if (!$paginator->getPages()->totalItemCount) {
            $this->_helper->redirector()->notFound();
        }

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->_offers->findBy('id', $id)
            ->findParentRow('\Ppb\Db\Table\Listings');

        $controller = ($listing->isOwner()) ? 'Selling' : 'Buying';

        return array(
            'controller' => $controller,
            'headline'   => $this->_('Offer Details'),
            'listing'    => $listing,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );

    }


}
