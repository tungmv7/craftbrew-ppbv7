<?php

/**
 *
 * PHP Pro Bid $Id$ iyIPNf1AOlbx+wk4AztnOnQNYpm3LZM1f79lxI1pOFE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * message row object view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
        Ppb\Db\Table\Row\Message as MessageModel,
        Ppb\Service;

class Message extends AbstractHelper
{

    /**
     *
     * message model
     *
     * @var \Ppb\Db\Table\Row\Message
     */
    protected $_message;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\Message $message
     * @return $this
     */
    public function message($message = null)
    {
        if ($message !== null) {
            $this->setMessage($message);
        }

        return $this;
    }

    /**
     *
     * get store message model
     *
     * @return \Ppb\Db\Table\Row\Message
     * @throws \InvalidArgumentException
     */
    public function getMessage()
    {
        if (!$this->_message instanceof MessageModel) {
            throw new \InvalidArgumentException("The store message model has not been instantiated");
        }

        return $this->_message;
    }

    /**
     *
     * set store message model
     *
     * @param \Ppb\Db\Table\Row\Message $message
     * @return $this
     */
    public function setMessage(MessageModel $message)
    {
        $this->_message = $message;

        return $this;
    }

    /**
     *
     * display topic title (with generated links included)
     *
     * @return string
     */
    public function topicTitle()
    {
        $message = $this->getMessage();

        return $message->getTopicTitle();
    }
}

