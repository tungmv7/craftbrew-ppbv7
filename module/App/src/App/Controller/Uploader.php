<?php

/**
 *
 * PHP Pro Bid $Id$ 1nwpWyxHlnLpAauy/DK5wick4qHwVhVNKnXl4e8G3Iw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * thumbnail generator controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Cube\Crypt,
    Ppb\Model\Uploader as UploaderEngine,
    Ppb\Form\Element\MultiUpload;

class Uploader extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * uploader class
     *
     * @var \Ppb\Model\Uploader
     */
    protected $_uploader;

    /**
     *
     * number of current uploads in the widget
     *
     * @var int
     */
    protected $_nbUploads;

    public function init()
    {
        $this->_uploader = new UploaderEngine();

        $this->_view = new View();

        $this->_nbUploads = $this->getRequest()->getParam('nbUploads');

    }

    public function Upload()
    {
        $output = array();

        $translate = $this->getTranslate();

        foreach ($_FILES as $files) {
            if (isset($files['name'])) {
                if (is_array($files['name'])) {
                    foreach ($files['name'] as $key => $fileName) {
                        $fileSize = $files['size'][$key];
                        $output[] = $this->_processFile($fileName, $files['tmp_name'][$key], $fileSize);
                    }
                }
                else {
                    $output[] = $this->_processFile($files['name'], $files['tmp_name'], $files['size']);
                }
            }
            else {
                $output[] = $result = array(
                    'name'  => null,
                    'size'  => null,
                    'error' => $translate->_('There are no files to upload.')
                );
            }
        }

//        $this->getResponse()->setHeader('Content-Type: application/json'); // doesnt work with IE9 and below
        $this->getResponse()->setHeader('Content-Type: text/plain');


        /**
         * we will return the file names and locations or false if a file was not saved, which can then be parsed by the jquery script
         */
        $this->_view->setContent(
            json_encode(array('files' => $output)));

        return $this->_view;
    }

    public function Success()
    {
        $name = $this->getRequest()->getParam('element');
        $value = $this->getRequest()->getParam('image');
        $multiple = (bool)$this->getRequest()->getParam('multiple');

        $element = new MultiUpload($name);
        $element->setValue($value)
            ->setMultiple($multiple);

        $this->_view->setContent(
            $element->renderThumb());

        return $this->_view;
    }

    public function Remove()
    {
        $options = Front::getInstance()->getOption('session');

        $crypt = new Crypt();
        $crypt->setKey($options['secret']);

        $fileName = $this->getRequest()->getParam('value');
        $uploadType = $this->getRequest()->getParam('element');
        $encryptionKey = str_replace(' ', '+', $_REQUEST['key']);

        $array = explode(
            MultiUpload::KEY_SEPARATOR, $crypt->decrypt($encryptionKey));
        $encryptedFileName = isset($array[0]) ? $array[0] : null;

        if ($encryptedFileName == $fileName) {
            $this->_uploader->remove($fileName, $uploadType);
            $this->_view->setContent(
                $this->getTranslate()->_("The file has been removed"));
        }

        return $this->_view;
    }

    private function _processFile($fileName, $tmpName, $fileSize)
    {
        $uploadType = $this->getRequest()->getParam('uploadType');
        $fileSizeLimit = $this->getRequest()->getParam('fileSizeLimit');
        $uploadLimit = $this->getRequest()->getParam('uploadLimit');
        $watermark = $this->getRequest()->getParam('watermark');
        $acceptFileTypes = urldecode($_REQUEST['acceptFileTypes']);

        $translate = $this->getTranslate();

        $fileSizeLimitDisplay = number_format(($fileSizeLimit / 1024), 0);

        if (!empty($acceptFileTypes) && !preg_match($acceptFileTypes, $fileName)) {
            $result = array(
                'name'  => $fileName,
                'size'  => $fileSize,
                'error' => sprintf(
                    $translate->_('Allowed extensions: %s'),
                    trim(str_replace('|', ', ', preg_replace('/[^a-zA-Z0-9\|\?]+/', '', substr($acceptFileTypes, 0, -1)))), ',')
            );
        }
        else if ($fileSize <= $fileSizeLimit && $this->_nbUploads < $uploadLimit) {
            $error = null;

            $name = $this->_uploader->upload($tmpName, $fileName, $uploadType, $watermark);
            if ($name === false) {
                $name = $fileName;
                $error = $translate->_("Please try again or contact the administrator.");
            }
            else {
                $this->_nbUploads++;
            }

            $result = array(
                'name'  => $name,
                'size'  => $fileSize,
                'error' => $error,
            );
        }
        else if ($fileSize > $fileSizeLimit) {
            $fileSizeDisplay = number_format(($fileSize / 1024), 2);
            $result = array(
                'name'  => $fileName,
                'size'  => $fileSize,
                'error' => sprintf(
                    $translate->_('The file size is %sKB, and exceeds the maximum allowed limit of %sKB'),
                    $fileSizeDisplay, $fileSizeLimitDisplay)
            );
        }
        else if ($this->_nbUploads >= $uploadLimit) {
            $result = array(
                'name'  => $fileName,
                'size'  => $fileSize,
                'error' => sprintf(
                    $translate->_('The maximum number of uploads allowed (%s) has been reached.'),
                    $this->_nbUploads),
            );
        }
        else { // other undocumented error.
            $result = array(
                'name'  => null,
                'size'  => null,
                'error' => $translate->_('An unknown file upload error has occurred.')
            );
        }

        return $result;
    }

}

