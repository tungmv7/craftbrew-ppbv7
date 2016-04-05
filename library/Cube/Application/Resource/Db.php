<?php

/**
 * 
 * Cube Framework $Id$ 2mBFh3EKb/5jbV/Q2mkm0BA5fqd5xPZrvvbntmt5cTg= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * creates a db adapter resource
 */

namespace Cube\Application\Resource;

use Cube\Db as DbObject,
    Cube\Db\Adapter\AbstractAdapter;

class Db extends AbstractResource
{

    /**
     *
     * @var \Cube\Db\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     *
     * initialize translate object
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Translate
     */
    public function init()
    {
        if (!($this->_adapter instanceof AbstractAdapter)) {
            if (!isset($this->_options['db']['adapter'])) {
                throw new \InvalidArgumentException("A database adapter is required for creating a Db resource.");
            }

            $this->_adapter = DbObject::factory($this->_options['db']['adapter'], $this->_options['db']);
        }

        return $this->_adapter;
    }

}

