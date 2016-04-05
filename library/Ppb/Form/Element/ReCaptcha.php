<?php

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 *
 * PHP Pro Bid $Id$ Z+zC3AZT+EAoOlUPBofyTtVOoA1xk9TM4UuZ1TXdCeU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * reCAPTCHA 2 form element for PHP Pro Bid V7
 *
 * customData accepted:
 *
 * theme: dark|light (default = light)
 * type: audio|image (default = image)
 * size: compact|normal (default = normal)
 *
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\Validate;

class ReCaptcha extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'ReCaptcha';

    /**
     *
     * only one instance of this widget can be present at one time
     *
     * @var bool
     */
    public static $loaded = false;

    /**
     *
     * class constructor
     *
     * the recaptcha validator will automatically be added to this form element
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        self::$loaded = true;

        $this->addValidator(
            new Validate\ReCaptcha()
        );
    }

    /**
     *
     * render the recaptcha form element
     *
     * @return string
     */
    public function render()
    {

        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $customData[] = "'sitekey' : '" . $settings['recaptcha_public_key'] . "'";

        foreach ((array)$this->_customData as $key => $value) {
            $customData[] = "'{$key}' : '{$value}'";
        }

        $callbackFunctionName = 'onloadCallback' . ucfirst($this->_name);

        return '
            <script type="text/javascript">
                var onloadCallback = function() {
                    grecaptcha.render("' . $this->_name . '", {
                        ' . implode(', ', $customData) . '
                    });
                };
            </script>
            <div id="' . $this->_name . '"></div>
            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                async defer></script>';
    }

}

