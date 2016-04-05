<?php
/**
 *
 * PHP Pro Bid $Id$ QEzhgFv9KUnY0T/BGC9KWRr4eyGiDSk5DFdL44kDuYg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * this class converts a date string into a mysql datetime format
 *
 */

namespace Ppb\Db\Expr;

use Cube\Db\Expr;

class DateTime extends Expr
{
    /**
     *
     * magic method
     * return the expression
     *
     * @return string
     */
    public function __toString()
    {
        return "'" . date("Y-m-d H:i:s", strtotime($this->_expression)) . "'";
    }
} 