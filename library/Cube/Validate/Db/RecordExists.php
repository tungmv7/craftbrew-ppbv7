<?php

/**
 *
 * Cube Framework $Id$ d6ezgq3QsU/CyMy7vJ1nagIpQ2F6wIcBAVue05oFDSE=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.3
 */

namespace Cube\Validate\Db;

class RecordExists extends AbstractDb
{

    protected $_message = "No record matching '%value%' has been found.";

    /**
     *
     * check if the record exists
     *
     * @return bool
     */
    public function isValid()
    {
        $this->setMessage(
            str_replace('%value%', $this->_value, $this->getMessage()));

        $result = $this->_table->fetchRow(
            $this->getSelect());

        if (count($result) > 0) {
            return true;
        }

        return false;
    }

}

