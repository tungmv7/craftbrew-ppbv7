<?php

/**
 *
 * PHP Pro Bid $Id$ Zb/bDKYuWxik7vwQnySMV3fNf1pnwmDOcl2FGQFkb/Y=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * generate and download csv file class
 */

namespace Ppb\Http;

use Cube\Controller\Response\AbstractResponse;

class GenerateCSV extends AbstractResponse
{
    /**
     *
     * name of the file that will be generated
     *
     * @var string
     */
    protected $_fileName;

    /**
     *
     * first row of the csv file (column descriptions)
     *
     * @var array
     */
    protected $_heading = array();

    /**
     *
     * csv data
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * class constructor
     *
     * @param string $fileName
     */
    public function __construct($fileName = null)
    {
        if ($fileName !== null) {
            $this->setFileName($fileName);
        }
    }

    /**
     *
     * set file name
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;

        return $this;
    }

    /**
     *
     * get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     *
     * get heading array
     *
     * @return array
     */
    public function getHeading()
    {
        return $this->_heading;
    }

    /**
     *
     * set heading array
     *
     * @param array $heading
     *
     * @return $this
     */
    public function setHeading($heading)
    {
        $this->_heading = $heading;

        return $this;
    }

    /**
     *
     * get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * set data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     *
     * generate csv file from array and send it to download
     *
     * @return void
     */
    public function send()
    {
        if (empty($this->_fileName)) {
            throw new \InvalidArgumentException("A file name must be set.");
        }

        $this->setHeader('Content-Type: text/csv')
            ->addHeader('Content-Disposition: attachment; filename="' . $this->_fileName . '"')
            ->addHeader('Pragma: no-cache')
            ->addHeader('Expires: 0');

        $output = fopen("php://output", "w");

        if (!empty($this->_heading)) {
            fputcsv($output, $this->_heading);
        }

        foreach ($this->_data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);

        parent::send();

        exit(0);
    }

}

