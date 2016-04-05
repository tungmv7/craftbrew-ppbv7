<?php

/**
 *
 * PHP Pro Bid $Id$ FcOWpG2sS1u3w6zMSaC9k5E8UJbB6u70wubbZyF/emg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * advert form
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Advertising as AdvertisingService,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    Cube\Validate;

class Advert extends AbstractBaseForm
{

    const BTN_SUBMIT = 'btn_submit';

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
     * @param string $advertType (image or code - default: image)
     * @param string $action     the form's action
     */
    public function __construct($advertType = null, $action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        $id = $this->createElement('hidden', 'id');
        $id->setBodyCode("<script type=\"text/javascript\">
                $(document).on('change', '#advert-type', function() {
                    $(this).closest('form').submit();
                });
            </script>");
        $this->addElement($id);

        $title = $this->createElement('text', 'name');
        $title->setLabel('Advert Name')
            ->setDescription('Enter a name for your advert (for internal usage only).')
            ->setRequired()
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ))
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($title);

        $advertisingService = new AdvertisingService();

        $section = $this->createElement('select', 'section');
        $section->setLabel('Section')
            ->setDescription('Select the section this advert belongs to.')
            ->setMultiOptions(
                $advertisingService->getSections()
            )
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($section);

        $categoriesService = new CategoriesService();

        $categoriesIds = $this->createElement('\\Ppb\\Form\\Element\\ChznSelect', 'category_ids');
        $categoriesIds->setLabel('Select Categories')
            ->setDescription('(Optional) Choose for which categories this advert will be displayed.')
            ->setMultiOptions(
                $categoriesService->getMultiOptions(null, null, false, true))
            ->setAttributes(array(
                'style'            => 'width: 350px;',
                'data-placeholder' => 'Choose Categories...',
            ))
            ->setMultiple(true);
        $this->addElement($categoriesIds);

        $languagesMultiOptions = array_merge(array('' => $translate->_('All Languages')), \Ppb\Utility::getLanguages());
        $language = $this->createElement('select', 'language');
        $language->setLabel('Language')
            ->setDescription('Select the language this advert will appear for.')
            ->setMultiOptions($languagesMultiOptions)
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($language);

        $section = $this->createElement('select', 'type');
        $section->setLabel('Type')
            ->setDescription('Select the type of advert.')
            ->setMultiOptions(array(
                'image' => $translate->_('Image'),
                'code'  => $translate->_('Code'),
            ))
            ->setAttributes(array(
                'id'    => 'advert-type',
                'class' => 'form-control input-small',
            ));
        $this->addElement($section);


        if ($advertType == 'code') {
            $content = $this->createElement('textarea', 'content');
            $content->setLabel('Content')
                ->setDescription('Enter the code of the advert.')
                ->setAttributes(
                    array(
                        'rows'  => 16,
                        'class' => 'form-control textarea-code code-field')
                )
                ->setRequired();
            $this->addElement($content);
        }
        else {
            $advertImage = $this->createElement('\Ppb\Form\Element\MultiUpload', 'content');
            $advertImage->setLabel('Image')
                ->setDescription('Upload the advert image.')
                ->setRequired()
                ->setCustomData(array(
                    'buttonText'      => $translate->_('Select Image'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 10000000, // approx 10MB
                        'uploadLimit'   => 1,
                    ),
                ));
            $this->addElement($advertImage);

            $url = $this->createElement('text', 'url');
            $url->setLabel('Advert Url')
                ->setDescription('Enter a the url this advert will redirect to.')
                ->setRequired()
                ->setAttributes(array(
                    'class' => 'form-control input-medium',
                ))
                ->setValidators(array(
                    'Url',
                ));
            $this->addElement($url);
        }

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