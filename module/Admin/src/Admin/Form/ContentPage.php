<?php

/**
 *
 * PHP Pro Bid $Id$ vijNtslHXBW6dljG1L3IzXMAb8CLf8Wqk5QHQiP/s1U=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * content page form
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Table\Relational\ContentSections as ContentSectionsService,
    Cube\Validate;

class ContentPage extends AbstractBaseForm
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

        $contentSectionsService = new ContentSectionsService();

        $section = $this->createElement('select', 'section_id');
        $section->setLabel('Section')
            ->setDescription('Select the section this page belongs to.')
            ->setMultiOptions(
                $contentSectionsService->getMultiOptions(null, null, false, true)
            )
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($section);

        $language = $this->createElement('select', 'language');
        $language->setLabel('Language')
            ->setDescription('Select the language this page will appear for.')
            ->setMultiOptions(\Ppb\Utility::getLanguages())
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($language);

        $title = $this->createElement('text', 'title');
        $title->setLabel('Title')
            ->setDescription('(Optional) Enter the title of the page.')
            ->setAttributes(array(
                'class' => 'form-control input-xlarge',
            ))
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($title);

        $content = $this->createElement('\\Ppb\\Form\\Element\\Wysiwyg', 'content');
        $content->setLabel('Content')
            ->setDescription('Enter the content for this page.<br>Allowed code: <br>'
                . '<%=action:{action}.{controller}.{module}%> <br>'
                . '<%=url:{param-key},{param-value};{param-key},{param-value};...%> <br>'
                . '<%=href:{uri}%>')
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
     * will generate the edit content page form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Content Page - ID: #%s'), $id));
        }

        return $this;
    }
}