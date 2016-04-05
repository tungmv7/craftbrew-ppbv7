<?php

/**
 *
 * PHP Pro Bid $Id$ fh47RtWmMjT3e4XQF40qrqol4HtlGlxNAbkb4VgATnM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing item postage validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Ppb\Form\Element\ListingPostageLocations;

class ItemPostage extends AbstractValidate
{

    const NO_POSTAGE = 1;
    const PRICE_NOT_NUMERIC = 2;

    protected $_messages = array(
        self::NO_POSTAGE        => "'%s' is required and cannot be empty.",
        self::PRICE_NOT_NUMERIC => "'%s': the price fields only accept numeric values.",
    );

    /**
     *
     * checks if at least one item postage option has been entered and
     * if the price fields contain numeric values
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (isset($value[ListingPostageLocations::FIELD_METHOD])) {
            $values = array_filter($value[ListingPostageLocations::FIELD_METHOD]);

            if (count($values) > 0) {
                $prices = array_filter($value[ListingPostageLocations::FIELD_PRICE]);
                foreach ($prices as $price) {
                    if (!is_numeric($price)) {
                        $this->setMessage($this->_messages[self::PRICE_NOT_NUMERIC]);

                        return false;
                    }
                }

                return true;
            }
        }

        $this->setMessage($this->_messages[self::NO_POSTAGE]);

        return false;
    }

}

