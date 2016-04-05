<?php

/**
 *
 * PHP Pro Bid $Id$ jvpz4eh3ntPPRpCSYJsGYa5R3Hx1vxW7XF+qvgIt/QLXB2ptyFfCwFEsS2KSccbKU9bQUZlwwPZMv09DmUltKQ==
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * content section options form
 * TODO: need workaround on posting forms when in pop-ups (most likely will use an async solution).
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate;

class ContentSectionOptions extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save',
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

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $metaTitle = $this->createElement('text', 'meta_title');
        $metaTitle->setLabel('Meta Title')
            ->setDescription('(Optional) Add a custom meta title for this section. If left empty, the meta title will be generated automatically.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setValidators(array(
                'NoHtml'
            ));
        $this->addElement($metaTitle);

        $metaDescription = $this->createElement('textarea', 'meta_description');
        $metaDescription->setLabel('Meta Description')
            ->setDescription('(Optional) Add meta description for this section. '
                . 'Your description should be no longer than 155 characters (including spaces)')
            ->setAttributes(
                array('class' => 'form-control')
            );
        $this->addElement($metaDescription);


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/popup-form.phtml');
    }

}