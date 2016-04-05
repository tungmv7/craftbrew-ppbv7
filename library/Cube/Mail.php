<?php

/**
 *
 * Cube Framework $Id$ HI7C78swHCI0cuZ/9p4YeGj4Lw59u01HK3FEtmIau2g=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube;

use Cube\Mail\Transport\AbstractTransport,
    Cube\Controller\Front,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter;

/**
 * unified mailer class
 *
 * Class Mail
 *
 * @package Cube
 */
class Mail
{
    /**
     *  carriage return
     */

    const CRLF = "\r\n";

    /**
     * new line character
     */
    const NL = "\n";

    /**
     *
     * mail charset
     *
     * @var string
     */
    protected $_charset = 'utf-8';

    /**
     *
     * content type, accepted: text/plain, text/html
     *
     * @var string
     */
    protected $_contentType = 'text/plain';

    /**
     *
     * content encoding
     *
     * @var string
     */
    protected $_encoding = '7bit';

    /**
     *
     * mailer to use
     *
     * @var \Cube\Mail\Transport\AbstractTransport
     */
    protected $_transport;

    /**
     *
     * to field ('address', 'name')
     *
     * @var array
     */
    protected $_to = array();

    /**
     *
     * from field ('address', 'name')
     *
     * @var array
     */
    protected $_from = array();

    /**
     *
     * reply to field ('address', 'name')
     *
     * @var array
     */
    protected $_replyTo = array();

    /**
     *
     * cc field ('address', 'name')
     *
     * @var array
     */
    protected $_cc = array();

    /**
     *
     * bcc field ('address', 'name')
     *
     * @var array
     */
    protected $_bcc = array();

    /**
     *
     * email subject
     *
     * @var string
     */
    protected $_subject;

    /**
     *
     * text email body
     *
     * @var string
     */
    protected $_bodyText;

    /**
     *
     * html email body
     *
     * @var string
     */
    protected $_bodyHtml;

    /**
     *
     * email headers
     *
     * @var array
     */
    protected $_headers = array('X-Mailer' => 'Cube Framework');

    /**
     * date header
     *
     * @var string
     */
    protected $_date = null;

    /**
     * Message-ID header
     *
     * @var string
     */
    protected $_messageId = null;

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * class constructor
     *
     * @param string $charset
     */
    public function __construct($charset = null)
    {
        if ($charset !== null) {
            $this->_charset = $charset;
        }

        date_default_timezone_set(
            @date_default_timezone_get());

        $this->setDate()
            ->setMessageId();
    }

    /**
     *
     * set charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     *
     * get charset
     *
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;

        return $this;
    }

    /**
     *
     * get content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     *
     * set content type
     *
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;

        return $this;
    }

    /**
     *
     * get 'To' field
     *
     * @return array
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     *
     * set 'To' field (clear first)
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function setTo($address, $name = null)
    {
        $this->clearTo()
            ->addTo($address, $name);

        return $this;
    }

    /**
     *
     * add new address in the  'To' field
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function addTo($address, $name = null)
    {
        $this->_to[] = array(
            'address' => $address,
            'name'    => $name,
        );

        return $this;
    }

    /**
     *
     * get 'From' field
     *
     * @return array
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     *
     * set 'From' field
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function setFrom($address, $name = null)
    {
        $this->_from = array(
            'address' => $address,
            'name'    => $name,
        );

        return $this;
    }

    /**
     *
     * get 'Cc' field
     *
     * @return array
     */
    public function getCc()
    {
        return $this->_cc;
    }

    /**
     *
     * set 'Cc' field
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function setCc($address, $name = null)
    {
        $this->_cc[] = array(
            'address' => $address,
            'name'    => $name,
        );

        return $this;
    }

    /**
     *
     * get 'Bcc' field
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     *
     * set 'Bcc' field
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function setBcc($address, $name = null)
    {
        $this->_bcc[] = array(
            'address' => $address,
            'name'    => $name,
        );

        return $this;
    }

    /**
     *
     * get 'ReplyTo' field
     *
     * @return array
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     *
     * set 'ReplyTo' field
     *
     * @param string $address 'To' email address
     * @param string $name    'To' name (optional)
     *
     * @return $this
     */
    public function setReplyTo($address, $name = null)
    {
        $this->_replyTo = array(
            'address' => $address,
            'name'    => $name,
        );

        return $this;
    }

    /**
     *
     * clear all "To" and headers fields
     *
     * @return $this
     */
    public function clearTo()
    {
        $this->_to = array();
        $this->_cc = array();
        $this->_bcc = array();
        $this->_replyTo = array();
        $this->_headers = array('X-Mailer' => 'Cube Framework');

        return $this;
    }

