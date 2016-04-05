<?php

/**
 *
 * PHP Pro Bid $Id$ z0ojKERmqaMw5qtG7Kes3tUPPFX++KgxZfjTvEYT+oQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * members module - messaging controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Paginator,
    Ppb\Service,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel,
    Members\Form;

class Messaging extends AbstractAction
{

    /**
     *
     * messaging table service
     *
     * @var \Ppb\Service\Messaging
     */
    protected $_messaging;

    public function init()
    {
        $this->_messaging = new Service\Messaging();
    }


    public function Browse()
    {
        $inAdmin = $this->_loggedInAdmin();

        $filter = $this->getRequest()->getParam('filter', 'received');
        $archived = $this->getRequest()->getParam('archived', 0);
        $summary = $this->getRequest()->getParam('summary');
        $keywords = $this->getRequest()->getParam('keywords');

        $table = $this->_messaging->getTable();
        $select = $table->select()
            ->order('created_at DESC');

        if (!$inAdmin) {
            $filter = ($filter == 'all') ? null : $filter;
        }

        if ($this->getRequest()->isPost() &&
            $this->getRequest()->getParam('option') == 'archive'
        ) {
            $id = $this->getRequest()->getParam('id');

            $ids = array_filter(
                array_values((array)$id));

            $messagingService = new Service\Messaging();
            $messagingService->archive($ids, $this->_user['id'], $filter);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The selected messages have been archived'),
                'class' => 'alert-success',
            ));
        }

        if (!empty($keywords)) {
            $params = '%' . str_replace(' ', '%', $keywords) . '%';

            $select->where("title LIKE '{$params}' OR content LIKE '{$params}' OR topic_title LIKE '{$params}'");
        }

        switch ($filter) {
            case 'all':
                break;
            case 'sent':
                $select->where('sender_id = ?', $this->_user['id'])
                    ->where('sender_deleted = ?', (int)$archived);
                break;
            default: // received
                $select->where('receiver_id = ?', $this->_user['id'])
                    ->where('receiver_deleted = ?', (int)$archived);
                break;
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($pageNumber)
            ->setItemCountPerPage(10);

        $array = array(
            'controller' => ($inAdmin) ? 'Tools' : (($summary) ? 'Members Area' : 'Messages'),
            'paginator'  => $paginator,
            'filter'     => $filter,
            'keywords'   => $keywords,
            'archived'   => $archived,
            'summary'    => $summary,
            'inAdmin'    => $inAdmin,
        );

        if (!$summary) {
            $array['messages'] = $this->_flashMessenger->getMessages();
        }

        return $array;
    }

    public function Create()
    {
        $messageId = null;

        $params = $this->getRequest()->getParams();
        $params['sender_id'] = $this->_user['id'];

        $form = new Form\Message();
        $form->setData($params);

        $inAdmin = $this->_loggedInAdmin();
        $blockedUser = null;

        if (!$inAdmin) {
            $receiverId = null;
            if (!empty($params['sale_id'])) {
                $salesService = new Service\Sales();
                $sale = $salesService->findBy('id', (int)$params['sale_id']);

                $receiverId = ($params['sender_id'] == $sale['buyer_id']) ? $sale['seller_id'] : $sale['buyer_id'];
            }
            else if (!empty($params['receiver_id'])) {
                $receiverId = $params['receiver_id'];
            }

            $blockedUsersService = new Service\BlockedUsers();
            $blockedUser = $blockedUsersService->check(
                BlockedUserModel::ACTION_MESSAGING,
                array(
                    'ip'       => $_SERVER['REMOTE_ADDR'],
                    'username' => $this->_user['username'],
                    'email'    => $this->_user['email'],
                ), $receiverId);
        }

        if (!empty($params['topic_type'])) {
            $form->setTitle($this->_messaging->generateTopicTitle($params));
        }
        else {
            $form->setTitle('Post Message');
        }

        if ($blockedUser !== null) {
            $form->clearElements();
        }
        else {
            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {
                    $messageId = $this->_messaging->save($form->getData());

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The message has been posted successfully.'),
                        'class' => 'alert-success',
                    ));

                    $mail = new \Members\Model\Mail\User();
                    $mail->messageReceived($messageId)->send();

                    if ($this->_settings['bcc_emails']) {
                        $subject = $mail->getMail()->getSubject();
                        $subject = '[BCC] ' . $subject;
                        $mail->getMail()
                            ->setSubject($subject)
                            ->setTo($this->_settings['admin_email'])
                            ->send();
                    }
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }

                $view = Front::getInstance()->getBootstrap()->getResource('view');

                switch ($form->getData('topic_type')) {
                    case Service\Messaging::SALE_TRANSACTION:
                        $params = array(
                            'module'     => 'members',
                            'controller' => 'messaging',
                            'action'     => 'topic'
                        );
                        if ($messageId) {
                            $params['id'] = $this->_messaging->findBy('id', $messageId)->getData('topic_id');
                        }

                        $redirectUrl = $view->url($params);
                        break;
                    case Service\Messaging::ABUSE_REPORT_LISTING:
                    case Service\Messaging::ABUSE_REPORT_USER:
                    case Service\Messaging::REFUND_REQUEST:
                        $redirectUrl = $view->url(array('module' => 'members', 'controller' => 'messaging', 'action' => 'browse', 'filter' => 'sent'));

                        break;
                    case Service\Messaging::PRIVATE_QUESTION:
                    case Service\Messaging::PUBLIC_QUESTION:
                        $params = array(
                            'module'     => 'listings',
                            'controller' => 'listing',
                            'action'     => 'details',
                            'id'         => $this->getRequest()->getParam('listing_id'),
                        );

                        $redirectUrl = $view->url($params);
                        break;
                    default:
                        $params = array(
                            'module'     => 'members',
                            'controller' => 'messaging',
                            'action'     => 'topic',
                            'id'         => $this->getRequest()->getParam('id'),
                        );

                        $redirectUrl = $view->url($params);
                        break;
                }


                if (!empty($redirectUrl)) {
                    $this->_helper->redirector()->gotoUrl($redirectUrl);
                }
            }
        }

        return array(
            'form'        => $form,
            'blockedUser' => $blockedUser,
        );
    }

    public function Topic()
    {
        $inAdmin = $this->_loggedInAdmin();

        $sale = null;
        $messageId = $this->getRequest()->getParam('id', 0);

        /** @var \Ppb\Db\Table\Row\Message $message */
        $message = $this->_messaging
            ->fetchAll(
                $this->_messaging->getTable()->select()
                    ->where('id = ?', $messageId)
                    ->where("sender_id = '{$this->_user['id']}' OR receiver_id = '{$this->_user['id']}'")
            )->getRow(0);

        if (!$message) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The selected topic doesnt exist or you are not allowed to view it.'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('browse');
        }

        if (!empty($message['sale_id'])) {
            $sale = $message->findParentRow('\Ppb\Db\Table\Sales');
        }

        if (isset($message['topic_id'])) {
            $this->_messaging->getTable()
                ->update(array(
                    'flag_read' => 1,
                ), "topic_id = '{$message['topic_id']}' AND receiver_id='{$this->_user['id']}'");
        }

        return array(
            'headline'   => $message->getTopicTitle(),
            'controller' => ($inAdmin) ? 'Tools' : 'Messages',
            'messages'   => $this->_flashMessenger->getMessages(),
            'message'    => $message,
            'sale'       => $sale,
        );
    }
}

