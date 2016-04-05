<?php

/**
 *
 * PHP Pro Bid $Id$ iyIPNf1AOlbx+wk4AztnOnQNYpm3LZM1f79lxI1pOFE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * message post form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
        Ppb\Service,
        Ppb\Filter,
        Cube\Validate;

class Message extends AbstractBaseForm
{

    const BTN_SUBMIT = 'message_post';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Send',
    );

    /**
     *
     * messaging service
     *
     * @var \Ppb\Service\Messaging
     */
    protected $_messaging;

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->_messaging = new Service\Messaging();

        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $topicId = $this->createElement('hidden', 'topic_id');
        $this->addElement($topicId);

        $topicType = $this->createElement('hidden', 'topic_type');
        $this->addElement($topicType);

        $receiverId = $this->createElement('hidden', 'receiver_id');
        $this->addElement($receiverId);

        $acceptPublicQuestions = $this->createElement('hidden', 'accept_public_questions');
        $this->addElement($acceptPublicQuestions);

        $saleId = $this->createElement('hidden', 'sale_id');
        $this->addElement($saleId);

        $listingId = $this->createElement('hidden', 'listing_id');
        $this->addElement($listingId);

        $username = $this->createElement('hidden', 'username');
        $this->addElement($username);

        $publicQuestion = $this->createElement('select', 'public_question');
        $publicQuestion->setLabel('Message Type')
                ->setMultiOptions(array(
                    0 => $translate->_('Private'),
                    1 => $translate->_('Public'),
                ))
                ->setAttributes(array(
                    'class' => 'form-control input-small'
                ));
        $this->addElement($publicQuestion);


        $title = $this->createElement('text', 'title');
        $title->setLabel('Title')
                ->setAttributes(array(
                    'class' => 'form-control input-large'
                ))
                ->setRequired()
                ->addValidator(
                    new Validate\NoHtml())
                ->addValidator(
                    new Validate\StringLength(array(null, 255)))
                ->addFilter(
                    new Filter\BadWords());

        $this->addElement($title);

        $content = $this->createElement('textarea', 'content');
        $content->setLabel('Message')
                ->setAttributes(array(
                    'rows'  => 6,
                    'class' => 'form-control',
                ))
                ->setRequired()
                ->addValidator(
                    new Validate\NoHtml())
                ->addFilter(
                    new Filter\BadWords());

        $this->addElement($content);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * override method, remove public question selector if not allowed
     *
     * @param array $data form data
     * @return $this
     */
    public function setData(array $data = null)
    {
        $acceptPublicQuestions = false;
        if (!isset($data['accept_public_questions'])) {
            $this->removeElement('accept_public_questions');
        }
        else {
            $acceptPublicQuestions = $data['accept_public_questions'];
        }

        if (!empty($data['topic_id']) && empty($data['title'])) {
            $data['title'] = $this->_messaging->generateMessageReplyTitle($data['topic_id']);
        }

        if (!$acceptPublicQuestions) {
            $this->removeElement('public_question');
        }

        parent::setData($data);

        return $this;
    }

}