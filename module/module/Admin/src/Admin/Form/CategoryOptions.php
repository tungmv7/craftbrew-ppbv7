<?php

/**
 *
 * PHP Pro Bid $Id$ lMSvGe7auwcmH81QaEpjYT2Q7TWhWY5v1/yoaPpw8Tc=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * category options form
 * TODO: since this form is accessed through a pop-up, it currently is problematic to post it
 * TODO: need workaround on posting forms when in pop-ups (most likely will use an async solution).
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate;

class CategoryOptions extends AbstractBaseForm
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

        $translate = $this->getTranslate();

        $categoryLogo = $this->createElement('\\Ppb\\Form\\Element\\MultiUpload', 'logo_path');
        $categoryLogo->setLabel('Category Logo')
            ->setDescription('(Optional) Upload a logo for this category.')
            ->setCustomData(array(
                'buttonText'      => $translate->_('Select Logo'),
                'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                'formData'        => array(
                    'fileSizeLimit' => 5000000,
                    'uploadLimit'   => 1,
                )
            ));
        $this->addElement($categoryLogo);

        $metaTitle = $this->createElement('text', 'meta_title');
        $metaTitle->setLabel('Meta Title')
            ->setDescription('(Optional) Add a custom meta title for this category. If left empty, the meta title will be generated automatically.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setValidators(array(
                'NoHtml'
            ));
        $this->addElement($metaTitle);

        $metaDescription = $this->createElement('textarea', 'meta_description');
        $metaDescription->setLabel('Meta Description')
            ->setDescription('(Optional) Add meta description for this category. '
                . 'Your description should be no longer than 155 characters (including spaces)')
            ->setAttributes(
                array('class' => 'form-control')
            );
        $this->addElement($metaDescription);

        $htmlHeader = $this->createElement('textarea', 'html_header');
        $htmlHeader->setLabel('HTML Header')
            ->setDescription('(Optional) Add a custom html header for this category. '
                . 'The html code will be rendered when users are browsing the category.')
            ->setAttributes(
                array(
                    'class' => 'form-control',
                    'rows'  => 8,
                )
            );
        $this->addElement($htmlHeader);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/popup-form.phtml');
    }

}