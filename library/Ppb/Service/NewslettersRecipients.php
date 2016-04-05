<?php

/**
 *
 * PHP Pro Bid $Id$ GWWOaF6678NOsfTq/yiu8ipL+Tfq+KhMVA+y7Zx6tRXXB2ptyFfCwFEsS2KSccbKU9bQUZlwwPZMv09DmUltKQ==
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * newsletters recipients table service class
 */


namespace Ppb\Service;

use Ppb\Db\Table\NewslettersRecipients as NewslettersRecipientsTable;

class NewslettersRecipients extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new NewslettersRecipientsTable());
    }

    /**
     *
     * delete multiple recipients from the table
     *
     * @param array $ids the ids of the rows to be deleted
     * @return int     returns the number of affected rows
     */
    public function delete(array $ids)
    {
        $adapter = $this->_table->getAdapter();

        $where[] = $adapter->quoteInto('id IN (?)', $ids);

        return $this->_table->delete($where);
    }
}

