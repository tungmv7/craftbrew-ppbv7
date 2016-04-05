<?php

/**
 * 
 * Cube Framework $Id$ T8VZlERI99HTB+86iQipwA+HAzTYN22ZiOBiaXwRyVs= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * sliding paginator scrolling style
 */

namespace Cube\Paginator\ScrollingStyle;

use Cube\Paginator;

class Sliding implements ScrollingStyleInterface
{

    /**
     * 
     * returns an array of pages in range
     *
     * @param  \Cube\Paginator $paginator
     * @param  integer $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        if ($pageRange === null) {
            $pageRange = $paginator->getPageRange();
        }

        $pageNumber = $paginator->getCurrentPageNumber();
        $pageCount = count($paginator);
        
        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $min = $pageCount - $pageRange + 1;
            $max = $pageCount;
        }
        else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }

            $offset = $pageNumber - $delta;
            $min = $offset + 1;
            $max = $offset + $pageRange;
        }

        return $paginator->getPagesInRange($min, $max);
    }

}

