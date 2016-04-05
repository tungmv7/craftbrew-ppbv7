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

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service\Listings as ListingsService,
    Ppb\Service\Sales as SalesService,
    Cube\Paginator;

class Listings extends AbstractAction
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
        $this->_listings = new ListingsService();
    }

    public function Browse()
    {
        $select = $this->_listings->select(ListingsService::SELECT_ADMIN);

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getParam('id');
            $option = $this->getRequest()->getParam('option');

            $ids = array_filter(
                array_values((array)$id));

            $counter = null;
            $messages = array();

            if (count($ids) > 0) {
                $where = $this->_listings->getTable()->getAdapter()->quoteInto("id IN (?)", $ids);
                $listings = $this->_listings->fetchAll($where);
                $messages = $listings->changeStatus($option, true);

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

            if (count($messages) > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $messages,
                    'class' => 'alert-info',
                ));
            }
        }

        $itemsPerPage = $this->getRequest()->getParam('limit', 20);
        $itemsPerPage = ($itemsPerPage > 80) ? 80 : $itemsPerPage;

        $pageNumber = $this->getRequest()->getParam('page');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_listings->getTable()));
        $paginator->setPageRange(5)
            ->setItemCountPerPage($itemsPerPage)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator'    => $paginator,
            'messages'     => $this->_flashMessenger->getMessages(),
            'type'         => $this->getRequest()->getParam('type'),
            'filter'       => $this->getRequest()->getParam('filter'),
            'keywords'     => $this->getRequest()->getParam('keywords'),
            'params'       => $this->getRequest()->getParams(),
            'itemsPerPage' => $itemsPerPage,
            'listingId'    => $this->getRequest()->getParam('listing_id'),
        );
    }

    public function Edit()
    {
        $redirect = false;

        $id = $this->getRequest()->getParam('id');

        $data = $this->_listings->findBy('id', $id, false, true)->toArray();
        $userId = (isset($data['user_id'])) ? $data['user_id'] : null;
        $params = $this->getRequest()->getParams();

        $form = new \Listings\Form\Listing('item', null, $userId);

        if ($data !== null) {
            $form->setData($data)
                ->generateEditForm($id);

            if ($this->getRequest()->isPost()) {
                $form->setData($params);
            }

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {

                    $this->_listings->save($params);

                    $redirect = true;

                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_("Listing ID: #%s has been edited successfully."), $id),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The listing you are trying to edit does not exist.'),
                'class' => 'alert-danger',
            ));

            $redirect = true;
        }

        if ($redirect) {
            $this->_helper->redirector()->redirect('browse');
        }

        return array(
            'form'        => $form,
            'messages'    => $this->_flashMessenger->getMessages(),
            'currentStep' => null,
        );
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_listings->findBy('id', (int)$id)->delete(true);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The listing could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null, $this->getRequest()->getParams());
    }

    public function Sales()
    {
        if ($this->getRequest()->getParam('option') == 'delete') {
            $salesService = new SalesService();

            $saleId = (int)$this->getRequest()->getParam('sale_id');
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $salesService->findBy('id', $saleId);
            $result = $sale->delete(true);

            if ($result) {
                $translate = $this->getTranslate();

                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($translate->_("The sale invoice #%s has been deleted."), $saleId),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_("Error: the sale invoice cannot be deleted."),
                    'class' => 'alert-danger',
                ));
            }

            $this->getRequest()->clearParam('sale_id');
        }

        $this->_forward('browse', 'invoices', 'members');
    }

}