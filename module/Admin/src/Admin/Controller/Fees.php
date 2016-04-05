<?php

/**
 *
 * PHP Pro Bid $Id$ txRZDVBPG5TvTXNQnw6gzKCOOkcE+1275KfpIrhU+7k=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Admin\Form;

class Fees extends AbstractAction
{

    /**
     *
     * form fields that are to be skipped when saving data
     *
     * @var array
     */
    protected $_skipFields = array('name', 'csrf', 'submit');

    public function Index()
    {
        $feeName = $this->getRequest()->getParam('name');
        $categoryId = $this->getRequest()->getParam('category_id');
        $type = $this->getRequest()->getParam('type');

        if ($feeName !== null) {

            $feesService = new Service\Fees();
            $feesService->setFeeName($feeName)
                ->setType($type);

            $formIds = array();
            array_push($formIds, $feeName);
            if ($feesService->hasTiers()) {
                array_push($formIds, 'tiers');
            }

            if (!$feesService->hasNoCategory()) {
                array_push($formIds, 'fees_category');
            }

            $form = new Form\Settings($formIds);

            if (in_array($feeName, $feesService->getFeesTiers())) {
                $form->setPartial('forms/fees-tiers.phtml');
            }

            if ($this->getRequest()->isPost()) {
                $params = $feesService->preparePostParams(
                    $this->getRequest()->getParams());

                $form->setData($params);

                if ($form->isValid() === true) {

                    foreach ($this->_skipFields as $key) {
                        if (array_key_exists($key, $params)) {
                            unset($params[$key]);
                        }
                    }

                    $delete = array();
                    if (isset($params['delete'])) {
                        $delete = $params['delete'];
                    }

                    $feesService->save($params)->delete($delete);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The settings have been saved.'),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }

            $form->setData(
                $feesService->getData(null, $categoryId));

            return array(
                'form'     => $form,
                'feeName'  => $feeName,
                'messages' => $this->_flashMessenger->getMessages()
            );
        }
        else {
            return array();
        }
    }

    public function Gateways()
    {
        $form = new Form\PaymentGateways();
        $service = new Service\Table\PaymentGateways();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData(
                array(
                    'csrf' => $this->getRequest()->getParam('csrf')));

            if ($form->isValid() === true) {

                foreach ($this->_skipFields as $key) {
                    if (array_key_exists($key, $params)) {
                        unset($params[$key]);
                    }
                }

                $service->save($params);


                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        $form->setData(
            $service->getData());

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

}