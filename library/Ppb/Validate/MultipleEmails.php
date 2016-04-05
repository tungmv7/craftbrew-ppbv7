<?php

/**
 *
 * PHP Pro Bid $Id$ bUjT5+neO/S4dW+AbsPXUyajeVBae+G4AhXYrW4gEr4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */
/**
 * multiple email addresses validator class
 */

namespace Ppb\Validate;

use Cube\Validate\Email;

class MultipleEmails extends Email
{

    protected $_message = "'%s' must contain one or multiple valid email addresses.";

    /**
     *
     * emails separator
     *
     * @var string
     */
    protected $_separator = ',';

    /**
     *
     * set emails separator
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;

        return $this;
    }

    /**
     *
     * get emails separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }


    /**
     *
     * checks if the variable contains a valid email address
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $separator = $this->getSeparator();

        $value = $this->getValue();
        $emails = explode($separator, $value);

        foreach ($emails as $email) {
            parent::setValue(
                trim($email));

            if (!parent::isValid()) {
                return false;
            }
        }

        return true;
    }

}

