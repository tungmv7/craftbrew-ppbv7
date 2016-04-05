<?php

/**
 * 
 * Cube Framework $Id$ ud8pL4md/+RQ54VuntOSI5fU60v7lrcUFRk9V6NHHKY= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * locale resource management class
 */

namespace Cube\Application\Resource;

use Cube\Locale as LocaleObject;

class Locale extends AbstractResource
{

    /**
     *
     * @var \Cube\Locale;
     */
    protected $_locale;

    /**
     * 
     * initialize locale object based on resource settings
     * 
     * @return \Cube\Locale
     */
    public function init()
    {
        if (!($this->_locale instanceof LocaleObject)) {
            $code = (isset($this->_options['locale']['default'])) ? $this->_options['locale']['default'] : null;
            
            $this->_locale = new LocaleObject($code);
        }

        return $this->_locale;
    }

}

