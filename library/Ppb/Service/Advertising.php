<?php

/**
 *
 * PHP Pro Bid $Id$ ApWTXRZ1YQNstyOA/9kKmV3wwTHFgUo7WrOsf5BWxI0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * advertising table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
        Ppb\Db\Table\Advertising as AdvertisingTable;

class Advertising extends AbstractService
{

    /**
     *
     * default advert sections - will return in case
     * the active theme doesnt have a valid "adverts.txt" file set
     *
     * @var array
     */
    protected $_defaultSections = array(
        'header' => 'Site Header',
        'footer' => 'Site Footer',
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new AdvertisingTable());
    }

    /**
     *
     * create or update an advert
     *
     * @param array $data
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

        if (array_key_exists('language', $data) && empty($data['language'])) {
            unset($data['language']);
        }

        if (count($row) > 0) {

            $data['updated_at'] = new Expr('now()');
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
     * save settings
     *
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveSettings(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array_keys($data);

        foreach ((array)$data['id'] as $key => $value) {
            $row = $this->_table->fetchRow("id='{$value}'");

            $input = array();
            foreach ($columns as $column) {
                if (is_array($data[$column]) && isset($data[$column][$key])) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = parent::_prepareSaveData($input);

            if (count($row) > 0) {
                $this->_table->update($input, "id='{$value}'");
            }
        }

        return $this;
    }

    /**
     *
     * get the available advertising sections for the currently active theme
     *
     * @return array
     */
    public function getSections()
    {
        $settings = $this->getSettings();

        $fileName = \Ppb\Utility::getPath('themes') . DIRECTORY_SEPARATOR . $settings['default_theme'] . DIRECTORY_SEPARATOR . 'adverts.txt';

        if (file_exists($fileName)) {
            $output = array();
            if (($handle = fopen($fileName, "r")) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $output[$data[0]] = $data[1];
                }
                fclose($handle);
            }

            return $output;
        }

        return $this->_defaultSections;
    }

    /**
     *
     * delete an advert from the table
     *
     * @param integer $id the id of the advert
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }
}

