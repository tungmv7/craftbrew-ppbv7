<?php

/**
 *
 * PHP Pro Bid $Id$ u9wmZb7Whyp71RsAD0Vg/NSWfZvNkSxwVyaEF0gD05I=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * SEARCH AUTOCOMPLETE MOD
 */
/**
 * autocomplete tags table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
        Ppb\Db\Table\AutocompleteTags as AutocompleteTagsTable;

class AutocompleteTags extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new AutocompleteTagsTable());
    }

    /**
     *
     * create or update a tag
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
}

