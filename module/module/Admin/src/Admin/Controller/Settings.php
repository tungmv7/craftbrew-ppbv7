<?php

/**
 *
 * PHP Pro Bid $Id$ tJPMcn3M2bYE/5qU0galTwCyZobNLpmvTu7N/RVU45E=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Admin\Form\Settings as SettingsForm,
    Ppb\Service\Settings as SettingsService;

class Settings extends AbstractAction
{

    protected $_skipFields = array('page', 'csrf', 'submit', 'nb_uploads', 'file-site_logo_path');

    public function Index()
    {
        $page = $this->getRequest()->getParam('page', 'site_setup');

        $form = new SettingsForm($page);
        $settingsService = new SettingsService();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {

                foreach ($this->_skipFields as $key) {
                    if (array_key_exists($key, $params)) {
                        unset($params[$key]);
                    }
                }

                $settingsService->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => 'The settings have been saved.',
                    'class' => 'alert-success',
                ));

                switch ($page) {
                    case 'products_settings':
                        $enableProducts = $this->getRequest()->getParam('enable_products');
                        if (!$enableProducts) {
                            $data = array('enable_stores' => 0);
                            $settingsService->save($data);
                        }
                        break;
                }

                // redirect after save to avoid multiple writes on refresh
                $redirectUrl = $this->getRequest()->getBaseUrl() .
                    $this->getRequest()->getRequestUri();
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                $settingsService->get());
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

}

