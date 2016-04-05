<?php

/**
 *
 * Cube Framework $Id$ DKvflM2wgCcnlH0sdkBcO1Evb6PM+h/Mm61RPURlLqI=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * javascript, css etc view helper
 */

namespace Cube\View\Helper;

class Script extends AbstractHelper
{

    /**
     *
     * code to be added between the <head> tags of the html code
     *
     * @var array
     */
    protected $_headerCode = array();

    /**
     *
     * code to be added between the <body> tags, preferably towards the
     * bottom, usable for javascript code etc
     *
     * @var array
     */
    protected $_bodyCode = array();

    /**
     *
     * add code to the page header, duplicates will be skipped
     *
     * @param string $code
     *
     * @return \Cube\View\Helper\Script
     */
    public function addHeaderCode($code)
    {
        $code = (string)$code;

        if (!in_array($code, $this->_headerCode)) {
            $this->_headerCode[] = $code;
        }

        return $this;
    }


    /**
     *
     * remove header code
     *
     * @param string $code
     *
     * @return $this
     */
    public function removeHeaderCode($code)
    {
        $code = $this->_addSpecialChars($code);

        foreach ($this->_headerCode as $key => $value) {
            $value = $this->_addSpecialChars($value);

            if ($value == $code) {
                unset($this->_headerCode[$key]);
            }
        }

        return $this;
    }

    /**
     *
     * clear header code variable
     *
     * @return $this
     */
    public function clearHeaderCode()
    {
        $this->_headerCode = array();

        return $this;
    }

    /**
     *
     * add code to the page body, duplicates will be skipped
     *
     * @param string $code
     *
     * @return $this
     */
    public function addBodyCode($code)
    {
        if (is_array($code)) {
            foreach ($code as $c) {
                $this->addBodyCode($c);
            }
        }
        else {
            $code = (string)$code;

            if (!in_array($code, $this->_bodyCode)) {
                $this->_bodyCode[] = $code;
            }
        }

        return $this;
    }

    /**
     *
     * remove body code
     *
     * @param string $code
     *
     * @return $this
     */
    public function removeBodyCode($code)
    {
        $code = $this->_addSpecialChars($code);

        foreach ($this->_bodyCode as $key => $value) {
            $value = $this->_addSpecialChars($value);

            if ($value == $code) {
                unset($this->_bodyCode[$key]);
            }
        }

        return $this;
    }

    /**
     *
     * clear body code variable
     *
     * @return $this
     */
    public function clearBodyCode()
    {
        $this->_bodyCode = array();

        return $this;
    }

    /**
     *
     * method that is called by the reflection class, returns an instance of the object
     *
     * @return \Cube\View\Helper\Script
     */
    public function script()
    {
        return $this;
    }

    /**
     *
     * display the header code
     *
     * @return string
     */
    public function displayHeaderCode()
    {
        return implode("\n", $this->_headerCode);
    }

    /**
     *
     * display the footer code
     *
     * @return string
     */
    public function displayBodyCode()
    {
        return implode("\n", $this->_bodyCode);
    }

    /**
     *
     * add special chars
     *
     * @param string $input
     *
     * @return mixed
     */
    private function _addSpecialChars($input)
    {
        return str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $input);
    }
}

