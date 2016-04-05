<?php

/**
 * 
 * PHP Pro Bid $Id$ jJRt2v6iacCDBbgwXW1gAJeUvTMQ/je/1jSlXdngiv4=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * timezones table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Timezones as TimezonesTable;

class Timezones extends AbstractService
{

    /**
     * 
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new TimezonesTable());
    }

    /**
     * 
     * get all timezones
     * to be used for the timezone selector
     * 
     * @return array
     */
    public function getMultiOptions()
    {
        $data = array();

        $rows = $this->_table->fetchAll();

        foreach ($rows as $row) {
            $data[(string) $row['value']] = $row['caption'];
        }

        return $data;
    }

}

