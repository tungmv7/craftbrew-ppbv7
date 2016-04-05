<?php

/**
 *
 * PHP Pro Bid $Id$ sU4nEvQMmyXOZJvru2b2wMl9lhIk4RKXaMOHXIbNVC8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * members module - reputation management controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service\Reputation as ReputationService,
    Members\Form\ReputationPost,
    Cube\Controller\Front,
    Cube\Paginator,
    Cube\Db\Expr;

class Reputation extends AbstractAction
{

    /**
     *
     * reputation service
     *
     * @var \Ppb\Service\Reputation
     */
    protected $_reputation;

    public function init()
    {
        $this->_reputation = new ReputationService();

    }

    public function Browse()
    {

        $inAdmin = $this->_loggedInAdmin();

        $filter = $this->getRequest()->getParam('filter');
        $controller = ($inAdmin) ? 'Users' : 'Feedback';

        $table = $this->_reputation->getTable();
        $select = $table->getAdapter()
            ->select()
            ->from(array('r' => 'reputation'));

        $userId = $this->getRequest()->getParam('userId');
        $username = $this->getRequest()->getParam('username');
        $listingId = $this->getRequest()->getParam('listingId');

        $user = array();

        if ($username === null && !$inAdmin) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');
        }
        else if ($userId !== null) {
            $user = $this->_users->findBy('id', $userId);
        }
        else if ($username !== null) {
            $user = $this->_users->findBy('username', $username);
        }

        if (!$inAdmin) {
            switch ($filter) {
                case 'pending':
                    $select->where('r.poster_id = ?', $user['id'])
                        ->where('r.posted = ?', 0)
                        ->order('r.created_at DESC');
                    break;
                case 'left':
                    $select->where('r.poster_id = ?', $user['id'])
                        ->where('r.posted = ?', 1)
                        ->order('r.updated_at DESC');

                    break;
                case 'from_buyers':
                    $select->where('r.user_id = ?', $user['id'])
                        ->where('r.posted = ?', 1)
                        ->where('r.reputation_type = ?', 'sale')
                        ->order('r.updated_at DESC');
                    break;

                case 'from_sellers':
                    $select->where('r.user_id = ?', $user['id'])
                        ->where('r.posted = ?', 1)
                        ->where('r.reputation_type = ?', 'purchase')
                        ->order('r.updated_at DESC');
                    break;
                default:
                    $select->where('r.user_id = ?', $user['id'])
                        ->where('r.posted = ?', 1)
                        ->order('r.updated_at DESC');
                    break;
            }
        }
        else {
            if ($listingId) {
                $select->join(array('sl' => 'sales_listings'), 'sl.id = r.sale_listing_id')
                    ->where('sl.listing_id = ?', $listingId);
            }

            if (isset($user['id'])) {
                $select->where("r.user_id = '{$user['id']}' OR r.poster_id = '{$user['id']}'");
            }

            $select->where('r.posted = ?', 1)
                ->order('r.updated_at DESC');
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($pageNumber)
            ->setItemCountPerPage(10);


        return array(
            'controller' => $controller,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'inAdmin'    => $inAdmin,
            'filter'     => $filter,
            'username'   => $username,
            'listingId'  => $listingId,
        );
    }

    public function Post()
    {
        $form = new ReputationPost();

        $ids = array_filter(
            (array)$this->getRequest()->getParam('id'));

        if (empty($ids)) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Please select at least one transaction you want to leave feedback for.'),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('browse', null, null, array('filter' => 'pending'));
        }

        $params = $this->getRequest()->getParams();
        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $params = $form->getData();
                $this->_reputation->postReputation($ids, $params['score'], $params['comments'], $this->_user['id']);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The feedback has been posted successfully.'),
                    'class' => 'alert-success',
                ));
                $this->_helper->redirector()->redirect('browse', null, null, array('filter' => 'left'));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        $select = $this->_reputation->getTable()->select()
            ->where('posted = ?', 0)
            ->where("poster_id = ? OR user_id = ?", $this->_user['id'])
            ->where('id IN (?)', new Expr(implode(', ', $ids)));
        $rowset = $this->_reputation->fetchAll($select);


        return array(
            'controller' => 'Feedback',
            'messages'   => $this->_flashMessenger->getMessages(),
            'rowset'     => $rowset,
            'form'       => $form,
        );
    }

    public function Details()
    {
        $username = $this->getRequest()->getParam('username');
        $column = (is_numeric($username)) ? 'id' : 'username';

        $translate = $this->getTranslate();

        $user = $this->_users->findBy($column, $username);

        if (!count($user)) {
            $this->_helper->redirector()->notFound();
        }

        $this->_user['id'] = $user->getData('id');

        $tabs = array(
            ''             => $translate->_('All Ratings'),
            'from_buyers'  => $translate->_('From Buyers'),
            'from_sellers' => $translate->_('From Sellers'),
            'left'         => $translate->_('Left For Others'),
        );

        return array(
            'headline'        => sprintf($translate->_('Feedback Details: "%s"'), $user->getData('username')),
            'filter'          => $this->getRequest()->getParam('filter'),
            'tabs'            => $tabs,
            'user'            => $user,
            'isMembersModule' => false,
        );
    }

}

