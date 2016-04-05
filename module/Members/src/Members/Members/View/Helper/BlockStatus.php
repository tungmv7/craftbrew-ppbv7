<?php

/**
 *
 * PHP Pro Bid $Id$ 6u3cfoCXiERFzAdHhwUANYlp7dZfdcwwqJB34x6mBlY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * blocked user status view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel;

class BlockStatus extends AbstractHelper
{

    /**
     *
     * blocked user model
     *
     * @var \Ppb\Db\Table\Row\BlockedUser
     */
    protected $_blockedUser;

    /**
     *
     * main method, only returns object instance
     *
     * @param \Ppb\Db\Table\Row\BlockedUser $blockedUser
     *
     * @return $this
     */
    public function blockStatus($blockedUser = null)
    {
        if ($blockedUser !== null) {
            $this->setBlockedUser($blockedUser);
        }

        return $this;
    }

    /**
     *
     * get blocked user model
     *
     * @return \Ppb\Db\Table\Row\BlockedUser
     * @throws \InvalidArgumentException
     */
    public function getBlockedUser()
    {
        if (!$this->_blockedUser instanceof BlockedUserModel) {
            throw new \InvalidArgumentException("The blocked user model has not been instantiated");
        }

        return $this->_blockedUser;
    }

    /**
     *
     * set blocked user model
     *
     * @param \Ppb\Db\Table\Row\BlockedUser $blockedUser
     *
     * @return $this
     */
    public function setBlockedUser(BlockedUserModel $blockedUser)
    {
        $this->_blockedUser = $blockedUser;

        return $this;
    }

    /**
     *
     * display block type
     *
     * @return string
     */
    public function blockType()
    {
        $blockedUser = $this->getBlockedUser();
        $translate = $this->getTranslate();

        return $translate->_(BlockedUserModel::$blockTypes[$blockedUser['type']]);
    }

    /**
     *
     * display blocked actions
     *
     * @param string $separator
     *
     * @return string
     */
    public function blockedActions($separator = '<br>')
    {
        $blockedUser = $this->getBlockedUser();
        $translate = $this->getTranslate();

        $output = array();

        foreach ($blockedUser->getBlockedActions() as $blockedAction) {
            $output[] = $translate->_(BlockedUserModel::$blockedActions[$blockedAction]);
        }

        return implode($separator, $output);
    }

    /**
     *
     * display the block message that the blocked user sees
     *
     * @return string
     */
    public function blockMessage()
    {
        $blockedUser = $this->getBlockedUser();
        $translate = $this->getTranslate();

        $blocker = $blockedUser->findParentRow('\Ppb\Db\Table\Users');

        $message = sprintf(
            $translate->_('Your %s (%s) has been blocked from %s by %s.'),
            BlockedUserModel::$blockTypes[$blockedUser['type']],
            $blockedUser['value'],
            $this->blockedActions(', '),
            ($blocker) ? $blocker['username'] : $translate->_('the administrator'));

        if ($blockedUser['show_reason'] && $blockedUser['block_reason']) {
            $message .= '<br>' . sprintf($translate->_('Block Reason: %s'), $blockedUser['block_reason']);
        }

        return $message;
    }
}

