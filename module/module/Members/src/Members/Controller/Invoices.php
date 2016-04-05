<?php

/**
 *
 * PHP Pro Bid $Id$ dHgBr6HaUraIdeR0lY6/SA/XjjV4iBarrlf8lijKq0I=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * members module - invoices management controller
 * (buyer, seller, admin can access these actions)
 *
 * browse invoices
 * view invoices (&print)
 * edit invoices
 * combine purchases
 * update shipping & payment statuses
 * delete invoices
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Members\Form,
    Ppb\Service,
    Ppb\Db\Table\Row\Sale,
    Cube\Paginator,
    Cube\Controller\Front;

class Invoices extends AbstractAction
{

    /**
     *
     * sales service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * invoice types to display ('sold', 'bought')
     *
     * @var string
     */
    protected $_type;

    public function init()
    {
        $this->_sales = new Service\Sales();
        $this->_type  = $this->getRequest()->getParam('type', 'sold');
    }

    public function Browse()
    {
        // TODO: [later] add search by listing id and name in invoices
        $filter = $this->getRequest()->getParam('filter', 'all');
        $saleId = $this->getRequest()->getParam('sale_id');

        $table  = $this->_sales->getTable();
        $select = $table->select()
            ->where('pending = ?', 0)
            ->order(array('updated_at DESC', 'created_at DESC'));

        $inAdmin = $this->_loggedInAdmin();

        if ($inAdmin) {
            $this->_type = 'all';
            $controller  = 'Listings';
        } else {
            $controller = ($this->_type == 'sold') ? 'Selling' : 'Buying';
        }

        switch ($this->_type) {
            case 'all': // only the administrator can view all invoices
                break;
            case 'bought': // invoices of bought items
                $select->where('buyer_id = ?', $this->_user['id'])
                    ->where('buyer_deleted = ?', 0);
                break;
            default: // invoices of sold items
                $select->where('seller_id = ?', $this->_user['id'])
                    ->where('seller_deleted = ?', 0);
                break;
        }

        if ($saleId) {
            $select->where('id = ?', (int)$saleId);
        }

        switch ($filter) {
            case 'paid':
                $select->where('flag_payment > ?', 0);
                break;
            case 'unpaid':
                $select->where('flag_payment = ?', 0);
                break;
            case 'posted_sent':
                $select->where('flag_shipping = ?', Sale::SHIPPING_SENT);
                break;
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'filter'     => $filter,
            'saleId'     => $saleId,
            'controller' => $controller,
            'type'       => $this->_type,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'params'     => $this->getRequest()->getParams(),
            'inAdmin'    => $inAdmin,
        );
    }

    public function View()
    {
        $id = $this->getRequest()->getParam('id');

        $translate = $this->getTranslate();

        /* @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', $id);

        $display = false;
        if (count($sale) > 0) {
            if ($sale->canView()) {
                $display = true;
            }
        }

        if (!$display) {
            $this->_helper->redirector()->redirect('browse');
        }

        return array(
            'sale'     => $sale,
            'headline' => sprintf($translate->_('Sale Invoice - ID: #%s'), $id),
        );
    }

    public function Edit()
    {
        $redirectController = null;
        $form               = null;

        $translate = $this->getTranslate();

        $saleId = $this->getRequest()->getParam('sale_id');
        $option = $this->getRequest()->getParam('option');

        $service = new Service\Table\SalesListings();

        /* @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', $saleId);

        if ($sale->canEdit()) {
            $form = new Form\Invoices($service, $sale, $option);

            $canCombinePurchases = $sale->canCombinePurchases();

            $select = $this->_sales->getTable()->getAdapter()->select()
                ->from(array('s' => 'sales'), '*')
                ->join(array('l' => 'sales_listings'), 'l.sale_id = s.id',
                    'l.id, l.sale_id, l.listing_id, l.price, l.quantity')
                ->join(array('ls' => 'listings'), 'l.listing_id = ls.id', 'ls.name, ls.currency')
                ->order(array('l.created_at ASC'));

            if ($option == 'combine' && $canCombinePurchases) { // must match the currency, item location and pickup options
                $select->where('s.buyer_id = ?', $sale->getData('buyer_id'))
                    ->where('s.seller_id = ?', $sale->getData('seller_id'))
                    ->where('ls.currency = ?', $sale->getData('currency'))
                    ->where('ls.country = ?', $sale->getData('country'))
                    ->where('ls.state = ?', $sale->getData('state'))
                    ->where('ls.address = ?', $sale->getData('address'))
                    ->where('ls.apply_tax = ?', $sale->getData('apply_tax', 0))
                    ->where('ls.postage_settings REGEXP \'"' . $sale['pickup_options'] . '"\'');


            } else if ($saleId) {
                $select->where('s.id = ?', $saleId);
            }

            $select->where('s.active = ?', 1)
                ->where('s.flag_payment = ?', Sale::PAYMENT_UNPAID);

            if ($sale['buyer_id'] == $this->_user['id']) {
                $select->where('s.buyer_id = ?', $this->_user['id'])
                    ->where('s.edit_locked = ?', 0)
                    ->where('s.buyer_deleted = ?', 0);
                $redirectController = 'buying';
            } else if ($sale['seller_id'] == $this->_user['id']) {
                $select->where('s.seller_id = ?', $this->_user['id'])
                    ->where('s.seller_deleted = ?', 0);
                $redirectController = 'selling';
            } else {
                $form->clearElements();
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Only the seller or the buyer can edit / combine invoices'),
                    'class' => 'alert-danger',
                ));
            }

            $form->setData($sale->getData())
                ->setData(
                    $service->fetchAll($select)->toArray());


            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();

                $form->setData($params, true)
                    ->preparePostageAmountField($this->getRequest()->getParam('postage_amount'));
            }

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {
                    $isSeller = $sale->isSeller();

                    $data = array(
                        'id'                  => $sale['id'],
                        'buyer_id'            => $sale['buyer_id'],
                        'seller_id'           => $sale['seller_id'],
                        'shipping_address_id' => $this->getRequest()->getParam('shipping_address_id'),
                        'postage_id'          => (int)$this->getRequest()->getParam('postage_id'),
                        'apply_insurance'     => (bool)$this->getRequest()->getParam('apply_insurance'),
                        'edit_locked'         => ($isSeller) ? 1 : 0,
                        'listings'            => $this->_flipArray($params),
                    );

                    if ($isSeller) {
                        $data['postage_amount']   = $params['postage_amount'];
                        $data['insurance_amount'] = $params['insurance_amount'];
                        $data['tax_rate']         = (!empty($params['tax_rate'])) ? $params['tax_rate'] : null;
                    }

                    $this->_sales->save($data);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The invoice has been saved successfully.'),
                        'class' => 'alert-success',
                    ));

                    if ($redirectController !== null) {
                        $this->_helper->redirector()->redirect('browse', 'invoices', null,
                            array(
                                'type'    => ($isSeller) ? 'sold' : 'bought',
                                'sale_id' => $saleId));
                    } else {
                        $form->clearElements();
                    }
                } else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        } else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("The invoice doesn't exist or it cannot be edited."),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'   => ($option == 'combine' && $canCombinePurchases) ?
                    $this->_('Combine Purchases') :
                    sprintf(
                        $translate->_('Edit Invoice - ID: #%s'),
                        $saleId),
            'controller' => ucfirst($redirectController),
            'form'       => $form,
            'sale'       => $this->_prepareSaleFromPostData($sale),
            'option'     => $option,
            'messages'   => $this->_flashMessenger->getMessages()
        );
    }

    public function UpdateStatus()
    {
        $form = null;
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setNoLayout();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->clearHeaderCode()
            ->clearBodyCode();

        $translate = $this->getTranslate();

        $saleId = $this->getRequest()->getParam('sale_id');

        /* @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', $saleId);

        if ($sale->isActive() && $sale->isSeller()) {
            $form = new Form\UpdateStatus();

            $form->setData($sale->getData() + array('type' => $this->getRequest()->getParam('type')));

            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();

                $form->setData($params);

                if ($form->isValid() === true) {
                    $sale->updateStatus($params);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_('Invoice #%s has been updated successfully.'), $sale['id']),
                        'class' => 'alert-success',
                    ));

                    $form->clearElements();
                } else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        } else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("You cannot update this invoice."),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Update Status'),
            'form'            => $form,
            'messages'        => $this->_flashMessenger->getMessages()
        );
    }

    public function Delete()
    {

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale   = $this->_sales->findBy('id', (int)$this->getRequest()->getParam('sale_id'));
        $result = $sale->delete();

        $translate = $this->getTranslate();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("The sale invoice #%s has been deleted."), $sale['id']),
                'class' => 'alert-success',
            ));
        } else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the sale invoice cannot be deleted."),
                'class' => 'alert-danger',
            ));
        }
//
        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array(
                'type'    => $this->getRequest()->getParam('type'),
                'sale_id' => null));
    }

    public function UpdateDownloadLinks()
    {
        $salesListingsService = new Service\Table\SalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        $saleListing = $salesListingsService->findBy('id', $this->getRequest()->getParam('sale_listing_id'));

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');

        if ($sale->isSeller()) {
            $saleListing->save(array(
                'downloads_active' => !$saleListing->getData('downloads_active')
            ));

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The statuses of the download links have been updated.'),
                'class' => 'alert-success',
            ));
        }

        $saleId = null;
        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array(
                'type'    => 'sold',
                'sale_id' => null));
    }

    /**
     *
     * flip array to save the edit/combine invoices form
     * will only take array values into consideration and skip form elements that dont contain multiple values
     *
     * @param array $array
     *
     * @return array
     */
    protected function _flipArray(array $array)
    {
        $output = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $output[$k][$key] = $v;
                }
            }
        }

        return $output;
    }

    protected function _prepareSaleFromPostData(Sale $sale)
    {
        $params = $this->getRequest()->getParams();
        $array  = $this->_flipArray($params);

        $sale->setSalesListings($array);

        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $sale->{$key} = $value;
            }
        }

        return $sale;
    }

}
