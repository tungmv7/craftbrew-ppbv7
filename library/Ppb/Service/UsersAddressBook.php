<?php

/**
 *
 * PHP Pro Bid $Id$ NnD7PmGLonBVDolnrOHluucwUHaD4SQ7qiQX/cciAZs=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * users address book table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\UsersAddressBook as UsersAddressBookTable,
        Cube\Controller\Front,
        Ppb\Model\Elements,
        Ppb\Db\Table\Row\User as UserModel;

class UsersAddressBook extends AbstractService
{

    /**
     *
     * columns to be saved in the serializable address field
     * TODO: get from user model
     *
     * @var array
     */
    protected $_addressFields = array();

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new UsersAddressBookTable());
    }

    /**
     *
     * set address fields (by default from user elements model)
     *
     * @param array $addressFields
     * @return array
     */
    public function setAddressFields($addressFields = null)
    {
        if ($addressFields === null) {
            $userModel = new Elements\User();
            $elements = $userModel->getElements();

            foreach ($elements as $element) {
                if (array_intersect((array)$element['form_id'], array('address')) && $element['element'] != 'hidden') {
                    $addressFields[] = $element['id'];
                }
            }
        }
        $this->_addressFields = $addressFields;

        return $this;
    }

    /**
     *
     * get address fields
     *
     * @return array
     */
    public function getAddressFields()
    {
        if (!$this->_addressFields) {
            $this->setAddressFields();
        }

        return $this->_addressFields;
    }


    /**
     *
     * save a user's address in the address book table
     *
     * @param array  $data   address data (serializable)
     * @param int    $userId user id
     * @param string $prefix the prefix of the input fields (optional)
     * @return int  the id of the saved column
     * @throws \InvalidArgumentException
     */
    public function save($data, $userId, $prefix = null)
    {
        $row = array();

        if ($prefix !== null) {
            foreach ($data as $key => $value) {
                if (strstr($key, $prefix) !== false) {
                    unset($data[$key]);
                    $key = str_replace($prefix, '', $key);
                    $data[$key] = $value;
                }
            }
        }

        if (isset($data['address_id'])) {
            $select = $this->_table->select()
                    ->where('user_id = ?', $userId)
                    ->where("id = ?", $data['address_id']);

            $row = $this->_table->fetchRow($select);
        }

        $address = array();

        $addressFields = $this->getAddressFields();

        foreach ($addressFields as $field) {
            $address[$field] = (isset($data[$field])) ? $data[$field] : null;
        }

        if (count($row) > 0) {
            $this->_table->update(array(
                'address' => serialize($address),
            ), "id='{$row['id']}'");

            $id = $row['id'];
        }
        else {
            $this->_table->insert(array(
                'user_id' => $userId,
                'address' => serialize($address),
            ));

            $id = $this->_table->lastInsertId();
        }

        // one address needs to be set as primary
        $select = $this->_table->select()
                ->where("user_id = ?", $userId)
                ->where('is_primary = ?', 1);

        $rowset = $this->_table->fetchAll($select);

        if (!count($rowset)) {
            $this->_table->update(array(
                'is_primary' => 1
            ), "id='{$id}'");
        }

        return $id;
    }

    /**
     *
     * get the addresses of a user in a key => value format
     * to be used for the shipping address selector
     *
     * @param UserModel $user
     * @param string    $separator
     * @param bool      $enhanced if true, it will return the data as an array, usable by the SelectAddress form element
     * @return array
     */
    public function getMultiOptions(UserModel $user, $separator = ', ', $enhanced = false)
    {
        $data = array();

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        /** @var \Members\View\Helper\UserDetails $userDetails */
        $userDetails = $view->getHelper('userDetails');

        $object = clone $user;

        $select = $this->getTable()->select()
                ->order('is_primary DESC, id DESC');
        $addresses = $user->findDependentRowset('\Ppb\Db\Table\UsersAddressBook', null, $select);

        foreach ($addresses as $address) {
            $title = $userDetails->userDetails($object)->setAddress($address)->displayAddress($separator);
            if ($enhanced) {
                $data[(string)$address['id']] = array(
                    'title'      => $title,
                    'locationId' => $address['country'],
                    'postCode'   => $address['zip_code'],
                );
            }
            else {
                $data[(string)$address['id']] = $title;
            }
        }

        return $data;
    }

}

