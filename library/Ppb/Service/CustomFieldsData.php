<?php

/**
 *
 * PHP Pro Bid $Id$ eBR8qPhV1vmloIN+phBSX/hQtbRdh1l1d5eCHIePGQY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * custom fields data table service class
 *
 * IMPORTANT:
 * search serialized custom fields:
 * select * from probid_custom_fields_data where value REGEXP '"x"|"y"|"z"';
 * (maybe we will serialize all saved data)
 */

namespace Ppb\Service;

use Ppb\Db\Table;

class CustomFieldsData extends AbstractService
{

    /**
     *
     * custom fields and custom fields data tables service
     *
     * @var \Ppb\Service\CustomFields
     */
    protected $_customFields;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\CustomFieldsData());
    }

    /**
     *
     * get custom fields table service
     *
     * @return \Ppb\Service\CustomFields
     */
    public function getCustomFieldsService()
    {
        if (!$this->_customFields instanceof CustomFields) {
            $this->setCustomFieldsService(
                new CustomFields());
        }

        return $this->_customFields;
    }

    /**
     *
     * set custom fields table service
     *
     * @param \Ppb\Service\CustomFields $customFields
     *
     * @return $this
     */
    public function setCustomFieldsService(CustomFields $customFields)
    {
        $this->_customFields = $customFields;

        return $this;
    }

    /**
     *
     * save data in the table
     *
     * 7.7: data is only saved if the custom field exists - workaround for when using the bulk lister and having
     * custom fields columns that do not exist
     *
     * @param string|array $value   custom field value (if array it will be serialized before saving)
     * @param string       $type    custom field data type (item, user etc)
     * @param integer      $fieldId custom_fields table id
     * @param integer      $ownerId id of the column for which this data belongs to
     *
     * @return $this
     */
    public function save($value, $type, $fieldId, $ownerId)
    {
        $customField = $this->getCustomFieldsService()->findBy('id', $fieldId);

        if (count($customField) > 0) {
            if (is_array($value)) {
                $value = serialize($value);
            }

            $data = array(
                'value'    => strval($value),
                'field_id' => intval($fieldId),
                'owner_id' => intval($ownerId),
                'type'     => strval($type),
            );

            $select = $this->_table->select()
                ->where("field_id = ?", $fieldId)
                ->where("owner_id = ?", $ownerId)
                ->where("type = ?", $type);

            $row = $this->_table->fetchRow($select);


            if (count($row) > 0) {
                $this->_table->update($data, "id='{$row['id']}'");
            }
            else {
                $this->_table->insert($data);
            }
        }

        return $this;
    }

    /**
     *
     * delete data from the table
     *
     * @param string  $type    custom field type
     * @param integer $ownerId the id of record that the custom field data belongs to
     *
     * @return integer     returns the number of affected rows
     */
    public function delete($type, $ownerId)
    {
        $adapter = $this->_table->getAdapter();

        $where = $adapter->quoteInto('type = ? AND ', $type) .
            $adapter->quoteInto('owner_id = ?', $ownerId);

        return $this->_table->delete($where);
    }

}

