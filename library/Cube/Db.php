<?php

/**
 *
 * Cube Framework $Id$ 2mBFh3EKb/5jbV/Q2mkm0BA5fqd5xPZrvvbntmt5cTg=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube;

/**
 * database class
 *
 * Class Db
 *
 * @package Cube
 */
class Db
{

    const INT_TYPE = 0;
    const FLOAT_TYPE = 1;
    const FETCH_ASSOC = 2;
    const FETCH_BOTH = 4;
    const FETCH_BOUND = 6;
    const FETCH_CLASS = 8;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_COLUMN = 7;
    const FETCH_FUNC = 10;
    const FETCH_GROUP = 65536;
    const FETCH_INTO = 9;
    const FETCH_LAZY = 1;
    const FETCH_NAMED = 11;
    const FETCH_NUM = 3;
    const FETCH_OBJ = 5;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_REL = 5;
    const FETCH_SERIALIZE = 524288;
    const FETCH_UNIQUE = 196608;
    const NULL_EMPTY_STRING = 1;
    const NULL_NATURAL = 0;
    const NULL_TO_STRING = null;
    const PARAM_BOOL = 5;
    const PARAM_INPUT_OUTPUT = -2147483648;
    const PARAM_INT = 1;
    const PARAM_LOB = 3;
    const PARAM_NULL = 0;
    const PARAM_STMT = 4;
    const PARAM_STR = 2;

    /**
     *
     * database factory
     *
     * @param mixed $adapter
     * @param array $config
     *
     * @return mixed database adapter
     * @throws \RuntimeException
     */
    public static function factory($adapter, $config = array())
    {
        if (class_exists($adapter)) {
            $dbAdapter = new $adapter($config);
        }
        else {
            throw new \RuntimeException(
                sprintf("Database adapter %s does not exist", $adapter));
        }

        return $dbAdapter;
    }

}

