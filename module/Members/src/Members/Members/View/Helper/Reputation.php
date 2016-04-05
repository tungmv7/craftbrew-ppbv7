<?php

/**
 *
 * PHP Pro Bid $Id$ sU4nEvQMmyXOZJvru2b2wMl9lhIk4RKXaMOHXIbNVC8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * reputation row view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Service\Reputation as ReputationService;

class Reputation extends AbstractHelper
{

    protected $_reputationTypes = array(
        ReputationService::SALE     => 'Sale',
        ReputationService::PURCHASE => 'Purchase',
    );

    /**
     *
     * reputation row to be displayed
     *
     * @var array|\Ppb\Db\Table\Row\Reputation
     */
    protected $_reputation;

    /**
     *
     * main method, only returns object instance
     *
     * @return \Members\View\Helper\Reputation
     */
    public function reputation()
    {
        return $this;
    }

    /**
     *
     * set reputation row
     *
     * @param array|\Ppb\Db\Table\Row\Reputation $reputation
     * @return \Members\View\Helper\Reputation
     */
    public function setReputation($reputation)
    {
        $this->_reputation = $reputation;

        return $this;
    }

    /**
     *
     * display reputation type
     *
     * @return string
     */
    public function type()
    {
        $output = null;

        $translate = $this->getTranslate();

        switch ($this->_reputation['reputation_type']) {
            case ReputationService::SALE:
                $output = '<span class="label label-info">' . $translate->_($this->_reputationTypes[ReputationService::SALE]) . '</span>';
                break;
            case ReputationService::PURCHASE:
                $output = '<span class="label label-inverse">' . $translate->_($this->_reputationTypes[ReputationService::PURCHASE]) . '</span>';
                break;
        }

        return $output;
    }

    /**
     *
     * display reputation score
     *
     * @return string
     */
    public function score()
    {
        $output = null;

        if ($this->_reputation['posted']) {
            if ($this->_reputation['score'] > 3) {
                $output = '<i class="fa fa-plus-circle text-success"></i>';
            }
            else if ($this->_reputation['score'] == 3) {
                $output = '<i class="fa fa-circle muted"></i>';
            }
            else if ($this->_reputation['score'] < 3) {
                $output = '<i class="fa fa-minus-circle text-danger"></i>';
            }
        }

        return $output;
    }

    /**
     *
     * display reputation comments
     *
     * @param bool $admin
     * @return string
     */
    public function comments($admin = false)
    {
        $output = null;

        if ($this->_reputation->canShowComments($admin)) {
            if ($admin) {
                $postUrl = $this->getView()->url(array('module' => 'admin', 'controller' => 'users', 'action' => 'save-reputation', 'id' => $this->_reputation['id']));

                $output = $this->getView()->formElement('\Ppb\Form\Element\AjaxText', 'comment')
                    ->setAttributes(array('class' => 'form-control'))
                    ->setPostUrl($postUrl)
                    ->setValue($this->_reputation['comments'])
                    ->render();
            }
            else {
                $output = $this->_reputation['comments'];
            }
        }

        return $output;
    }
    
}

