<?php

/**
 *
 * PHP Pro Bid $Id$ jxptxSH28IouG6cb4dDFc5YciWLoxqHteqD/JYedeCY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * bulk lister form
 */
namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Form\Element\MultiUpload,
    Cube\Validate;

class BulkLister extends AbstractBaseForm
{

    const BTN_SUBMIT = 'upload_bulk';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Upload',
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

        $translate = $this->getTranslate();

        $fileUpload = new MultiUpload('csv');
        $fileUpload->setLabel('Upload Bulk File')
            ->setDescription('Select the CSV bulk file to be uploaded.')
            ->setRequired()
            ->setCustomData(array(
                'buttonText'      => $translate->_('Select File'),
                'acceptFileTypes' => '/(\.|\/)(csv)$/i',
                'formData'        => array(
                    'uploadType'    => 'bulk_lister',
                    'fileSizeLimit' => (20 * 1024 * 1024), // 20 MB.
                    'uploadLimit'   => 1,
                ),
            ));
        $this->addElement($fileUpload);

        $uploadAs = $this->createElement('radio', 'upload_as')
            ->setLabel('Upload As')
            ->setDescription('Select how the listings will be uploaded as.')
            ->setValue('bulk')
            ->setMultiOptions(array(
                'bulk' => $translate->_('Drafts'),
                'live' => $translate->_('Live'),
            ));
        $this->addElement($uploadAs);


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}