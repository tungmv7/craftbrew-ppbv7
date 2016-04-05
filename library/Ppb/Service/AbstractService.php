<?php

/**
 *
 * PHP Pro Bid $Id$ ndQqbNASM+9hpqIn5DNAWjtGmmwATsdKH6y9UC3pSZM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * abstract service class
 * TODO: instead of row table select do this->findBy, and instead of row table update do row->save
 */

namespace Ppb\Service;

use Cube\Db\Table\AbstractTable,
    Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Ppb\Db\Table\Row\User as UserModel;

abstract class AbstractService
{

    /**
     *
     * the table that is handled through the service
     *
     * @var \Cube\Db\Table\AbstractTable
     */
    protected $_table;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * logged in user model / payer for site fees service classes
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    public function __construct()
    {

    }

    /**
     *
     * set the table that will be used by the service
     *
     * @param \Cube\Db\Table\AbstractTable $table
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setTable($table = null)
    {
        if (!$table instanceof AbstractTable) {
            throw new \InvalidArgumentException('The table must be an instance of \Cube\Db\Table\AbstractTable');
        }

        $this->_table = $table;

        return $this;
    }

    /**
     *
     * get the table that is to be used by the service
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     *
     * get settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (!is_array($this->_settings)) {
            $this->setSettings(
                Front::getInstance()->getBootstrap()->getResource('settings'));
        }

        return $this->_settings;
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * get user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');

            if ($user instanceof UserModel) {
                $this->setUser($user);
            }
        }

        return $this->_user;
    }

    /**
     *
     * set user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * fetches all matched rows
     * is overwritten by certain service classes
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     *
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->_table->fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * prepares data for an insert or update operation, by removing any keys that
     * do not correspond to columns in the selected table
     * serialize all arrays before saving them in the database
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        foreach ($data as $key => $value) {
            $serialized = false;
            if (is_array($data[$key])) {
                $data[$key] = serialize($data[$key]);
                $serialized = true;
            }

            if (is_string($data[$key]) && !$serialized) {
                $data[$key] = html_entity_decode($data[$key]);
            }
        }

        $tableColumns = $this->getTable()->info(AbstractTable::COLS);

        return array_intersect_key($data, array_flip(array_values($tableColumns)));
    }

    /**
     *
     * find a row on the table by querying a certain column
     *
     * @param string $name  column name
     * @param string $value column value
     *
     * @return \Cube\Db\Table\Row\AbstractRow|null
     */
    public function findBy($name, $value)
    {
        if ($value === null) {
            return null;
        }

        return $this->_table->fetchRow(
            $this->_table->select()
                ->where("{$name} = ?", $value));
    }

    /**
     *
     * round a number to the required number of decimals
     * multiply by 10^$round, then get the floor value of that amount then divide by 10^round
     *
     * @param float $number
     * @param int   $decimals
     *
     * @return float
     */
    protected function _roundNumber($number, $decimals = 2)
    {
        $value = $number * pow(10, $decimals);
        $value = (!strpos($value, '.')) ? $value : floor($value);
        $number = $value / pow(10, $decimals);

        return $number;
    }

    /**
     *
     * dummy function used as a placeholder for translatable sentences
     *
     * @param $string
     *
     * @return mixed
     */
    protected function _($string)
    {
        return $string;
    }

}