    /**
     *
     * get subject field
     *
     * @return string
     */
    public function getSubject()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_subject);
        }

        return $this->_subject;
    }

    /**
     *
     * set message subject field
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->_subject = (string)$subject;

        return $this;
    }

    /**
     *
     * get body text (for plain messages)
     *
     * @return string
     */
    public function getBodyText()
    {
        return $this->_bodyText;
    }

    /**
     *
     * set body text (for plain messages)
     *
     * @param string $bodyText
     *
     * @return $this
     */
    public function setBodyText($bodyText)
    {
        $this->setContentType('text/plain');
        $this->_bodyText = (string)$bodyText;

        return $this;
    }

    /**
     *
     * get body content (for html messages)
     *
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->_bodyHtml;
    }

    /**
     *
     * set body content (for html messages)
     *
     * @param string $bodyHtml
     *
     * @return $this
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->setContentType('text/html');
        $this->_bodyHtml = (string)$bodyHtml;

        return $this;
    }

    /**
     *
     * get mail body
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getBody()
    {
        if ($this->_contentType == 'text/plain') {
            return $this->_bodyText;
        }
        else if ($this->_contentType == 'text/html') {
            return $this->_bodyHtml;
        }
        else {
            throw new \RuntimeException(sprintf("The content type must be of type 
                'text/plain' or 'text/html', '%s' given", $this->_contentType));
        }
    }

    /**
     *
     * add multiple headers to the message
     *
     * @param array $headers
     *
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->addHeader($key, $value);
        }

        return $this;
    }

    /**
     *
     * add a single header to the message
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->_headers[(string)$key] = (string)$value;

        return $this;
    }

    /**
     *
     * get message date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     *
     * set message date
     *
     * @param string $date
     *
     * @return $this
     */
    public function setDate($date = null)
    {
        if ($date === null) {

            $date = date('D, j M Y H:i:s O');
        }

        $this->_date = $date;

        return $this;
    }

    /**
     *
     * get message id
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->_messageId;
    }

    /**
     *
     * set message id
     *
     * @param string $messageId
     *
     * @return $this
     */
    public function setMessageId($messageId = null)
    {
        if ($messageId === null) {
            $uniqId = md5(uniqid(time()));
            $serverName = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'localhost';
            $messageId = sprintf("<%s@%s>", $uniqId, $serverName);
        }

        $this->_messageId = $messageId;

        return $this;
    }

    /**
     *
     * get active mailer
     *
     * @return \Cube\Mail\Transport\AbstractTransport
     */
    public function getTransport()
    {
        if (!$this->_transport instanceof AbstractTransport) {
            $this->setTransport('mail');
        }

        return $this->_transport;
    }

    /**
     *
     * set active mailer
     *
     * @param string|\Cube\Mail\Transport\AbstractTransport $transport
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setTransport($transport)
    {
        if ($transport instanceof AbstractTransport) {
            $this->_transport = $transport;
        }
        else {
            if (!class_exists($transport)) {
                if (!in_array($transport, array('mail', 'sendmail', 'smtp'))) {
                    throw new \InvalidArgumentException(
                        sprintf("The mail transport must be
                                one of 'mail', 'sendmail', 'smtp' or a class that extends
                                \Cube\Mail\Transport\AbstractTransport.", $transport));
                }

                $transport = '\\Cube\\Mail\\Transport\\' . ucfirst($transport);
            }

            $this->_transport = new $transport();
        }

        return $this;
    }

    /**
     *
     * get the view object
     *
     * @return \Cube\View
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->setView();
        }

        return $this->_view;
    }

    /**
     * set the view object
     *
     * @param \Cube\View $view
     *
     * @return $this
     */
    public function setView(View $view = null)
    {
        if (!$view instanceof View) {
            $view = new View();
        }

        $this->_view = $view;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * format an address field
     *
     * @param array $data array of data
     *
     * @return string       formatted address
     */
    protected function _formatAddress($data)
    {
        $address = array();

        if (array_key_exists('address', $data)) {
            $data = array($data);
        }

        foreach ((array)$data as $field) {
            if (isset($field['name'])) {
                $address[] = $field['name'] . ' <' . $field['address'] . '>';
            }
            else {
                $address[] = $field['address'];
            }
        }

        return implode('; ', $address);
    }

    /**
     *
     * filter a string of new line characters
     *
     * @param string $string
     *
     * @return string
     */
    protected function _filterString($string)
    {
        return trim(str_replace(array("\r", "\n"), '', $string));
    }

    /**
     *
     * create mail header
     *
     * @return string
     * @throws \RuntimeException
     */
    public function createHeader()
    {
        $headers = array();

        $this->addHeader('Date', $this->getDate());

        if (!isset($this->_from['address'])) {
            throw new \RuntimeException("The 'From' email field must be set.");
        }

        $this->addHeader('Return-Path', '<' . $this->_from['address'] . '>');

        if (!$this->_transport instanceof Mail\Transport\Mail) {
            if (count($this->getTo()) > 0) {
                $this->addHeader('To', $this->_formatAddress($this->_to));
            }
            else if (count($this->_cc) == 0) {
                $this->addHeader('To', "Undisclosed Recipients");
            }
        }

        $from[] = $this->_from;
        $this->addHeader('From', $this->_formatAddress($from));

        if (count($this->_cc) > 0) {
            $this->addHeader('Cc', $this->_formatAddress($this->_cc));
        }

        if (count($this->_bcc) > 0) {
            $this->addHeader('Bcc', $this->_formatAddress($this->_bcc));
        }

        if (count($this->_replyTo) > 0) {
            $this->addHeader('Reply-To', $this->_formatAddress($this->_replyTo));
        }

        if (!$this->_transport instanceof Mail\Transport\Mail) {
            $this->addHeader('Subject', $this->_filterString($this->getSubject()));
        }


        $this->addHeader('Message-ID', $this->getMessageId())
            ->addHeader('MIME-Version', '1.0');
        $this->addHeader('Content-Transfer-Encoding', $this->_encoding)
            ->addHeader('Content-Type', sprintf('%s; charset="%s"', $this->_contentType, $this->_charset));

        foreach ((array)$this->_headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        return implode(self::NL, $headers);
    }

    /**
     *
     * send mail
     *
     * @return bool
     */
    public function send()
    {
        return $this->getTransport()
            ->setMail($this)
            ->send();
    }

}

