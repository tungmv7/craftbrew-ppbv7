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
 * messaging table row object model
 */

namespace Ppb\Db\Table\Row;

class Message extends AbstractRow
{

    /**
     *
     * returns an array used by the url view helper to generate the messaging topic display url
     *
     * @param bool $admin if in admin module, generate a different link
     * @return array|false
     */
    public function link($admin = false)
    {
        $user = $this->getUser();

        if (!in_array($user['id'], array($this->getData('sender_id'), $this->getData('receiver_id')))) {
            return false;
        }

        if ($admin) {
            return array(
                'module'     => 'admin',
                'controller' => 'tools',
                'action'     => 'messaging-topic',
                'id'         => $this->getData('id'),
            );
        }

        return array(
            'module'     => 'members',
            'controller' => 'messaging',
            'action'     => 'topic',
            'id'         => $this->getData('id'),
        );
    }

    public function getTopicTitle()
    {
        $topicTitle = $this->getData('topic_title');
        if (!empty($topicTitle)) {
            return $topicTitle;
        }
        else {
            return $this->findParentRow('\Ppb\Db\Table\Messaging')->getData('topic_title');
        }
    }
}

