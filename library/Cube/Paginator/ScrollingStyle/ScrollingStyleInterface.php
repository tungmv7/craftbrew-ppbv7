<?php

/**
 * 
 * Cube Framework $Id$ TvhT4RcGouAjp8Y2pgGMU1sDwMz7ydB7CcRHBlmK702OlExDS6lIcpA75Sn/Zgtr8ZMoYMkrTHTXj6ot5LrqkQ== 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */

namespace Cube\Paginator\ScrollingStyle;

use Cube\Paginator;

interface ScrollingStyleInterface
{

    /**
     * 
     * returns an array of pages given a page number and range.
     *
     * @param  \Cube\Paginator $paginator
     * @param  integer $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null);
}

