<?php

/**
 *
 * PHP Pro Bid $Id$ z0ojKERmqaMw5qtG7Kes3tUPPFX++KgxZfjTvEYT+oQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * messaging table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Messaging as MessagingTable,
    Cube\Db\Expr;

class Messaging extends AbstractService
{

    /**
     * messaging topics types
     */
    const PUBLIC_QUESTION = 'public_question';
    const PRIVATE_QUESTION = 'private_question';
    const SALE_TRANSACTION = 'sale_transaction';
    const ADMIN_MESSAGE = 'admin_message';
    const ABUSE_REPORT_USER = 'abuse_report_user';
    const ABUSE_REPORT_LISTING = 'abuse_report_listing';
    const REFUND_REQUEST = 'refund_request';

    const TITLE_RE = 'Re:';
    const TITLE_FWD = 'Fwd:';

    /**
     *
     * allowed topics types and their automatic titles
     *
     * @var array
     */
    protected $_topicTypes = array(
        self::PUBLIC_QUESTION      => array(
            'msg'  => 'Public Question - Listing ID: #%s',
            'args' => array('listing_id'),
        ),
        self::PRIVATE_QUESTION     => array(
            'msg'  => 'Private Message - Listing ID: #%s',
            'args' => array('listing_id'),
        ),
        self::SALE_TRANSACTION     => array(
            'msg'  => 'Sale Transaction - Invoice ID: #%s',
            'args' => array('sale_id'),
        ),
        self::ADMIN_MESSAGE        => array(
            'msg'  => 'Message from Site Admin',
            'args' => array(),
        ),
        self::ABUSE_REPORT_USER    => array(
            'msg'  => 'Abuse Report - User: %s',
            'args' => array('username'),
        ),
        self::ABUSE_REPORT_LISTING => array(
            'msg'  => 'Abuse Report - Listing ID: %s',
            'args' => array('listing_id'),
        ),
        self::REFUND_REQUEST       => array(
            'msg'  => 'Refund Request - Invoice ID: #%s',
            'args' => array('sale_id'),
        ),
    );

    protected $_replyPrefixes = array(
        self::TITLE_RE,
        self::TITLE_FWD,
    );

    /**
     *
     * topic type
     *
     * @var string
     */
    protected $_topicType = null;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new MessagingTable());
    }

    /**
     *
     * set topic types
     *
     * @param array $topicTypes
     *
     * @return $this
     */
    public function setTopicTypes($topicTypes)
    {
        $this->_topicTypes = $topicTypes;

        return $this;
    }

    /**
     *
     * get topic types
     *
     * @return array
     */
    public function getTopicTypes()
    {
        return $this->_topicTypes;
    }

    /**
     *
     * set topic type
     *
     * @param string $topicType
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTopicType($topicType)
    {
        if (!array_key_exists($topicType, $this->_topicTypes)) {
            throw new \InvalidArgumentException(
                sprintf("The messaging topic type '%s' is not allowed.", $topicType));
        }

        $this->_topicType = $topicType;

        return $this;
    }

    /**
     *
     * get topic type
     *
     * @return string
     */
    public function getTopicType()
    {
        return $this->_topicType;
    }

    /**
     *
     * create or update a row in the messaging table
     * if topic_id is null, update data and topic_id = id
     *
     * if receiver is not set, get one admin user (the one that is set to receive messages from users (TODO))
     *
     * @param array $post
     *
     * @return int  the id of the created/edited message
     */
    public function save($post)
    {
        $row = null;

        $data = $this->_prepareSaveData($post);

        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }

        if (!array_key_exists('receiver_id', $data)) {
            $usersService = new Users();
            $adminUser = $usersService->findBy('role', 'admin');
            $data['receiver_id'] = $adminUser['id'];
        }

        $data['created_at'] = new Expr('now()');

        $id = $this->_table->insert($data);

        if (!isset($data['topic_id'])) {
            $row = $this->findBy('id', $id);
            $row->save(array(
                'topic_id' => $id,
            ));
        }

        if (!empty($post['sale_id'])) {
            $salesService = new Sales();
            $sale = $salesService->findBy('id', (int)$post['sale_id']);
            if (!$sale['messaging_topic_id']) {
                $sale->save(array(
                    'messaging_topic_id' => $id,
                ));
            }
        }

        return $id;
    }

    public function archive($id, $userId, $filter)
    {
        $updateColumn = ($filter == 'sent') ? 'sender_deleted' : 'receiver_deleted';
        $userColumn = ($filter == 'sent') ? 'sender_id' : 'receiver_id';

        $adapter = $this->_table->getAdapter();
        $where = array(
            $adapter->quoteInto('id IN (?)', $id),
            $adapter->quoteInto("{$userColumn} = ?", $userId)
        );

        $this->_table->update(array($updateColumn => 1), $where);
    }

    /**
     *
     * messages can only be deleted by the administrator, and only from the admin area
     *
     * @param int|array $id sale id
     *
     * @return int the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id IN (?)', $id));
    }

    /**
     *
     * prepare save data
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        if (empty($data['topic_id'])) {
            if (!empty($data['sale_id'])) {
                $salesService = new Sales();
                $sale = $salesService->findBy('id', (int)$data['sale_id']);

                $data['receiver_id'] = ($data['sender_id'] == $sale['buyer_id']) ? $sale['seller_id'] : $sale['buyer_id'];
            }

            $data['topic_title'] = $this->generateTopicTitle($data);
        }

        $data['listing_id'] = (isset($data['public_question'])) ?
            (($data['public_question']) ? $data['listing_id'] : null) : null;


        $data = parent::_prepareSaveData($data);

        return array_filter($data, function ($value) {
            return trim($value) != null;
        });
    }


    /**
     *
     * generate the topic title of a messaging topic
     * TODO: review this code for private questions when public questions are disabled
     *
     * @param array $data
     *
     * @return string|null
     */
    public function generateTopicTitle(array $data)
    {
        if (!empty($data['topic_type'])) {
            $this->setTopicType($data['topic_type']);
        }
        else {
            $topicType = $this->getTopicType();
            if (!isset($topicType)) {
                $publicQuestion = (isset($data['public_question'])) ? $data['public_question'] : null;
                $this->setTopicType(
                    $publicQuestion ? self::PUBLIC_QUESTION : self::PRIVATE_QUESTION);
            }
        }


        if ($topicType = $this->getTopicType() != null) {
            $topicType = $this->_topicTypes[$this->getTopicType()];

            $args = array();
            foreach ($topicType['args'] as $arg) {
                $args[] = isset($data[$arg]) ? $data[$arg] : null;
            }

            $translate = $this->getTranslate();

            return vsprintf($translate->_($topicType['msg']), $args);
        }

        return null;
    }


    /**
     *
     * generate topic reply title
     *
     * @param $topicId
     *
     * @return null|string
     */
    public function generateMessageReplyTitle($topicId)
    {
        $topic = $this->findBy('id', $topicId);

        if ($topic) {
            $translate = $this->getTranslate();

            $messageTitle = $topic['title'];

            foreach ($this->_replyPrefixes as $prefix) {
                $messageTitle = str_ireplace($translate->_($prefix), '', $messageTitle);
            }

            return $translate->_(self::TITLE_RE) . ' ' . trim($messageTitle);
        }

        return null;
    }
}

