<?php

/**
 *
 * PHP Pro Bid $Id$ tJPMcn3M2bYE/5qU0galTwCyZobNLpmvTu7N/RVU45E=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * settings table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Settings as SettingsTable,
        Cube\Controller\Front;

class Settings extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new SettingsTable());
    }

    /**
     *
     * save data in the settings table
     *
     * @param array $data
     * @return \Ppb\Service\Settings
     */
    public function save(array $data)
    {
        foreach ($data as $key => $value) {
            $row = $this->_table->fetchRow("name='{$key}'");

            if (is_array($value)) {
                $value = serialize($value);
            }

            if (count($row) > 0) {
                $this->_table->update(array('value' => $value), "name='{$key}'");
            }
            else {
                $this->_table->insert(array('name' => $key, 'value' => $value));
            }
        }

        return $this;
    }

    /**
     *
     * get one or all settings table keys
     *
     * @param string  $key
     * @param bool $force whether to force the sql query or try to fetch the settings array from the front controller
     * @return array
     */
    public function get($key = null, $force = false)
    {
        $rows = array();
        $data = array();

        if ($key !== null) {
            $rows = array($this->_table->fetchRow("name='{$key}'"));
        }
        else {
            if ($force === false) {
                $rows = Front::getInstance()->getBootstrap()->getResource('settings');
            }

            if (empty($rows)) {
                $rows = $this->_table->fetchAll(
                    $this->_table->select(array('name', 'value')));
            }
            else {
                return $rows;
            }
        }

        foreach ($rows as $row) {
            $data[(string)$row['name']] = $row['value'];
        }

        return $data;
    }


}

