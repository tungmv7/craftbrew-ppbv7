<?php

/**
 *
 * PHP Pro Bid $Id$ dWgD00lP7qvB+C78YKYRE/O/MLMVfW3RvVKO/4FHYdY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * users statistics table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\UsersStatistics as UsersStatisticsTable,
    Ppb\Model\Elements,
    Cube\Db\Expr;

class UsersStatistics extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new UsersStatisticsTable());
    }

    /**
     *
     * create or update a stat row
     * if a row that matches the ip, user agent and accept language exists in the table, update the row
     * otherwise insert a new row
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        $select = $this->_table->select()
            ->where('remote_addr = ?', $data['remote_addr'])
            ->where('http_user_agent = ?', $data['http_user_agent']);

        $row = $this->_table->fetchRow($select);

        $data['updated_at'] = new Expr('now()');

        if (count($row) > 0) {
            if (isset($data['http_referrer'])) {
                unset($data['http_referrer']);
            }
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }

}

