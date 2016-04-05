<?php

/**
 *
 * PHP Pro Bid $Id$ 2zjd39SKWxvF4OXQ4yj/va+GZsY1en4LySaA9lU7gqI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * wysiwyg custom element
 *
 * uses redactor
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Textarea,
    Cube\Controller\Front;

class Wysiwyg extends Textarea
{

    const ELEMENT_CLASS = 'wysiwyg';
    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'wysiwyg';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $baseUrl . '/js/redactor/redactor.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/redactor/redactor.min.js"></script>');

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $('." . self::ELEMENT_CLASS . "').redactor({ " . "\n"
            . "     convertDivs: false, " . "\n"
            . "     minHeight: 200 " . "\n"
            . " }); " . "\n"
            . "</script>");

        $this->addAttribute('class', self::ELEMENT_CLASS);
    }
}

