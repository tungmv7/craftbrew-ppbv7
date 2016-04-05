<?php

/**
 *
 * Cube Framework $Id$ +0gziwE42eo3KT08O63z4trk26mNwk91piYhzo2VHx4=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.3
 */

namespace Cube\Validate\Db;

class NoRecordExists extends AbstractDb
{

    /**
     *
     * error message
     *
     * @var string
     */
    protected $_message = "A record matching '%value%' has been found.";

    /**
     *
     * check if the record exists and returns false if it does
     *
     * @return bool
     */
    public function isValid()
    {
        $this->setMessage(
            str_replace('%value%', $this->_value, $this->_message));

        $result = $this->_table->fetchRow(
            $this->getSelect());

        if (count($result) > 0) {
            return false;
        }

        return true;
    }

}

