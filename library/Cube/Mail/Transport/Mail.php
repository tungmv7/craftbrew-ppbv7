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
/**
 * mailer transport class - using php mail function
 */

namespace Cube\Mail\Transport;

class Mail extends AbstractTransport
{

    /**
     *
     * send mail method
     *
     * @return bool
     */
    public function send()
    {
        $result = false;

        $mail = $this->getMail();

        $mailHeader = $mail->createHeader();
        $mailBody = $mail->getBody();

        $from = $mail->getFrom();

        $params = sprintf("-oi -f %s", $from['address']);

        if (!ini_get('safe_mode')) {
            ini_set('sendmail_from', $from['address']);
        }

        foreach ($mail->getTo() as $to) {
            $result = @mail($to['address'], $mail->getSubject(), $mailBody, $mailHeader, $params);
        }

        ini_restore('sendmail_from');


        return $result;
    }

}

