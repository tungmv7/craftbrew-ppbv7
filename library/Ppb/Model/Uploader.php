<?php

/**
 *
 * PHP Pro Bid $Id$ 1nwpWyxHlnLpAauy/DK5wick4qHwVhVNKnXl4e8G3Iw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * A class used to upload and process uploaded files
 *
 * If an uploaded file is an image, the image will be resized based on settings from the config file.
 * If a file is not an image, it is only uploaded.
 *
 * All files will be renamed to avoid file name conflicts.
 *
 */

namespace Ppb\Model;

use Cube\Controller\Front,
    Cube\Db\Expr,
    Ppb\Service,
    Ppb\View\Helper\Thumbnail as ThumbnailHelper;

class Uploader
{

    /**
     * prohibited extension replacement
     */
    const PROHIBITED_EXTENSION_REPLACEMENT = 'invalid';

    /**
     *
     * prohibited extensions
     *
     * @var array
     */
    protected $_prohibitedExtensions = array(
        'php', 'exe', 'htm', 'js', 'pl', 'cgi', 'wml', 'perl'
    );

    /**
     *
     * the image upload process
     * if we have an image, we will resize it to a default maximum size
     *
     * @param string      $tempFile
     * @param string      $rawFileName
     * @param string      $uploadType
     * @param string|null $watermarkText watermark text
     *
     * @return string|null     return the file name or null if the upload was unsuccessful
     */
    public function upload($tempFile, $rawFileName, $uploadType = null, $watermarkText = null)
    {
        $fileName = $this->_generateFileName($rawFileName, $uploadType);
        $targetFile = $this->_generateTargetPath($fileName, $uploadType);

        $result = move_uploaded_file($tempFile, $targetFile);

        if ($result) {
            if ($this->_isImage($targetFile)) {
                $thumbnail = new ThumbnailHelper();
                $thumbnail->setWidth(ThumbnailHelper::MAX_WIDTH)
                    ->setHeight(false);

                $this->_imageSmartRotate($targetFile);

                list($imgWidth, $imgHeight, $imgType) = @getimagesize($targetFile);

                if ($imgWidth > ThumbnailHelper::MAX_WIDTH) {
                    $output = $thumbnail->createResizedImage($targetFile, false);

                    touch($targetFile);
                    imagepng($output, $targetFile);
                    imagedestroy($output);
                }
            }

            if (!empty($watermarkText)) {
                $this->_addWatermark($targetFile, $watermarkText);
            }

            return $fileName;
        }

        return false;
    }

