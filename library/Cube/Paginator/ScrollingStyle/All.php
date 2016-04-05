<?php

/**
 * 
 * Cube Framework $Id$ LHd6hrIXBJ0QRuLfqG52OcLNbIxa++u53u2B4lxHHRY= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * 
 */

namespace Cube\Paginator\ScrollingStyle;

use Cube\Paginator;

class All implements ScrollingStyleInterface
{

    /**
     * 
     * returns an array of all pages in the paginator
     *
     * @param  \Cube\Paginator $paginator
     * @param  integer $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        return $paginator->getPagesInRange(1, $paginator->count());
    }

}

