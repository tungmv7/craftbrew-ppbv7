<?php

/**
 *
 * PHP Pro Bid $Id$ fkI0siMe0JC9wnxG3IYF6o+SHcsAEFS3BV2qnVtB8Ic=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * reputation post form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
        Ppb\Service\Reputation,
        Cube\Validate,
        Ppb\Filter;

class ReputationPost extends AbstractBaseForm
{

    const BTN_SUBMIT = 'reputation_post';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Proceed',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);


        $this->setMethod(self::METHOD_POST);

        $ids = $this->createElement('hidden', 'id');
        $this->addElement($ids);

        $score = $this->createElement('radio', 'score');
        $score->setLabel('Rate')
                ->setSubtitle('Post Feedback')
                ->setMultiOptions(Reputation::$scores)
                ->setRequired();
        $this->addElement($score);

        $comments = $this->createElement('text', 'comments');
        $comments->setLabel('Comments')
                ->setAttributes(array(
                    'class' => 'form-control',
                ))
                ->setRequired()
                ->addValidator(
                    new Validate\NoHtml())
                ->addFilter(
                    new Filter\BadWords());
        $this->addElement($comments);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}