    /**
     *
     * remove a local file
     * only delete if the file is not used anymore.
     * TODO: this method will not work properly when editing a listing
     *
     * @param string $fileName
     * @param string $uploadType
     *
     * @return $this
     */
    public function remove($fileName, $uploadType)
    {
        $listingsMediaService = new Service\ListingsMedia();

        $uploadType = preg_replace('#[^a-z]+#i', '', $uploadType);

        $select = $listingsMediaService->getTable()
            ->select(array('nb_rows' => new Expr('count(*)')))
            ->where('value = ?', $fileName)
            ->where('type = ?', $uploadType);

        $nbUploads = $listingsMediaService->getTable()->getAdapter()->fetchOne($select);

        $targetPath = $this->_generateTargetPath($fileName, $uploadType);

        // remove cache files for images
        if ($uploadType == 'image') {
            $directory = __DIR__ . '/../../../' . \Ppb\Utility::getFolder('cache');
            $handler = opendir($directory);

            $pathInfo = pathinfo($targetPath);
            $baseName = (isset($pathInfo['filename'])) ? $pathInfo['filename'] : null;
            if ($baseName) {
                while ($file = readdir($handler)) {
                    if ($file != "." && $file != "..") {
                        if (strpos($file, $baseName) === 0) {
                            @unlink($directory . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                }
            }
        }

        // remove file
        if (!$nbUploads) {
            @unlink($targetPath);
        }

        return $this;
    }

    /**
     *
     * get the target path of an uploaded file
     *
     * @param string $fileName
     * @param string $uploadType
     *
     * @return string
     */
    protected function _generateTargetPath($fileName, $uploadType = null)
    {
        // for now we only have images and videos, that go in the "uploads" folder
        $uploadType = preg_replace('#[^a-z]+#i', '', $uploadType);

        switch ($uploadType) {
            case 'download':
                $settings = Front::getInstance()->getBootstrap()->getResource('settings');
                $targetPath = __DIR__ . '/../../../' . $settings['digital_downloads_folder'] . DIRECTORY_SEPARATOR;
                break;
            default:
                $targetPath = \Ppb\Utility::getPath('uploads') . DIRECTORY_SEPARATOR;
                break;
        }

        return str_replace('//', '/', $targetPath) . $fileName;
    }

    /**
     *
     * set a unique file name for the uploaded file, so that no files are overwritten
     *
     * @param string $rawFileName
     * @param string $uploadType
     *
     * @return string
     */
    protected function _generateFileName($rawFileName, $uploadType = null)
    {
        $pathInfo = pathinfo($rawFileName);
        $tempName = preg_replace("/[^a-zA-Z0-9_-]/", '', $pathInfo['filename']);
        $fileExtension = $pathInfo['extension'];

        foreach ($this->_prohibitedExtensions as $prohibitedExtension) {
            if (stristr($fileExtension, $prohibitedExtension)) {
                $fileExtension = self::PROHIBITED_EXTENSION_REPLACEMENT;
            }
        }

        switch ($uploadType) {
            case 'download':
                $tempName .= '-' . (int)(microtime(true) * 100);
                break;
        }

        $fileName = $tempName . '.' . $fileExtension;

        while (file_exists($this->_generateTargetPath($fileName))) {
            if (preg_match('#\((\d+)\)#', $fileName, $matches)) {
                $fileName = preg_replace('#\((\d+)\)#', '(' . ($matches[1] + 1) . ')', $fileName);
            }
            else {
                $fileName = $tempName . '-(1)' . '.' . $fileExtension;
            }

        }

        return $fileName;
    }

    /**
     *
     * image create function based on source image type
     *
     * @param string $fileName
     * @param null   $mime
     *
     * @return mixed
     */
    protected function _imageCreateFunction($fileName, $mime = null)
    {
        if ($mime === null) {
            $imgInfo = @getimagesize($fileName);
            $mime = $imgInfo['mime'];
        }

        switch ($mime) {
            case 'image/gif':
                $imageCreateFunction = 'ImageCreateFromGIF';
                break;
            case 'image/png':
                $imageCreateFunction = 'ImageCreateFromPNG';
                break;
            default:
                $imageCreateFunction = 'ImageCreateFromJPEG';
                break;
        }

        return $imageCreateFunction($fileName);
    }


    /**
     *
     * rotate uploaded image automatically based on exif orientation
     *
     * @param string $fileName
     *
     * @return $this
     */
    protected function _imageSmartRotate($fileName)
    {
        $output = null;

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($fileName);
            if (!empty($exif['Orientation'])) {
                $image = $this->_imageCreateFunction($fileName);

                switch ($exif['Orientation']) {
                    case 3:
                        $output = imagerotate($image, 180, 255);
                        break;
                    case 6:
                        $output = imagerotate($image, -90, 255);
                        break;
                    case 8:
                        $output = imagerotate($image, 90, 255);
                        break;
                }

                if ($output !== null) {
                    touch($fileName);
                    imagepng($output, $fileName);
                    imagedestroy($output);
                }
            }
        }

        return $this;
    }

    /**
     *
     * add watermark to the image that has been uploaded
     *
     * @param $fileName
     * @param $watermarkText
     *
     * @return $this
     */
    protected function _addWatermark($fileName, $watermarkText)
    {
        if ($this->_isImage($fileName)) {
            $output = $this->_imageCreateFunction($fileName);

            // Get identifier for white
            $white = imagecolorallocate($output, 255, 255, 255);

            // Add text to image
            imagestring($output, 20, 5, imagesy($output) - 20, $watermarkText, $white);

            // Save the image to file and free memory
            touch($fileName);
            imagepng($output, $fileName);
            imagedestroy($output);
        }

        return $this;

    }

    /**
     *
     * check whether the uploaded file is an image
     *
     * @param string $file
     *
     * @return bool
     */
    protected function _isImage($file)
    {
        $fileInfo = @getimagesize($file);

        if ($fileInfo === false || $fileInfo[2] > 3) {
            return false;
        }

        return true;
    }
}

