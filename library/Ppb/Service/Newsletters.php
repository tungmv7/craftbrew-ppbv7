<?php

/**
 *
 * PHP Pro Bid $Id$ hwG2qgipCc7KYdWyE8WYN6JxrwI1isqwElqhud59HMU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * newsletters table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
    Ppb\Db\Table;

class Newsletters extends AbstractService
{

    /**
     *
     * newsletters recipients array
     *
     * @var array
     */
    protected $_recipients = array(
        'all'         => array(
            'name'  => 'All Users',
            'query' => '1',
        ),
        'active'      => array(
            'name'  => 'Active Users',
            'query' => 'active = 1',
        ),
        'suspended'   => array(
            'name'  => 'Suspended Users',
            'query' => 'active = 0',
        ),
        'subscribers' => array(
            'name'  => 'Newsletter Subscribers',
            'query' => 'active = 1 AND newsletter_subscription = 1',
        ),
        'store'       => array(
            'name'  => 'Store Owners',
            'query' => 'active = 1 AND store_active = 1',
        ),
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\Newsletters());
    }

    /**
     *
     * set recipients array
     *
     * @param array $recipients
     *
     * @return $this
     */
    public function setRecipients(array $recipients)
    {
        $this->_recipients = $recipients;

        return $this;
    }

    /**
     *
     * get recipients array
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    /**
     *
     * get recipient by key
     *
     * @param string $key
     *
     * @return array|false
     */
    public function getRecipient($key)
    {
        if (array_key_exists($key, $this->_recipients)) {
            return $this->_recipients[$key];
        }

        return false;
    }


    /**
     *
     * save newsletter recipients in the recipients table
     * return the number recipients saved or false
     *
     * @param string $key recipients to send to
     * @param int    $id  newsletter id
     *
     * @return int|false
     */
    public function saveRecipients($key, $id)
    {
        if (($recipient = $this->getRecipient($key)) !== false) {
            $newslettersRecipients = new Table\NewslettersRecipients();
            $users = new Table\Users();

            $newslettersRecipients->getAdapter()
                ->query("INSERT INTO " . $newslettersRecipients->getPrefix() . $newslettersRecipients->getName() . "
                    (newsletter_id, user_id, email)
                    SELECT {$id}, id, email
                    FROM " . $users->getPrefix() . $users->getName() . "
                    WHERE {$recipient['query']}");

            return true;
        }

        return false;
    }

    /**
     *
     * create or update a newsletter
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
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

//            $data['updated_at'] = new Expr('now()');
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }


    /**
     *
     * delete a newsletter from the table
     *
     * @param integer $id the id of the newsletter
     *
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }
}

