<?php

/**
 *
 * PHP Pro Bid $Id$ U/lhz+ZsL7mkV8lXH8mIsjwQ4h+sVXDM33lvjLfNkLw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * members module - selling controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Paginator;

class Selling extends AbstractAction
{

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    public function init()
    {
        $this->_listings = new Service\Listings();
    }

    public function Browse()
    {
        $filter = $this->getRequest()->getParam('filter', 'open');

        $select = $this->_listings->select(Service\Listings::SELECT_MEMBERS);

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getParam('id');
            $option = $this->getRequest()->getParam('option');

            $messages = array();

            $ids = array_filter(
                array_values((array)$id));

            $counter = null;

            if (count($ids) > 0) {
                $where = $this->_listings->getTable()->getAdapter()->quoteInto("id IN (?)", $ids);
                $listings = $this->_listings->fetchAll($where, null, null, null, false, true);
                $messages = $listings->changeStatus($option);

                $counter = count($listings);
            }

            if ($counter > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($this->_listings->getStatusMessage($option), $counter),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('No listings have been updated.'),
                    'class' => 'alert-danger',
                ));
            }

            $messages = array_filter($messages);

            if (count($messages) > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $messages,
                    'class' => 'alert-info',
                ));
            }
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_listings->getTable()));

        $itemsPerPage = $this->getRequest()->getParam('limit', 10);
        $itemsPerPage = ($itemsPerPage > 80) ? 80 : $itemsPerPage;

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage($itemsPerPage)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'filter'             => $filter,
            'keywords'           => $this->getRequest()->getParam('keywords'),
            'listingId'          => $this->getRequest()->getParam('listing_id'),
            'paginator'          => $paginator,
            'messages'           => $this->_flashMessenger->getMessages(),
            'params'             => $this->getRequest()->getParams(),
            'itemsPerPage'       => $itemsPerPage,
            'sellerVerification' => !($this->_user->isVerified('seller')),
        );
    }

}

