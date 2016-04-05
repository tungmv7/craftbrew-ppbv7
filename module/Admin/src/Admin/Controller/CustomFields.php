<?php

/**
 *
 * PHP Pro Bid $Id$ DiFLsX6dxxi9VqyWgpy/iUrUj1z7h5qRiwsqEth5maQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service\CustomFields as CustomFieldsService,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    Cube\Paginator;

class CustomFields extends AbstractAction
{

    /**
     *
     * custom fields service
     *
     * @var \Ppb\Service\CustomFields
     */
    protected $_customFields;

    public function init()
    {
        $this->_customFields = new CustomFieldsService();
    }

    public function Browse()
    {
        $type = $this->getRequest()->getParam('type');
        $label = $this->getRequest()->getParam('label');
        $id = $this->getRequest()->getParam('id');

        if ($this->getRequest()->isPost() && count($id) > 0) {
            $this->_customFields->saveBrowseSettings(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The custom fields settings have been updated.'),
                'class' => 'alert-success',
            ));
        }

        $customFieldTypes = $this->_customFields->getCustomFieldTypes();
        if (!in_array($type, $customFieldTypes)) {
            $type = $customFieldTypes[0];
        }

        $select = $this->_customFields->getTable()
            ->select()
            ->where('type = ?', $type);

        if ($label !== null) {
            $params = '%' . str_replace(' ', '%', $label) . '%';
            $select->where('label LIKE ?', $params);
        }

        $select->order(array('active DESC', 'order_id ASC'));

        $paginator = new Paginator(
            new Paginator\Adapter\DbSelect($select));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        $categoriesService = new CategoriesService();

        return array(
            'paginator'         => $paginator,
            'messages'          => $this->_flashMessenger->getMessages(),
            'type'              => $type,
            'label'             => $label,
            'categoriesService' => $categoriesService,
            'controller'        => 'Custom Fields'
        );
    }

    public function Add()
    {
        $this->_forward('edit');
    }

    public function Edit()
    {
        $id = $this->getRequest()->getParam('id');

        $data = array();
        $type = null;

        if ($id) {
            $data = $this->_customFields->findBy('id', $id)->toArray();
            if (isset($data['type'])) {
                $type = $data['type'];
            }
        }
        else {
            $type = $this->getRequest()->getParam('type');
        }

        $form = new \Admin\Form\CustomField($type);

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {

                $this->_customFields->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The custom field has been edited successfully') :
                        $this->_('The custom field has been created successfully.'),
                    'class' => 'alert-success',
                ));


                $this->_helper->redirector()->redirect('browse', null, null, array('type' => $type));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Custom Fields',
        );
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_customFields->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Custom Field ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The custom field could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null, $this->getRequest()->getParams());
    }

}