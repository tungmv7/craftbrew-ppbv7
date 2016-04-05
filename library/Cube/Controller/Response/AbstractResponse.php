<?php

/**
 *
 * Cube Framework $Id$ 3TKTXJO2PnWPIw8Jz1tY9RAcjxf8NMcACoLc/xk/n6A=
 *
 * @link
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license
 *
 * @version     1.2
 */
/**
 * abstract response class
 */

namespace Cube\Controller\Response;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * http 1.1
     */

    const HTTP_11 = "HTTP/1.1";

    /**
     * http 1.0
     */
    const HTTP_10 = "HTTP/1.0";

    /**
     *
     * http version
     *
     * @var string
     */
    protected $_version;

    /**
     *
     * headers
     *
     * @var array
     */
    protected $_headers = array();

    /**
     *
     * body variable
     *
     * @var string
     */
    protected $_body = null;

    /**
     *
     * http response code
     *
     * @var integer
     */
    protected $_responseCode;

    /**
     *
     * class constructor
     *
     * @param string $version
     */
    public function __construct($version = self::HTTP_11)
    {
        $this->_version = $version;
    }

    /**
     *
     * get http version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     *
     * get headers in array format
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     *
     * set header, clear all previous headers
     *
     * @param string $header
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function setHeader($header)
    {
        $this->clearHeaders()
            ->addHeader($header);

        return $this;
    }

    /**
     *
     * add new header line
     * 7.2: if we have 404 in the header, empty the content automatically (workaround for fastcgi header status codes)
     *
     * @param string $header
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function addHeader($header)
    {
        $this->_headers[] = "$header";

        return $this;
    }

    /**
     *
     * add multiple headers
     *
     * @param array $headers
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }

        return $this;
    }

    /**
     *
     * clear all headers
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function clearHeaders()
    {
        $this->_headers = array();

        return $this;
    }

    /**
     * clear specified header
     *
     * @param  string $header
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function clearHeader($header)
    {
        $key = array_search($header, $this->_headers);
        if ($key !== false) {
            unset($this->_headers[$key]);
        }

        return $this;
    }

    /**
     *
     * get body variable
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     *
     * set body variable, reset if it already had content
     *
     * @param string $body
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function setBody($body)
    {
        $this->_body = (string)$body;

        return $this;
    }

    /**
     *
     * append content to the body
     *
     * @param string $body
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function appendBody($body)
    {
        if (!isset($this->_body)) {
            $this->setBody($body);
        }
        else {
            $this->_body .= (string)$body;
        }

        return $this;
    }

    /**
     *
     * clear body variable
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function clearBody()
    {
        $this->_body = null;

        return $this;
    }

    /**
     *
     * get the http response code (if set)
     *
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     *
     * set http response code
     *
     * @param integer $responseCode
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setResponseCode($responseCode)
    {
        if ($responseCode >= 100 && $responseCode <= 599) {
            $this->_responseCode = $responseCode;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("Invalid HTTP response code set (%s).", $responseCode));
        }

        return $this;
    }

    /**
     *
     * send headers
     *
     * @return \Cube\Controller\Response\AbstractResponse
     * @throws \RuntimeException
     */
    public function sendHeaders()
    {
        $responseCodeSent = false;

        if (!headers_sent()) {
            foreach ($this->_headers as $header) {
                if (isset($this->_responseCode) && $responseCodeSent === false) {
                    header($header, true, $this->_responseCode);
                    $responseCodeSent = true;
                }
                else {
                    header($header, true);
                }
            }
        }
        else {
            throw new \RuntimeException('Cannot call "sendHeaders" function. Headers already sent.');
        }

        return $this;
    }

    /**
     *
     * set redirect url and code
     *
     * @param string $url
     * @param int    $code
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function setRedirect($url, $code = 302)
    {
        $this->clearHeaders()
            ->addHeader('Location: ' . $url)
            ->setResponseCode($code);

        return $this;
    }

    /**
     *
     * output body
     *
     * @return string
     */
    public function outputBody()
    {
        echo $this->_body;
    }

    /**
     *
     * send response
     */
    public function send()
    {
        $this->sendHeaders()
            ->outputBody();
    }

}

