<?php

/**
 *
 * PHP Pro Bid $Id$ sU4nEvQMmyXOZJvru2b2wMl9lhIk4RKXaMOHXIbNVC8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * reputation (feedback) table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Reputation as ReputationTable,
    Cube\Db\Expr;

class Reputation extends AbstractService
{

    const SALE = 'sale';
    const PURCHASE = 'purchase';

    /**
     * positive rating threshold
     * any rating that is above this value will be considered positive
     */
    const POSITIVE_THRESHOLD = 3;

    /**
     * reputation intervals
     */
    const INTERVAL_ONE_MONTH = 'INTERVAL 1 MONTH';
    const INTERVAL_SIX_MONTHS = 'INTERVAL 6 MONTH';
    const INTERVAL_TWELVE_MONTHS = 'INTERVAL 1 YEAR';

    /**
     *
     * allowed reputation scores
     *
     * @var array
     */
    public static $scores = array(
        5 => 'Positive',
        3 => 'Neutral',
        1 => 'Negative',
    );

    /**
     *
     * reputation intervals (used by the reputation details page)
     *
     * @var array
     */
    public static $intervals = array(
        self::INTERVAL_ONE_MONTH     => 'Last Month',
        self::INTERVAL_SIX_MONTHS    => 'Last 6 Months',
        self::INTERVAL_TWELVE_MONTHS => 'Last 12 Months',
    );

    /**
     *
     * reputation icons
     *
     * @var array
     */
    public static $icons = array(
        1   => '<i class="fa fa-star-o fa-lg star-yellow"></i>',
        10  => '<i class="fa fa-star-o fa-lg star-green"></i>',
        50  => '<i class="fa fa-star-o fa-lg star-blue"></i>',
        100 => '<i class="fa fa-star-o fa-lg star-red"></i>',
        200 => '<i class="fa fa-star-o fa-lg star-gold"></i>',
    );

    /**
     *
     * users table service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ReputationTable());
    }

    /**
     *
     * get users table service
     *
     * @return \Ppb\Service\Users
     */
    public function getUsers()
    {
        if (!$this->_users instanceof Users) {
            $this->setUsers(
                new Users());
        }

        return $this->_users;
    }

    /**
     *
     * set users table service
     *
     * @param \Ppb\Service\Users $users
     *
     * @return \Ppb\Service\Reputation
     */
    public function setUsers(Users $users)
    {
        $this->_users = $users;

        return $this;
    }

    /**
     *
     * create or update an row in the reputation table
     *
     * @param array $data
     * @param int   $posterId   force poster id in case the reputation is updated from the members module
     *                          only the admin can update any reputation row
     *
     * @return \Ppb\Service\Reputation
     */
    public function save($data, $posterId = null)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {
            $data['updated_at'] = new Expr('now()');
            $where = "id='{$row['id']}'";

            if ($posterId !== null) {
                $where .= " AND poster_id = '" . intval($posterId) . "'";
            }

            $this->_table->update($data, $where);
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }

    /**
     *
     * save the data from a reputation form post
     * this method will also update the reputation_data column in the users table
     *
     * @param array  $ids
     * @param float  $score
     * @param string $comments
     * @param int    $posterId
     *
     * @return \Ppb\Service\Reputation
     */
    public function postReputation(array $ids, $score, $comments, $posterId = null)
    {
        foreach ($ids as $id) {
            $this->save(array(
                'id'       => $id,
                'score'    => $score,
                'comments' => $comments,
                'posted'   => 1
            ), $posterId);

            $user = $this->findBy('id', $id)->findParentRow('\Ppb\Db\Table\Users', 'User');

            $reputationData = serialize(array(
                'score'      => $this->getScore($user->getData('id')),
                'percentage' => $this->getPercentage($user->getData('id')),
            ));

            $user->save(array(
                'reputation_data' => $reputationData,
            ));
        }

        return $this;
    }

    /**
     *
     * calculate the reputation score of a user based on different input variables
     *
     * @param int    $userId         the id of the user
     * @param float  $score          score threshold
     * @param string $operand        calculation operand
     * @param string $reputationType reputation type to be calculated (sale, purchase)
     * @param string $interval       calculation interval
     *
     * @return int                      resulted score
     */
    public function calculateScore($userId, $score = null, $operand = '=', $reputationType = null, $interval = null)
    {
        $select = $this->_table->select(array('total' => new Expr('count(*)')))
            ->where('user_id = ?', $userId)
            ->where('posted = ?', 1);

        if ($score !== null) {
            $select->where("score {$operand} ?", $score);
        }

        if (in_array($reputationType, array(self::SALE, self::PURCHASE))) {
            $select->where('reputation_type = ?', $reputationType);
        }

        if (in_array($interval, array_keys(self::$intervals))) {
            $select->where('updated_at >= date_sub(now(), ?)', new Expr($interval));
        }

        return $this->_table->fetchRow($select)->getData('total');
    }

    /**
     *
     * get the reputation score of a certain user
     * the method will always get live values from the reputation table
     *
     * @param int $userId
     *
     * @return int
     */
    public function getScore($userId)
    {
        $positive = $this->calculateScore($userId, self::POSITIVE_THRESHOLD, '>');
        $negative = $this->calculateScore($userId, self::POSITIVE_THRESHOLD, '<');

        return $positive - $negative;
    }

    /**
     *
     * get the positive reputation percentage of a certain user
     * the method will always get live values from the reputation table
     *
     * @param int $userId
     *
     * @return string
     */
    public function getPercentage($userId)
    {
        $all = $this->calculateScore($userId);

        if (!$all) {
            return 'n/a';
        }

        $positive = $this->calculateScore($userId, self::POSITIVE_THRESHOLD, '>');

        $percentage = round($positive * 100 / $all);

        return $percentage . '%';
    }

    /**
     *
     * reputation can only be deleted by the administrator, and only from the admin area
     *
     * @param array $ids reputation ids
     *
     * @return int the number of affected rows
     */
    public function delete(array $ids)
    {
        $result = 0;

        foreach ($ids as $id) {
            $user = $this->findBy('id', $id)->findParentRow('\Ppb\Db\Table\Users', 'User');

            $result += $this->_table->delete(
                $this->_table->getAdapter()->quoteInto('id = ?', $id));

            $reputationData = serialize(array(
                'score'      => $this->getScore($user->getData('id')),
                'percentage' => $this->getPercentage($user->getData('id')),
            ));

            $user->save(array(
                'reputation_data' => $reputationData,
            ));
        }

        return $result;
    }

}

