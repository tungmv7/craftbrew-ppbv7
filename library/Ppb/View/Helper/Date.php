<?php

/**
 *
 * PHP Pro Bid $Id$ qP1GbE2btFhCWgpckhd+na8RbuASmKCt9x2ssxt0ftA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * date view helper class
 *
 * Theory of operation:
 * - dates are saved based on the timezone set in the admin area
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper;

class Date extends AbstractHelper
{

    /**
     *
     * the format the date will be output in (strftime format required)
     *
     * @var string
     */
    protected $_format;

    /**
     *
     * class constructor
     *
     * @param string $format the format the date will be output in (strftime format required)
     */
    public function __construct($format)
    {
        $this->_format = $format;
    }

    /**
     *
     * display a formatted date
     *
     * @param string $date
     * @param bool   $dateOnly
     * @param string $format
     * @return string
     */
    public function date($date, $dateOnly = false, $format = null)
    {
        if ($date === null) {
            return 'n/a';
        }

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        if ($format === null) {
            $format = $this->_format;
        }

        if ($dateOnly){
            $format = trim(str_ireplace('%H:%M:%S', '', $format));
        }

        return strftime($format, $date);
    }

}

