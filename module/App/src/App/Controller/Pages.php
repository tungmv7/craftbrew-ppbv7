<?php

/**
 *
 * PHP Pro Bid $Id$ qVsFLFyE4hI7nEaEZpWKVBFSekpR2Sz4mh+L/1CHEHA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * custom pages controller
 * - will not be available with direct routing
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    App\Form;

class Pages extends AbstractAction
{

    public function Contact()
    {
        $form = new Form\Contact();

        if (count($this->_user) > 0) {
            $fullName = Front::getInstance()->getBootstrap()->getResource('view')->userDetails($this->_user)->displayFullName();
            $form->setData(array(
                'name'  => $fullName,
                'email' => $this->_user->getData('email'),
            ));
        }


        if ($form->isPost(
            $this->getRequest())
        ) {
            $form->setData($this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The email has been sent successfully.'),
                    'class' => 'alert-success',
                ));

                $form->clearElements();

                $mail = new \Admin\Model\Mail\Admin();
                $mail->contact($this->getRequest())->send();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function SiteFees()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $tab = $this->getRequest()->getParam('tab', 'general');

        $translate = $this->getTranslate();

        $tabs['general'] = array(
            'name' => $this->_('General'),
        );

        if ($this->_settings['enable_auctions'] || $this->_settings['enable_products']) {
            $tabs['listings'] = array(
                'name' => $this->_('Listings'),
            );
        }

        if ($this->_settings['enable_stores']) {
            $tabs['stores'] = array(
                'name' => $this->_('Stores'),
            );
        }

        $selectFeesCategories = "parent_id IS NULL AND custom_fees='1'";

        $storesSubscriptions = null;
        $fees = array();

        switch ($tab) {
            case 'listings':
                $services = array(
                    'ListingSetup',
                    'SaleTransaction',
                );

                $selectFeesCategories .= " AND enable_auctions='1'";

                foreach ($services as $name) {
                    $serviceName = '\\Ppb\\Service\\Fees\\' . $name;
                    /** @var \Ppb\Service\Fees $service */
                    $service = new $serviceName();

                    $select = $service->getTable()->select()
                        ->where('name IN (?)', array_keys($service->getFees()))
                        ->where('type = ?', 'default')
                        ->order(array('name ASC', 'tier_from ASC'));

                    if ($categoryId) {
                        $select->where('category_id = ?', $categoryId);
                    }
                    else {
                        $select->where('category_id is null');
                    }

                    $rowset = $service->fetchAll($select);

                    $feesArray = $service->getFees();

                    $feesSettings = array(
                        Service\Fees::ADDL_CATEGORY     => $this->_settings['addl_category_listing'],
                        Service\Fees::BUYOUT            => $this->_settings['enable_buyout'],
                        Service\Fees::RESERVE           => $this->_settings['enable_auctions'],
                        Service\Fees::DIGITAL_DOWNLOADS => $this->_settings['digital_downloads_max'],
                        Service\Fees::MAKE_OFFER        => $this->_settings['enable_make_offer'],
                        Service\Fees::IMAGES            => $this->_settings['images_max'],
                        Service\Fees::MEDIA             => $this->_settings['videos_max'],
                        Service\Fees::SUBTITLE          => $this->_settings['enable_subtitle'],
                    );

                    foreach ($rowset as $row) {
                        if (!array_key_exists($row['name'], $feesSettings) || ($feesSettings[$row['name']] > 0)) {
                            $row['desc'] = $feesArray[$row['name']];
                            $fees[] = $row;
                        }
                    }
                }

                break;
            case 'stores':
                $storesSubscriptionsService = new Service\Table\StoresSubscriptions();
                $storesSubscriptions = $storesSubscriptionsService->getMultiOptions();
                break;

            default:
                $services = array(
                    'UserSignup',
                    'UserVerification',
                );

                foreach ($services as $name) {
                    $serviceName = '\\Ppb\\Service\\Fees\\' . $name;
                    /** @var \Ppb\Service\Fees $service */
                    $service = new $serviceName();

                    $rowset = $service->fetchAll(
                        $service->getTable()->select()
                            ->where('category_id is null')
                            ->where('name IN (?)', array_keys($service->getFees())));

                    $feesArray = $service->getFees();
                    foreach ($rowset as $row) {
                        $row['desc'] = $feesArray[$row['name']];
                        $fees[] = $row;
                    }
                }

                break;
        }

        $categoriesService = new Service\Table\Relational\Categories();
        $categoriesMultiOptions = $categoriesService->getMultiOptions($selectFeesCategories, null,
            $translate->_('Default'));

        return array(
            'categoryId'             => $categoryId,
            'tabs'                   => $tabs,
            'tab'                    => $tab,
            'categoriesMultiOptions' => $categoriesMultiOptions,
            'fees'                   => $fees,
            'storesSubscriptions'    => $storesSubscriptions,
        );
    }

}

