<?php

/**
 *
 * Cube Framework $Id$ WQmP1Gi38y3UwTjtwzDSEgOdlpBCqV6lRbeGYtZe2Sc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * download file class
 */

namespace Cube\Http;

use Cube\Controller\Response\AbstractResponse;

class Download extends AbstractResponse
{
    /**
     *
     * real path to the file or false if the file does not exist
     *
     * @var string|false
     */
    protected $_filePath = false;

    /**
     *
     * class constructor
     *
     * @param string|null $file
     */
    public function __construct($file = null)
    {
        if ($file !== null) {
            $this->setFilePath($file);
        }
    }

    /**
     *
     * set real path to the file to be downloaded
     *
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->_filePath = realpath($filePath);

        return $this;
    }

    /**
     *
     * get real file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filePath;
    }

    /**
     *
     * if the file exists, create a download request and stop all other executions
     *
     * @return null|mixed
     */
    public function send()
    {
        if (($filePath = $this->getFilePath()) !== false) {
            $this->setHeader('Content-Type: application/octet-stream')
                    ->addHeader('Content-Disposition: attachment; filename="' . basename($filePath) . '"')
                    ->addHeader('Content-Length: ' . filesize($filePath));

            $this->setBody(
                readfile($filePath));

            parent::send();

            exit(0);
        }

        return null;
    }

}

