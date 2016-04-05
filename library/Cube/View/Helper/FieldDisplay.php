<?php

/**
 *
 * Cube Framework $Id$ 5/xr+FYB5la/cfcmbhDx8ZkX7f8CO1pBWCjI96ZxDYY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\View\Helper;

/**
 * displays a field's value if the field exists or false otherwise
 * also display array and serialized fields, and glue them by a selected implode separator
 *
 * Class FieldDisplay
 *
 * @package Cube\View\Helper
 */
class FieldDisplay extends AbstractHelper
{

    const DISPLAY_FALSE = 'n/a';
    const IMPLODE_GLUE = ', ';

    /**
     *
     * returns the formatted field for display purposes
     *
     * @param mixed  $field
     * @param string $true
     * @param string $false
     * @param string $glue
     *
     * @return string
     */
    public function fieldDisplay($field, $true = null, $false = null, $glue = null)
    {
        $glue = ($glue !== null) ? (string)$glue : self::IMPLODE_GLUE;

        if ($field) {
            if (is_array($field)) {
                return (implode($glue, $field));
            }

            if (($array = @unserialize(html_entity_decode($field))) !== false) {
                return implode($glue, $array);
            }

            return ($true === null) ? $field : $true;
        }
        else {
            return ($false === null) ? self::DISPLAY_FALSE : $false;
        }
    }

}

