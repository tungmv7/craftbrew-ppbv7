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
 * reCAPTCHA 2 validator for PHP Pro Bid V7
 *
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Cube\Controller\Front;

class ReCaptcha extends AbstractValidate
{

    /**
     * reCAPTCHA verify server
     *
     * @var string
     */
    const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';
    /**
     *
     * validation error message
     *
     * @var string
     */
    protected $_message = "The reCAPTCHA code is incorrect.";

    /**
     *
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @return bool return true if the validation is successful
     */
    public function isValid()
    {
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');
        $request = Front::getInstance()->getRequest();

        $response = $request->getParam('g-recaptcha-response');

        if ($response == null || strlen($response) == 0) {
            return false;
        }

        $json = $this->_post(array(
                'secret'   => $settings['recaptcha_private_key'],
                'remoteip' => $_SERVER['REMOTE_ADDR'],
                'response' => $response
            ));

        $result = json_decode($json, true);

        if (isset($result['success']) && $result['success'] == true) {
            return true;
        }

        return false;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param array  $data
     *
     * @return array response
     */
    protected function _post(array $data)
    {
        $peerKey = version_compare(PHP_VERSION, '5.6.0', '<') ? 'CN_name' : 'peer_name';

        $options = array(
            'http' => array(
                'header'      => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'      => 'POST',
                'content'     => http_build_query($data, '', '&'),

                // Force the peer to validate (not needed in 5.6.0+, but still works
                'verify_peer' => true,
                // Force the peer validation to use www.google.com
                $peerKey      => 'www.google.com',
            ),
        );
        $context = stream_context_create($options);

        return file_get_contents(self::VERIFY_SERVER, false, $context);
    }

}

