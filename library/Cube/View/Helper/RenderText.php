<?php

/**
 *
 * Cube Framework $Id$ j4J85t76JjL1v9/h+D6/s7dtf5Q87++PCfVdgYdYAuQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * processes an input value and renders it as forced text
 * applies nl2br as well
 */

namespace Cube\View\Helper;

class RenderText extends AbstractHelper
{

    /**
     *
     * output formatted string
     *
     * @param string $string
     * @param bool   $nl2br
     *
     * @return string
     */
    public function renderText($string, $nl2br = false)
    {
        $output = trim(str_ireplace(
            array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'),
            stripslashes(rawurldecode($string))));

        return ($nl2br) ? nl2br($output) : $output;
    }

}

