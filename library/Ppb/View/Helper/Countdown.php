<?php

/**
 *
 * PHP Pro Bid $Id$ AIiXhNVM2q3CXOtdzwyXgVpEpnRWhnJhPFrIViu1Kck=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * live countdown / timer view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front;

class Countdown extends AbstractHelper
{

    /**
     * constants in seconds
     */
    const DAY = 86400;
    const HOUR = 3600;
    const MIN = 60;
    const SEC = 1;

    /**
     *
     * intervals array
     * values mean:
     * [0] minimum value in seconds needed for the result to display (eg. from 1 day)
     * [1] plural string
     * [2] singular string
     * [3] separator
     * [4] suffix
     *
     * @var array
     */
    protected $_intervals = array(
        self::DAY  => array(self::DAY, 'days', 'day', ' ', ', '),
        self::HOUR => array(self::HOUR, 'hrs', 'hr', '', ''),
        self::MIN  => array(0, 'mins', 'min', '', ''),
        self::SEC  => array(0, 'secs', 'sec', '', ''),
    );

    /**
     *
     * base url of the application
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *
     * date timestamp
     *
     * @var int
     */
    protected $_date;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        $this->_baseUrl = Front::getInstance()->getRequest()->getBaseUrl();
    }

    /**
     *
     * main method, will only store the date in the helper
     *
     * @param string|int $date
     *
     * @return string
     */
    public function countdown($date)
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $this->_date = $date;

        return $this;
    }

    /**
     *
     * display the time left w/o javascript component
     *
     * @param int $secs minimum number of seconds to display
     *
     * @return string
     */
    public function timeLeft($secs = self::MIN)
    {
        $translate = $this->getTranslate();

        if (!$this->_date) {
            return $translate->_('n/a');
        }

        $output = null;

        $original = $countdown = $this->_date - time();

        if ($countdown > 0) {
            foreach ($this->_intervals as $seconds => $data) {
                $left = floor($countdown / $seconds);
                $countdown -= ($left * $seconds);

                list($min, $plural, $singular, $separator, $suffix) = $data;
                if ($original >= $min && $seconds >= $secs) {
                    $output .= ' ' . $left . $separator . (($left > 1) ? $translate->_($plural) : $translate->_($singular)) . $suffix;
                }
            }
        }
        else {
            $output = '<span class="closed">' . $translate->_('Closed') . '</span>';
        }

        return $output;
    }

    /**
     *
     * display the time elapsed w/o javascript component
     *
     * @param int $secs minimum time interval to display (by key)
     *
     * @return string
     */
    public function timeElapsed($secs = self::MIN)
    {
        $translate = $this->getTranslate();

        $original = $elapsed = time() - $this->_date;

        if ($original <= 0 || $this->_date <= 0) {
            return $translate->_('n/a');
        }

        $output = null;

        foreach ($this->_intervals as $seconds => $data) {
            $left = floor($elapsed / $seconds);
            $elapsed -= ($left * $seconds);

            list($min, $plural, $singular, $separator, $suffix) = $data;
            if ($original >= $min && $seconds >= $secs) {
                $output .= $left . $separator . (($left > 1) ? $translate->_($plural) : $translate->_($singular)) . $suffix;
            }
        }

        return $output;
    }

    public function display()
    {
        $translate = $this->getTranslate();

        if (!$this->_date) {
            return $translate->_('n/a');
        }

        $this->_generateJavascript();

        $countdown = $this->_date . '000'; // workaround for 32 bit systems
        return '<span data-countdown="' . $countdown . '"></span>';
    }

    protected function _generateJavascript()
    {
        $translate = $this->getTranslate();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $this->getView()->getHelper('script');
        $scriptHelper->addBodyCode('<script type="text/javascript" src="' . $this->_baseUrl . '/js/jquery.countdown.min.js"></script>')
            ->addBodyCode("<script type=\"text/javascript\">" . "\n"
                . " $(document).ready(function() {    " . "\n"
                . "     $('[data-countdown]').each(function () {
                            var element = $(this), finalDate = element.data('countdown');
                            element.countdown(finalDate, function (event) {
                                var format =  '%M" . $translate->_('mins') . " %S" . $translate->_('secs') . "';
                                if (event.offset.totalDays > 0 || event.offset.hours > 0) {
                                    format = '%-H" . $translate->_('hrs') . " ' + format;
                                }
                                if (event.offset.totalDays > 0) {
                                    format = '%-D %!D:" . $translate->_('day') . "," . $translate->_('days') . ";, ' + format;
                                }

                                element.html(event.strftime(format));
                            }).on('finish.countdown', function(event) {
                                element.html('<span class=\"closed\">" . $translate->_('Closed') . "</span>');
                            });
                        });
                    }); " . "\n"
                . "</script>");
    }
}

