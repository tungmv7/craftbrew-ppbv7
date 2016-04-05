<?php

/**
 *
 * PHP Pro Bid $Id$ 71GYHbPsX86Id4+boTxujR5bC0dO6rvbGRDooG4p/fE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * members module - buying controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Cube\Paginator,
    Cube\Crypt,
    Cube\Controller\Front,
    Cube\Http\Download,
    Ppb\Service;

class Buying extends AbstractAction
{

    /**
     *
     * bids service
     *
     * @var \Ppb\Service\Bids
     */
    protected $_bids;

    public function init()
    {
        $this->_bids = new Service\Bids();
    }

    public function Bids()
    {
        $keywords = $this->getRequest()->getParam('keywords');
        $listingId = $this->getRequest()->getParam('listing_id');

        $table = $this->_bids->getTable();

        $where = array(
            "bids.user_id = '" . $this->_user['id'] . "'",
            "listings.active = '1'",
            "listings.closed = '0'",
        );

        if ($listingId) {
            $where[] = "listings.id = '" . intval($listingId) . "'";
        }

        if (!empty($keywords)) {
            $params = '%' . str_replace(' ', '%', $keywords) . '%';
            $where[] = "listings.name LIKE '" . $params . "'";
        }

        $statement = $table->getAdapter()
            ->query("SELECT bids.id
                    FROM(
                        SELECT *
                        FROM " . $table->getPrefix() . $table->getName() . "
                        ORDER BY id DESC
                    ) as bids
                    INNER JOIN " . $table->getPrefix() . "listings AS listings ON listings.id=bids.listing_id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY bids.listing_id, bids.user_id
                    ORDER BY bids.created_at DESC");

        $result = $statement->fetchAll();

        $paginator = new Paginator(
            new Paginator\Adapter\ArrayAdapter($result));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'keywords'    => $keywords,
            'listingId'   => $listingId,
            'paginator'   => $paginator,
            'bidsService' => $this->_bids,
            'messages'    => $this->_flashMessenger->getMessages(),
            'params'      => $this->getRequest()->getParams(),
        );
    }

    public function RetractBid()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Ppb\Db\Table\Row\Bid $bid */
        $bid = $this->_bids->findBy('id', $id);

        $translate = $this->getTranslate();

        $result = false;
        if (count($bid)) {
            $result = $bid->retract();
        }

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("Your bid #%s has been retracted successfully."),
                    $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the bid cannot be retracted."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('bids');
    }

    public function Download()
    {
        $this->_setNoLayout();
        $options = Front::getInstance()->getOption('session');

        $translate = $this->getTranslate();

        $crypt = new Crypt();
        $crypt->setKey($options['secret']);

        $key = str_replace(' ', '+', $_REQUEST['key']);

        $saleId = null;

        $array = explode(
            Service\Table\SalesListings::KEY_SEPARATOR, $crypt->decrypt($key));
        $listingMediaId = isset($array[0]) ? intval($array[0]) : null;
        $saleListingId = isset($array[1]) ? intval($array[1]) : null;

        if ($listingMediaId && $saleListingId) {
            $salesListingsService = new Service\Table\SalesListings();

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            $saleListing = $salesListingsService->findBy('id', $saleListingId);
            $saleId = $saleListing->getData('sale_id');

            if (count($saleListing) > 0) {
                $digitalDownload = $saleListing->getDigitalDownloads($listingMediaId);

                if ($digitalDownload !== false) {
                    if ($digitalDownload['active']) {

                        $saleListing->countDownload($listingMediaId);

                        $filePath = \Ppb\Utility::getPath('base') . DIRECTORY_SEPARATOR .
                                    $this->_settings['digital_downloads_folder'] . DIRECTORY_SEPARATOR .
                                    $digitalDownload['value'];

                        $download = new Download($filePath);
                        $download->send();

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => sprintf(
                                $translate->_("Error: the file %s does not exist."),
                                $digitalDownload['value']),
                            'class' => 'alert-danger',
                        ));

                    }
                }
            }
        }

        $this->_flashMessenger->setMessage(array(
            'msg'   => $this->_("Unable to download the requested file."),
            'class' => 'alert-danger',
        ));
        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array('type' => 'bought', 'sale_id' => $saleId));
    }
}

