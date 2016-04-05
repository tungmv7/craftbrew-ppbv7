<?php

/**
 *
 * PHP Pro Bid $Id$ jfNoOWvsKdkVWfiU3/S+2EyFuID7Yqq5C2VFG4456ew=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * live date & time view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper;

class LiveTime extends AbstractHelper
{

    const ELEMENT_ID = 'live-time-id';
    const DATE_FORMAT = 'F d, Y H:i:s';

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
     * display a formatted date (w/ live clock component)
     *
     * @param string $date
     * @param null   $format
     * @return string
     */
    public function liveTime($date, $format = null)
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

        $this->_generateJavascript(
            date(self::DATE_FORMAT, $date));

        $format = str_replace(
            array(':', '%H', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z'), '', $format);

        return strftime($format, $date)
               . ' '
               . '<span id="' . self::ELEMENT_ID . '"></span>';
    }

    protected function _generateJavascript($dateTime)
    {
        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $this->getView()->getHelper('script');
        $scriptHelper->addBodyCode("<script type=\"text/javascript\">" . "\n"
                                   . " var serverDate = new Date('{$dateTime}'); " . "\n"
                                   . " function padLength(value){ " . "\n"
                                   . "     var output=(value.toString().length==1)? '0' + value : value; " . "\n"
                                   . "     return output; " . "\n"
                                   . " } " . "\n"
                                   . " function displayTime() { " . "\n"
                                   . "     serverDate.setSeconds(serverDate.getSeconds() + 1) " . "\n"
                                   . "     var timeString=padLength(serverDate.getHours()) + ':' + padLength(serverDate.getMinutes()) + ':' + padLength(serverDate.getSeconds()); " . "\n"
                                   . "     document.getElementById('" . self::ELEMENT_ID . "').innerHTML = timeString; " . "\n"
                                   . " } " . "\n"
                                   . " window.onload=function(){ " . "\n"
                                   . "     setInterval('displayTime()', 1000) " . "\n"
                                   . " }" . "\n"
                                   . "</script>");
    }

}

