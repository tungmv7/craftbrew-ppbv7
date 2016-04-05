<?php

/**
 *
 * PHP Pro Bid $Id$ Qf0C8Dr3+ZbhWERZaF2lG/4X6+mpu5w1qtwzZF2vR6I=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * listing payment methods validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Cube\Controller\Front;

class PaymentMethods extends AbstractValidate
{

    protected $_message = "You must select at least one method of payment.";

    protected $_keys = array(
        'direct_payment', 'offline_payment'
    );

    /**
     *
     * set request keys array
     *
     * @param array $keys
     */
    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    /**
     * get request keys array
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->_keys;
    }


    /**
     *
     * checks at least one method of payment has been selected
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $request = Front::getInstance()->getRequest();

        $values = array();
        foreach ($this->_keys as $key) {
            $array = $request->getParam($key);
            if (is_array($array)) {
                $values = array_merge($values, $request->getParam($key));
            }
            else if (is_string($array)) {
                $values[] = $array;
            }
        }

        $values = array_filter($values);

        if (count($values) > 0) {
            return true;
        }

        return false;
    }

}

