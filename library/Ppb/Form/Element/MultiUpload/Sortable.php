<?php

/**
 *
 * PHP Pro Bid $Id$ 4oefZMBQlCyKrmvpfRnx683WmO28DagWLpR+s7EhH7A=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */

namespace Ppb\Form\Element\MultiUpload;

use Ppb\Form\Element\MultiUpload as MultiUploadElement;

class Sortable extends MultiUploadElement
{

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->_thumbDivId = $name . 'Sortable';

        $this->setBodyCode('<script type="text/javascript">
                    $("#' . $this->_thumbDivId . '").sortable();
                </script>');
    }

}

