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
 * bulk lister service class
 */

namespace Ppb\Service\Listings;

use Ppb\Service\Listings as ListingsService,
    Ppb\Model\Elements,
    Ppb\Http\GenerateCSV,
    External\ParseCSV,
    External\ForceUTF8;

class BulkLister extends ListingsService
{

    /**
     * bulk lister file max size
     */
    const MAX_SIZE = 20; // 20 MB
    const ARRAY_SEPARATOR = '||';

    protected $_jsonColumns = array('stock_levels', 'postage');

    protected $_forceUtf8Columns = array('name', 'subtitle', 'description', 'item_selected_carriers');

    protected $_prefilledFields = array();

    /**
     *
     * get prefilled fields
     *
     * @return array
     */
    public function getPrefilledFields()
    {
        return $this->_prefilledFields;
    }

    /**
     *
     * set prefilled fields
     *
     * @param array $prefilledFields
     *
     * @return $this
     */
    public function setPrefilledFields($prefilledFields)
    {
        $this->_prefilledFields = $prefilledFields;

        return $this;
    }

    /**
     * we will have a generate sample file method
     */
    /**
     * we will have a save all method that will call the parent save() method for each row and do post save actions for each row
     * if in account mode, it will output a combined message for all listed items together with the total balance debit added
     * if in live payment, it will output a message with the number of items activated, and the number of items that will need the
     * setup fee to be paid
     */
    /**
     * we will have a method that will validate each uploaded row based on the Elements\Listing model and return true if all are valid
     * or return an array of error messages (error/row #/row item title)
     */

    /**
     *
     * get listing model elements that apply to the bulk file structure
     *
     * @return array
     */
    public function getBulkElements()
    {
        $model = new Elements\Listing('bulk');

        $listingElements = $model->getElements();

        $elements = array();

        foreach ($listingElements as $element) {
            $formId = (isset($element['form_id'])) ? $element['form_id'] : array();
            if (in_array('bulk', (array)$formId)) {
                if ($element['element'] !== false) {
                    if (isset($element['bulk']['multiOptions'])) {
                        $element['multiOptions'] = $element['bulk']['multiOptions'];
                    }

                    if (isset($element['bulk']['label'])) {
                        $element['label'] = $element['bulk']['label'];
                    }

                    if (isset($element['bulk']['sample'])) {
                        $element['sample'] = $element['bulk']['sample'];
                    }
                    else if (isset($element['value'])) {
                        $element['sample'] = $element['value'];
                    }
                    else if (isset($element['multiOptions'])) {
                        $multiOptions = array_keys($element['multiOptions']);
                        $element['sample'] = reset($multiOptions);
                    }
                    else {
                        $element['sample'] = null;
                    }

                    array_push($elements, $element);
                }
            }
        }

        return $elements;
    }

    /**
     *
     * generate and download sample csv file
     *
     * @return void
     */
    public function downloadSampleFile()
    {
        $elements = $this->getBulkElements();

        $heading = array_map(function ($element) {
            return $element['id'];
        }, $elements);

        $data = array_map(function ($element) {
            return $element['sample'];
        }, $elements);

        $download = new GenerateCSV('bulk-lister-sample.csv');
        $download->setHeading($heading)
            ->setData(array(
                $data))
            ->send();
    }

    /**
     *
     * parse csv file and return an array containing data formatted in order to be parsed by the listing form.
     *
     * using external ParseCSV class, will properly import descriptions with new lines in them etc
     *
     * @param string $fileName
     *
     * @return array
     */
    public function parseCSV($fileName)
    {
        $data = array();

        $filePath = \Ppb\Utility::getPath('uploads') . '/' . $fileName;

        $csv = new ParseCSV($filePath);

        if (count($csv->data) > 0) {
            $data = $csv->data;

            foreach ($data as $id => $row) {
                foreach ($row as $key => $value) {
                    if (in_array($key, $this->_forceUtf8Columns)) {
                        $data[$id][$key] = $value = ForceUTF8\Encoding::toUTF8($value);
                    }

                    if (!is_array($value)) {
                        // we use json for columns that accept arrays
                        if (in_array($key, $this->_jsonColumns)) {
                            $data[$id][$key] = $this->_jsonDecode($value);
                        }
                        else if (stristr($value, self::ARRAY_SEPARATOR)) {
                            $data[$id][$key] = explode(self::ARRAY_SEPARATOR, $value);
                        }
                    }
                }
            }
        }


        return $data;
    }

    /**
     *
     * decode json or return the original string if it cannot be decoded
     *
     * @param $string
     *
     * @return mixed
     */
    protected function _jsonDecode($string)
    {
        $array = json_decode($string, true);

        return ($array !== null) ? $array : $string;
    }
}

