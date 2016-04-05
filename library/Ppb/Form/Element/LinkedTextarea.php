<?php

/**
 *
 * PHP Pro Bid $Id$ hGZCLmkDoBq/NcT7drF3SHHbuU/xE0J6mM7ZaTKQQRE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * linked textarea custom element
 *
 * used specifically for the languages editor admin page
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front;

class LinkedTextarea extends Element
{

    const ELEMENT_CLASS = 'linked-textarea';


    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'linkedTextarea';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $baseUrl . '/js/linedtextarea/linedtextarea.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/linedtextarea/linedtextarea.js"></script>');

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $(document).ready(function() { " . "\n"
            . "     $('." . self::ELEMENT_CLASS . "').linedtextarea();" . "\n"
            . " }); " . "\n"
            . "</script>"
        );

        $this->addAttribute('class', self::ELEMENT_CLASS);
    }

//    /**
//     *
//     * set script code in case the element is not part of a form
//     *
//     * @return $this
//     */
//    public function setScriptCode()
//    {
//        $view = Front::getInstance()->getBootstrap()->getResource('view');
//
//        /* @var \Cube\View\Helper\Script $helper */
//        $helper = $view->getHelper('script');
//
//        $headerCode = $this->getHeaderCode();
//        foreach ($headerCode as $code) {
//            $helper->addHeaderCode($code);
//        }
//        $bodyCode = $this->getBodyCode();
//        foreach ($bodyCode as $code) {
//            $helper->addBodyCode($code);
//        }
//
//        return $this;
//    }

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getValue();


        return '<div class="row">'
               . '<div class="col-sm-6">'
               . '<textarea name="' . $this->_name . 'Keys" readonly="readonly" '
               . $this->renderAttributes() . '>'
               . implode("\n", array_keys($value))
               . '</textarea>'
               . '</div>'
               . '<div class="col-sm-6">'
               . '<textarea name="' . $this->_name . '" '
               . $this->renderAttributes() . '>'
               . implode("\n", array_values($value))
               . '</textarea>'
               . '</div>'
               . '</div>';
    }
}

