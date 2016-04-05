<?php

/**
 *
 * PHP Pro Bid $Id$ X+KNrUJaao7PlFqpF9WQd1WTn5P3wAIak+JXunA8gv4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * members module - store management controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
        Members\Form,
        Ppb\Service;

class Store extends AbstractAction
{
    public function Setup()
    {
        $type = $this->getRequest()->getParam('type');

        $translate = $this->getTranslate();

        switch ($type) {
            case 'store-pages':
                $formId = array('store_pages');
                $formTitle = $translate->_('Store Pages');
                $redirect = 'pages';
                break;
            default:
                $formId = array('store_setup');
                $formTitle = $translate->_('Store Setup');
                $redirect = 'setup';
                break;
        }

        $form = new Form\Register($formId, null, $this->_user);

        $form->setData(array_merge($this->_user->getData(), $this->_user->getStoreSettings()))
                ->generateEditForm($this->_user['id']);

        if ($formTitle) {
            $form->setTitle($formTitle);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_user->updateStoreSettings($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved successfully.'),
                    'class' => 'alert-success',
                ));

                if ($redirect !== null) {
                    $this->_helper->redirector()->redirect($redirect);
                }
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline' => $form->getTitle(),
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
            'user'     => $this->_user,
        );
    }

    public function Pages()
    {
        $this->_forward('setup', null, null, array('type' => 'store-pages'));
    }

    public function Categories()
    {
        return array();
    }

    public function Disable()
    {
        if ($this->_user->getData('store_active')) {
            $this->_user->updateStoreSubscription(0, false, false);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The store has been disabled.'),
                'class' => 'alert-success',
            ));
        }

        $this->_forward('setup');
    }

}

