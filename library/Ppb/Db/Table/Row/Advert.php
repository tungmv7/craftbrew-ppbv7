<?php

/**
 *
 * PHP Pro Bid $Id$ FcOWpG2sS1u3w6zMSaC9k5E8UJbB6u70wubbZyF/emg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * adverts table row object model
 */

namespace Ppb\Db\Table\Row;

class Advert extends AbstractRow
{
    /**
     *
     * count number of views
     *
     * @return $this
     */
    public function addView()
    {
        $nbViews = $this->getData('nb_views') + 1;
        $this->save(array(
            'nb_views' => $nbViews,
        ));

        return $this;
    }

    /**
     *
     * count number of clicks (image adverts only)
     *
     * @return $this
     */
    public function addClick()
    {
        $nbClicks = $this->getData('nb_clicks') + 1;
        $this->save(array(
            'nb_clicks' => $nbClicks,
        ));

        return $this;
    }

    /**
     *
     * generate advert redirect url
     *
     * @return array
     */
    public function link()
    {
        return array(
            'module'     => 'app',
            'controller' => 'index',
            'action'     => 'advert-redirect',
            'id'         => $this->getData('id')
        );
    }

}

