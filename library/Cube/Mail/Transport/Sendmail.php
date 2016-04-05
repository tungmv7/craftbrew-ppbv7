<?php

/**
 * 
 * Cube Framework $Id$ v4toWor5rO2K2yDGpmJ+byqchO/Idj39G7d8dzqj5yU= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.2
 */
/**
 * mailer transport class - using sendmail program
 */

namespace Cube\Mail\Transport;

class Sendmail extends AbstractTransport
{

    /**
     * 
     * path of the sendmail program
     * 
     * @var string
     */
    protected $_path = '/usr/sbin/sendmail';

    /**
     * 
     * class constructor
     * 
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    /**
     * 
     * get the path of the sendmail application
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * 
     * set the path of the sendmail application
     * 
     * @param string $path
     * @return \Cube\Mail\Transport\Sendmail
     */
    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    /**
     *
     * send mail method
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function send()
    {
        $result = false;

        $mail = $this->getMail();

        $mailHeader = $mail->createHeader();
        $mailBody = $mail->getBody();

        $from = $mail->getFrom();

        $sendmail = sprintf("%s -oi -f%s -t", escapeshellcmd($this->_path), escapeshellarg($from['address']));

        foreach ($mail->getTo() as $to) {
            if (!@$mail = popen($sendmail, 'w')) {
                throw new \RuntimeException(sprintf(
                                "Could not execute sendmail program, path given: '%s'.", $this->_path));
            }

            fputs($mail, "To: " . $to['address'] . "\n");
            fputs($mail, $mailHeader . "\n");
            fputs($mail, $mailBody . "\n");
            $result = pclose($mail);
        }

        $result = ($result == 0) ? true : false;

        return $result;
    }

}

