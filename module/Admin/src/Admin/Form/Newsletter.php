<?php

/**
 *
 * PHP Pro Bid $Id$ q+wf9qM32Ti7FHavGlfP/S4GFkZ/BRPIuqjdFux+2yw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * newsletter form
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
        Cube\Validate;

class Newsletter extends AbstractBaseForm
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

        $title = $this->createElement('text', 'title');
        $title->setLabel('Title')
                ->setDescription('Enter the title of the newsletter.')
                ->setAttributes(array(
                    'class' => 'form-control input-xlarge',
                ))
                ->setRequired()
                ->setValidators(array(
                    'NoHtml',
                    array('StringLength', array(null, 255)),
                ));
        $this->addElement($title);

        $content = $this->createElement('\\Ppb\\Form\\Element\\Wysiwyg', 'content');
        $content->setLabel('Content')
                ->setDescription('Enter the content of the newsletter. Html is allowed.')
                ->setAttributes(
                    array(
                        'rows'  => 8,
                        'class' => 'form-control'
                    )
                )
                ->setRequired();
        $this->addElement($content);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * will generate the edit form
     *
     * @param int $id
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Newsletter - ID: #%s'), $id));
            $this->setTitle('Edit Newsletter - ID: #' . $id);
        }

        return $this;
    }
}