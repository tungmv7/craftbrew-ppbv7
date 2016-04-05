<?php

/**
 *
 * PHP Pro Bid $Id$ yKJ075DbTklUST4D6hTiNDzfaUDS1GgEVVHEsvgEB9A=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * async controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\View,
    Cube\Db\Table\AbstractTable,
    Cube\Db\Expr,
    Ppb\Service;

class Async extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    public function init()
    {
        $this->_view = new View();
    }

    public function SelectCategory()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $option = $this->getRequest()->getParam('option');
        $storeId = $this->getRequest()->getParam('storeId');
        $categoriesDisplay = $this->getRequest()->getParam('categoriesDisplay');

        $refresh = false;
        $categoryName = null;
        $boxes = null;

        $categoriesService = new Service\Table\Relational\Categories();

        $translate = $this->getTranslate();

        if ($option != 'reset') {
            $categoriesSelect = $categoriesService->getTable()->select()
                ->order(array('parent_id ASC', 'order_id ASC', 'name ASC'));

            $categoriesTableColumns = array_values($categoriesService->getTable()->info(AbstractTable::COLS));

            if (in_array($categoriesDisplay, $categoriesTableColumns)) {
                $categoriesSelect->where("$categoriesDisplay = ?", 1);
            }
            else {
                $categoriesSelect->where('enable_auctions = ?', 1);
            }

            if ($storeId) {
                $categoriesSelect->where("user_id is null OR user_id = '{$storeId}'");

                $usersService = new Service\Users();
                $storeCategories = $usersService->findBy('id', $storeId)
                    ->getStoreSettings('store_categories');

                if ($storeCategories) {
                    $categoriesSelect->where("parent_id is not null OR id IN (" . implode(', ', $storeCategories) . ")")
                        ->order('parent_id ASC');
                }
            }
            else {
                $categoriesSelect->where('user_id is null');
            }

            $select = $categoriesService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('parent_id = ?', $id);

            $stmt = $select->query();

            $nbChildren = (integer)$stmt->fetchColumn('nb_rows');

            if ($option == 'change' || !$id || ($nbChildren > 0)) {
                $array = $categoriesService->getCategoriesSelectData($id, $categoriesSelect);

                foreach ((array)$array as $row) {
                    $boxes .= $this->_view->formElement('select', 'category_data', $row['selected'])
                        ->setMultiOptions($row['values'])
                        ->setAttributes(array(
                            'size'  => '10',
                            'class' => 'form-control input-medium category-selector'))
                        ->render();
                }
            }

            $breadcrumbs = $categoriesService->getBreadcrumbs($id);

            $categoryName = implode(' :: ', array_values($breadcrumbs));
        }

        if (empty($boxes)) {
            // check for refresh data
            // 1. if we have category specific custom fields
            $customFieldsService = new Service\CustomFields();

            $select = $customFieldsService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('type = ?', 'item')
                ->where('active = ?', 1)
                ->where("category_ids != ?", '');

            $stmt = $select->query();

            $nbCustomFields = (integer)$stmt->fetchColumn('nb_rows');

            if ($nbCustomFields > 0) {
                $refresh = true;
            }
            else {
                // 2. if we have category specific fees
                $select = $categoriesService->getTable()
                    ->select(array('nb_rows' => new Expr('count(*)')))
                    ->where('parent_id is null')
                    ->where('custom_fees = ?', 1);

                $stmt = $select->query();

                $nbCustomFeesCategories = (integer)$stmt->fetchColumn('nb_rows');

                if ($nbCustomFeesCategories > 0) {
                    $refresh = true;
                }
            }
        }

        $data = array(
            'category_id'   => $id,
            'category_name' => ($categoryName) ? $categoryName : $translate->_('Select Category'),
            'output'        => $boxes,
            'refresh'       => $refresh,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }

    public function SelectLocation()
    {
        // action body
        $id = $this->getRequest()->getParam('id');
        $name = $this->getRequest()->getParam('name', 'state');

        $locationsService = new Service\Table\Relational\Locations();
        $locations = $locationsService->getMultiOptions($id);

        $element = null;
        if (count($locations) > 0) {
            $element = $this->_view->formElement('select', $name)
                ->setMultiOptions($locations)
                ->setAttributes(array(
                    'class' => 'form-control input-medium',
                ))
                ->render();
        }
        else {
            $element = $this->_view->formElement('text', $name)
                ->setAttributes(array(
                    'class' => 'form-control input-medium',
                ))
                ->render();
        }

        $this->getResponse()->setHeader('Content-Type: text/plain');

        $this->_view->setContent($element);

        return $this->_view;
    }

    public function UpdateStockLevelsElement()
    {
        $params = $this->getRequest()->getParams();

        $categoriesFilter = array(0);

        $categoriesService = new Service\Table\Relational\Categories();

        if ($categoryId = $this->getRequest()->getParam('category_id')) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $categoriesService->getBreadcrumbs($categoryId)));
        }

        if ($addlCategoryId = $this->getRequest()->getParam('addl_category_id')) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $categoriesService->getBreadcrumbs($addlCategoryId)));
        }

        $customFieldsService = new Service\CustomFields();
        $customFields = $customFieldsService->getFields(
            array(
                'type'         => 'item',
                'active'       => 1,
                'category_ids' => $categoriesFilter,
            ))->toArray();

        $isProductAttributes = false;
        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = array($customField['type'], 'product_edit');
            $customFields[$key]['id'] = 'custom_field_' . $customField['id'];
            $customFields[$key]['subform'] = 'details';

            if (!empty($customField['multiOptions'])) {
                $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                $customFields[$key]['bulk']['multiOptions'] = (!empty($multiOptions['key'])) ?
                    array_flip(array_filter($multiOptions['key'])) : array();
            }

            if ($customField['product_attribute']) {
                $isProductAttributes = true;
                $customFields[$key]['attributes'] = array('class' => 'product-attribute');
            }
        }

        $element = null;

        if ($isProductAttributes) {
            $element = $this->_view->formElement('\\Ppb\\Form\\Element\\StockLevels', 'stock_levels')
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ))
                ->setCustomFields($customFields)
                ->setFormData($params)
                ->setValue($this->getRequest()->getParam('stock_levels'))
                ->setRequired($isProductAttributes)
                ->render();
        }

        $this->getResponse()->setHeader('Content-Type: text/plain');

        $this->_view->setContent($element);

        return $this->_view;
    }

    public function checkDirectPaymentMethod()
    {
        $userId = $this->getRequest()->getParam('userId');
        $gatewayId = $this->getRequest()->getParam('gatewayId');

        $active = false;

        $paymentGatewaysService = new Service\Table\PaymentGateways();
        $paymentGateway = $paymentGatewaysService->getData($userId, $gatewayId, true, true);

        if (count($paymentGateway) > 0) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $paymentGateway['name'];

            if (class_exists($className)) {
                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className($userId);
                $active = $gatewayModel->enabled();
            }
        }

        $data = array(
            'active' => $active,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }
}

