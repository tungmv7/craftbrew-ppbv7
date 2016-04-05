<?php

/**
 *
 * Cube Framework $Id$ sXgF4YgfMEr8Hod7DgXJJdkaZQxruDg6i3M1AnUJ19c=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Mail\Transport;

/**
 * mailer transport class - using smtp protocol
 *
 * Class Smtp
 *
 * @package Cube\Mail\Transport
 */
class Smtp extends AbstractTransport
{

    /**
     *
     * SMTP server connection
     *
     * @var mixed
     */
    protected $_connection = null;

    /**
     *
     * Local client hostname or i.p.
     *
     * @var string
     */
    protected $_name = 'localhost';

    /**
     *
     * Remote smtp hostname or i.p.
     *
     * @var string
     */
    protected $_host;

    /**
     * smtp server port
     *
     * @var int
     */
    protected $_port = 25;

    /**
     *
     * connection protocol "tcp" or "ssl"
     *
     * @var string
     */
    protected $_protocol = 'tcp';

    /**
     *
     * tls
     *
     * @var bool
     */
    protected $_tls = false;

    /**
     *
     * SMTP username
     *
     * @var string
     */
    protected $_username;

    /**
     *
     * SMTP password
     *
     * @var string
     */
    protected $_password;

    /**
     *
     * debug messages
     *
     * @var string
     */
    protected $_debug;

    /**
     *
     * class constructor
     *
     * @param string $host
     * @param array  $config
     */
    public function __construct($host = 'localhost', array $config = array())
    {
        parent::__construct($config);

        $this->setHost($host);
    }

    /**
     *
     * get local server name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * set local server name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     *
     * get remote smtp hostname
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     *
     * set remote smtp hostname
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    /**
     *
     * get smtp port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     *
     * set remote smtp port
     *
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->_port = $port;

        return $this;
    }

    /**
     *
     * get secure string
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    /**
     *
     * set protocol
     *
     * @param string $protocol
     *
     * @return $this
     */
    public function setProtocol($protocol)
    {
        if ('tls' == $protocol) {
            $this->_protocol = 'tcp';
            $this->_tls = true;
        }
        else {
            $this->_protocol = $protocol;
            $this->_tls = false;
        }

        return $this;
    }

    /**
     *
     * get smtp username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     *
     * set smtp username
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->_username = $username;

        return $this;
    }

    /**
     *
     * get smtp password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     *
     * set smtp password
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->_password = $password;

        return $this;
    }

    /**
     *
     * get output messages from the smtp server
     *
     * @return string
     */
    public function getDebug()
    {
        return (string)$this->_debug;
    }

    /**
     *
     * connect method
     *
     * @return bool
     */
    public function connect()
    {
        $hostname = (($this->_protocol == 'ssl') ? 'ssl://' : '') . $this->_host;
        $this->_connection = fsockopen($hostname, $this->_port, $errno, $errstr, 30);

        // response
        if ($this->_getCode() !== 220) {
            return false;
        }

        fputs($this->_connection, 'EHLO ' . $this->_name . "\r\n");
        if ($this->_getCode() !== 250) {
            fputs($this->_connection, 'HELO ' . $this->_name . "\r\n");
            if ($this->_getCode() !== 250) {
                return false;
            }
        }

        if ($this->_tls === true) {
            fputs($this->_connection, 'STARTTLS' . "\r\n");
            if ($this->_getCode() !== 220) {
                return false;
            }

            stream_socket_enable_crypto($this->_connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            fputs($this->_connection, 'EHLO ' . $this->_name . "\r\n");
            if ($this->_getCode() !== 250) {
                fputs($this->_connection, 'HELO ' . $this->_name . "\r\n");
                if ($this->_getCode() !== 250) {
                    return false;
                }
            }
        }

        if ($this->_host != 'localhost') {
            fputs($this->_connection, 'AUTH LOGIN' . "\r\n");
            if ($this->_getCode() !== 334) {
                return false;
            }

            fputs($this->_connection, base64_encode($this->_username) . "\r\n");
            if ($this->_getCode() !== 334) {
                return false;
            }
            fputs($this->_connection, base64_encode($this->_password) . "\r\n");
            if ($this->_getCode() !== 235) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * disconnect method
     *
     * @return $this
     */
    public function disconnect()
    {
        if ($this->isConnection()) {
            fputs($this->_connection, "QUIT " . "\r\n");
            fclose($this->_connection);
        }

        return $this;
    }

    /**
     *
     * check if we have an open connection
     *
     * @return bool
     */
    public function isConnection()
    {
        return ($this->_connection) ? true : false;
    }

    /**
     *
     * send mail method
     *
     * @return bool
     */
    public function send()
    {
        $result = false;

        if ($this->connect()) {
            // deliver the email
            $mail = $this->getMail();
            $from = $mail->getFrom();
            $replyTo = $mail->getReplyTo();

            foreach ($mail->getTo() as $to) {
                fputs($this->_connection, "MAIL FROM: {$from['address']}\r\n"
                    . "RCPT TO: " . $to['address'] . "\r\n"
                    . "DATA\r\n"
                    . "Subject: " . $mail->getSubject() . "\r\n"
                    . "From: " . $this->_formatAddress($from) . "\r\n"
                    . "To: " . $this->_formatAddress($to) . " \r\n");

                if (count($replyTo) > 0) {
                    fputs($this->_connection, "Reply-to: {$replyTo['address']} \r\n");
                }

                fputs($this->_connection, "X-Sender: <{$from['address']}>\r\n"
                    . "Return-Path: <{$from['address']}>\r\n"
                    . "Errors-To: <{$from['address']}>\r\n"
                    . "X-Mailer: Cube Framework/SMTP\r\n"
                    . "X-Priority: 3\r\n"
                    . "Content-Type: " . sprintf('%s; charset="%s"', $mail->getContentType(),
                        $mail->getCharset()) . "\r\n"
                    . "\r\n"
                    . $mail->getBody() . " \r\n"
                    . ".\r\n"
                    . "RSET\r\n");
            }

            $result = true;
        }

//        print ($this->_debug);

        // disconnect
        $this->disconnect();

        // return
        return $result;
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
     * get server response
     *
     * @return string
     */
    protected function _getServerResponse()
    {
        $response = "";
        while ($str = fgets($this->_connection, 4096)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }

        $this->_debug .= '<code>' . $response . '</code><br/>';

        return $response;
    }

    /**
     *
     * get the code from the server response
     *
     * @return int
     */
    protected function _getCode()
    {
        // filter code from response
        return (int)substr($this->_getServerResponse(), 0, 3);
    }
}

