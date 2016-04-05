<?php

/**
 *
 * Cube Framework $Id$ z2mtfTJbpgMmnKFL7onS8Rezs2icti4y2C0tUKG7jAU=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Controller\Response;

/**
 * response interface
 *
 * Interface ResponseInterface
 *
 * @package Cube\Controller\Response
 */
interface ResponseInterface
{

    public function getVersion();

    public function getHeaders();

    /**
     *
     * add single response header
     *
     * @param $header
     *
     * @return $this
     */
    public function addHeader($header);

    /**
     *
     * add multiple headers
     *
     * @param array $headers
     */
    public function addHeaders(array $headers);

    public function getBody();

    /**
     *
     * append content to the body
     *
     * @param string $body
     */
    public function appendBody($body);

    /**
     *
     * set redirect url and code
     *
     * @param string $url
     * @param int    $code
     */
    public function setRedirect($url, $code = 302);

    public function send();

    /**
     *
     * set response header
     *
     * @param $header
     *
     * @return $this
     */
    public function setHeader($header);

    /**
     *
     * set http response code
     *
     * @param integer $responseCode
     */
    public function setResponseCode($responseCode);

}

