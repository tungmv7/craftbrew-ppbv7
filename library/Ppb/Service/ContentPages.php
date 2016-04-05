<?php

/**
 *
 * PHP Pro Bid $Id$ znSUuM4wWKyi4xJDIYEIDq/SUq4DNL248GWN8/gagtM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * content pages table service class
 */


namespace Ppb\Service;

use Cube\Db\Expr,
        Ppb\Db\Table\ContentPages as ContentPagesTable;

class ContentPages extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ContentPagesTable());
    }

    /**
     *
     * create or update a page
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


    /**
     *
     * delete a content page from the table
     *
     * @param integer $id the id of the content page
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }
}

