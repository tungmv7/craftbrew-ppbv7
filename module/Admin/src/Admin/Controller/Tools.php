<?php

/**
 *
 * PHP Pro Bid $Id$ 3kfJRfL20v3Ok6BEOOA/Uu+2Lq/i1sydIgtow6k20YM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Db\Expr,
    Admin\Form,
    Cube\Paginator,
    Ppb\Service;

class Tools extends AbstractAction
{

    /**
     *
     * newsletters service
     *
     * @var \Ppb\Service\Newsletters
     */
    protected $_newsletters;

    /**
     *
     * form fields that are to be skipped when saving data
     *
     * @var array
     */
    protected $_skipFields = array('name', 'csrf', 'submit');

    public function init()
    {
        $this->_newsletters = new Service\Newsletters();
    }

    public function ShippingCarriers()
    {
        $form = new Form\ShippingCarriers();
        $service = new Service\Table\ShippingCarriers();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData(
                array(
                    'csrf' => $this->getRequest()->getParam('csrf')));

            if ($form->isValid() === true) {

                foreach ($this->_skipFields as $key) {
                    if (array_key_exists($key, $params)) {
                        unset($params[$key]);
                    }
                }

                $service->save($params);


                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved.'),
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
        $form->setData(
            $service->getData());

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }


    public function WordFilter()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->controller = 'Tools';

        $this->_forward('index', 'tables', null, array('table' => 'wordFilter'));
    }

    public function Accounting()
    {
        $params = null;

        $userId = $this->getRequest()->getParam('id');
        if ($userId) {
            $params = array(
                'user_id' => $userId,
            );
        }
        $this->_forward('history', 'account', 'members', $params);
    }


    public function Messaging()
    {
        if ($this->getRequest()->isPost() &&
            $this->getRequest()->getParam('option') == 'delete'
        ) {

            $id = $this->getRequest()->getParam('id');

            $ids = array_filter(
                array_values((array)$id));

            $messagingService = new Service\Messaging();
            $messagingService->delete($ids);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The selected messages have been removed'),
                'class' => 'alert-success',
            ));
        }

        $this->_forward('browse', 'messaging', 'members');
    }

    public function MessagingTopic()
    {
        $this->_forward('topic', 'messaging', 'members');
    }

    public function ViewInvoice()
    {
        $this->_forward('view-invoice', 'account', 'members');
    }

    public function Newsletters()
    {
        $title = $this->getRequest()->getParam('title');
        $send = $this->getRequest()->getParam('send');
        $id = $this->getRequest()->getParam('id');

        $recipients = $this->_newsletters->getRecipients();

        if (array_key_exists($send, $recipients)) {
            $result = $this->_newsletters->saveRecipients($send, $id);

            if ($result) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Recipients added successfully. The newsletter will now be sent to the selected users.'),
                    'class' => 'alert-success',
                ));
            }
        }

        $adapter = $this->_newsletters->getTable()->getAdapter();

        $recipientsCounter = $adapter->select()
            ->from(array('nl' => 'newsletters_recipients'), '')
            ->columns(array('total' => new Expr('count(*)')))
            ->where('nl.newsletter_id = n.id');

        $select = $adapter->select()
            ->from(array('n' => 'newsletters'))
            ->columns(array(
                'recipients' => new Expr('(' . $recipientsCounter . ')')
            ))
            ->order(array('n.created_at DESC'));

        if ($title !== null) {
            $params = '%' . str_replace(' ', '%', $title) . '%';
            $select->where('n.title LIKE ?', $params);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbSelect($select));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'title'      => $title,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'recipients' => $recipients,
        );
    }


    public function AddNewsletter()
    {
        $this->_forward('edit-newsletter');
    }

    public function EditNewsletter()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $data = $this->_newsletters->findBy('id', $id)->toArray();
        }

        $form = new \Admin\Form\Newsletter();

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {

                $this->_newsletters->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                            $this->_('The newsletter has been edited successfully') :
                            $this->_('The newsletter has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('newsletters');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteNewsletter()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_newsletters->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Newsletter ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The newsletter could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('newsletters', null, null, $this->getRequest()->getParams());
    }

    public function BlockedUsers()
    {
        $this->_forward('blocked-users', 'tools', 'members');
    }

    public function AddBlockedUser()
    {
        $this->_forward('add-blocked-user', 'tools', 'members');
    }

    public function EditBlockedUser()
    {
        $this->_forward('edit-blocked-user', 'tools', 'members');
    }

    public function DeleteBlockedUser()
    {
        $this->_forward('delete-blocked-user', 'tools', 'members');
    }

    public function Vouchers()
    {
        $this->_forward('vouchers', 'tools', 'members');
    }

    public function AddVoucher()
    {
        $this->_forward('add-voucher', 'tools', 'members');
    }

    public function EditVoucher()
    {
        $this->_forward('edit-voucher', 'tools', 'members');
    }

    public function DeleteVoucher()
    {
        $this->_forward('delete-voucher', 'tools', 'members');
    }


    public function LinkRedirects()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->controller = 'Tools';

        $this->_forward('index', 'tables', null, array('table' => 'linkRedirects'));
    }

    public function UsersStatistics()
    {
        $usersStatisticsService = new Service\UsersStatistics();

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect(
                $usersStatisticsService->getTable()->select(),
                $usersStatisticsService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator' => $paginator,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }
}