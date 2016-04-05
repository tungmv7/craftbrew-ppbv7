<?php

/**
 *
 * PHP Pro Bid $Id$ mrOuBmJIkhgHvHxC6EKyhCQ3VFizhK9QSLFxUgUHdzQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Listings\Form,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel;

class Listing extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    public function init()
    {
        $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
        $this->_listings = new Service\Listings();
    }

    public function Create()
    {
        $saveData = false;
        $params = array();
        $paymentBox = null;
        $savedListing = null;

        /** @var \Ppb\Db\Table\Row\User $user */
        $user = Front::getInstance()->getBootstrap()->getResource('user');

        $translate = $this->getTranslate();

        $option = $this->getRequest()->getParam('option');
        $id = $this->getRequest()->getParam('id');

        $currentStep = $this->getRequest()->getParam(Form\Listing::ELEMENT_STEP);


        $formId = 'item';

        if ($id !== null) { // get similar listing
            $savedListing = $this->_listings->findBy('id', $id, true, true);
            if ($savedListing !== null) {
                $params = $savedListing->getData();
                unset($params['last_count_operation']); // to count the new listing properly
            }

            if ($option == 'edit' && $savedListing) {
                if (
                    $savedListing->getData('listing_type') == 'product' &&
                    $savedListing->canEdit() &&
                    $savedListing->hasActivity()
                ) {
                    $formId = 'product_edit';
                }
            }
            else {
                $savedListing = null; // so that fees are applied properly when listing similar items
                unset($params['draft']); // to calculate fees properly in the preview step
            }

            $params['option'] = $option;
        }
        else if (($prefilledFields = $user->getPrefilledFields()) !== null) {
            $params = $prefilledFields;
        }

        $form = new Form\Listing($formId);

        if ($this->getRequest()->isPost()) {
            $params = array_merge(
                $params, $this->getRequest()->getParams());
        }

        $form->setData($params);

        $listingModel = new ListingModel(array(
            'data'  => $params,
            'table' => $this->_listings->getTable()
        ));

        // if editing is disabled, redirect to the listing details page.
        if ($option == 'edit') {
            if (!$listingModel->canEdit()) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('This listing cannot be edited.'),
                    'class' => 'alert-danger',
                ));
                $this->_helper->redirector()->redirect('details', null, null, array('id' => $id));
            }
        }

        $listingSetupService = new Service\Fees\ListingSetup(
            $listingModel, $user);

        if ($voucherCode = $this->getRequest()->getParam('voucher_code')) {
            $listingSetupService->setVoucher($voucherCode);
        }

        if ($option != 'edit') {
            $removeDraftButton = false;
            if ($id !== null && $option == 'list-draft') {
                $form->setTitle('List Draft');
                if (!$currentStep) {
                    $currentStep = 'preview';
                    $removeDraftButton = true;
                }
            }

            $form->generateSubForm($currentStep);

            if ($removeDraftButton) {
                $form->removeElement(Form\Listing::BTN_DRAFT);
            }

            // check if we have store only mode enabled, but the seller doesnt have an active store
            if ($form->hasElement('list_in') && !$form->isPost($this->getRequest()) && !$this->getRequest()->getParam('voucher_add')) {
                if (count($form->getModel()->getListIn()) == 0 && $formId == 'item') {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('<h4>Store only mode is enabled</h4>'
                            . 'Please create a store or upgrade your subscription in order to be able to list items.'),
                        'class' => 'alert-danger',
                    ));
                    $form->clearElements();
                }
            }
        }
        else {
            $form->generateEditForm($id);

            if ($params === null) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_("The listing you are trying to edit does not exist or you are not it's owner."),
                    'class' => 'alert-danger',
                ));
                $form->clearElements();
            }
        }

        if ($savedListing instanceof ListingModel) {
            $listingSetupService->setSavedListing($savedListing);
        }

        $listingFees = $listingSetupService->calculate();

        if ($form->isPost(
            $this->getRequest())
        ) {
            if ($form->isValid() === true || isset($params[Form\Listing::BTN_PREV])) {

                if (isset($params[Form\Listing::BTN_NEXT])) {
                    $currentStep = $form->nextStep($currentStep);
                    if ($currentStep === false) {
                        $saveData = true;
                    }
                }
                else if (isset($params[Form\Listing::BTN_PREV])) {
                    $form->clearMessages();
                    $currentStep = $form->prevStep($currentStep);

                    if ($currentStep === false) {
                        $steps = $form->getSteps();
                        reset($steps);
                        $currentStep = current($steps);
                    }
                }
                else if (isset($params[Form\Listing::BTN_LIST])) {
                    $saveData = true;
                    $params['id'] = 0;
                    $params['draft'] = 0;
                    $currentStep = $form->nextStep($currentStep);
                }
                else if (isset($params[Form\Listing::BTN_DRAFT])) {
                    $saveData = true;
                    $params['id'] = 0;
                    $params['draft'] = 1;
                    $currentStep = $form->nextStep($currentStep);
                }
                else if ($option == 'edit') {
                    $saveData = true;
                }

                if ($saveData === true) {
                    $listingId = $this->_listings->save($params);

                    $listingModel = $this->_listings->findBy('id', $listingId, false, true);

                    // send listing favorite store notification
                    if ($option != 'edit' && $listingModel['list_in'] != 'site') {
                        $favoriteStoresService = new Service\FavoriteStores();
                        $rowset = $favoriteStoresService->fetchAll(
                            $favoriteStoresService->getTable()->select()
                                ->where('store_id = ?', $listingModel['user_id'])
                        );

                        $mail = new \Members\Model\Mail\User();

                        /** @var \Cube\Db\Table\Row $favoriteStore */
                        foreach ($rowset as $favoriteStore) {
                            $mail->newListingFavoriteStoreNotification($listingModel, $favoriteStore->findParentRow('\Ppb\Db\Table\Users', 'User'))
                                ->send();
                        }
                    }

                    $form->clearElements();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => ($option == 'edit') ?
                            sprintf(
                                $translate->_("Listing ID: #%s has been edited successfully."),
                                $listingId) :
                            $this->_('The listing has been created successfully.'),
                        'class' => 'alert-success',
                    ));


                    $message = $listingModel->processPostSetupActions($savedListing);

                    if ($message) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $message,
                            'class' => 'alert-info',
                        ));
                    }

                    $totalAmount = $listingSetupService->getTotalAmount();
                    $userPaymentMode = $user->userPaymentMode();
                    if ($totalAmount > 0 && $userPaymentMode == 'live') {
                        $this->_helper->redirector()->redirect('listing-setup', 'payment', 'app',
                            array('id' => $listingId));
                    }
                    else {
                        $this->_helper->redirector()->redirect('confirm', null, null, array('id' => $listingId));
                    }
                }
            }

            if ($saveData === false) {
                $form->setData($params);
                if ($option != 'edit') {
                    $form->generateSubForm($currentStep);
                }
                else {
                    $form->generateEditForm($id);
                }
            }
        }

        if (count($form->getMessages())) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $form->getMessages(),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'form'                => $form,
            'headline'            => $form->getTitle(),
            'messages'            => $this->_flashMessenger->getMessages(),
            // listing related data
            'listingModel'        => $listingModel,
            'listingSetupService' => $listingSetupService,
            'listingFees'         => $listingFees,
            'currentStep'         => $currentStep,
        );
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        $result = false;

        $translate = $this->getTranslate();

        if ($listing->canDelete()) {
            $result = $listing->delete();
        }

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The listing could not be found or deletion is not possible.'),
                'class' => 'alert-danger',
            ));
        }

        $params = $this->getRequest()->getParams();

        if (empty($params['filter'])) {
            $params['filter'] = 'open';
        }

        $this->_helper->redirector()->redirect('browse', 'selling', 'members', $params);
    }

    public function Close()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        $translate = $this->getTranslate();

        if ($listing->canClose()) {
            $listing->close();
        }

        if ($listing->getClosedFlag() === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been closed."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: the listing could not be closed or it was not found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', 'selling', 'members', $this->getRequest()->getParams());
    }

    public function Details()
    {
        $listing = $this->_listings->findBy('id', (int)$this->getRequest()->getParam('id'));
        $listing->addClick()
            ->addRecentlyViewedListing();

        // META TAGS
        $this->_view->headTitle()->prepend($listing['name']);
        $this->_view->headMeta()->setName('description', $listing->getMetaDescription());

        // Facebook meta tags
        $this->_view->headMeta()->setProperty('og:title', $listing->getData('name'))
            ->setProperty('og:type', 'other')
            ->setProperty('og:image', $listing->getMainImage(true))
            ->setProperty('og:url', $this->_settings['site_path'] . $this->_view->url($listing->link(), null, false, null, false))
            ->setProperty('og:description', $listing->shortDescription());

        // add canonical link
        $this->_view->script()->addHeaderCode('<link rel="canonical" href="' . $this->_view->url($listing->link()) . '">');

        return array(
            'listing'  => $listing,
            'seller'   => $listing->findParentRow('\Ppb\Db\Table\Users'),
            'messages' => $this->_flashMessenger->getMessages(),
            'live'     => true,
        );
    }

    public function Confirm()
    {

        $listing = $this->_listings->findBy('id', (int)$this->getRequest()->getParam('id'));

        return array(
            'listing'  => $listing,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    /**
     *
     * TODO: implement this action/method later
     *
     * @return array
     */
    public function Rollback()
    {
        return array();
    }

    public function Watch()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        $bootstrap = Front::getInstance()->getBootstrap();
        $session = $bootstrap->getResource('session');

        $userToken = strval($session->getCookie(UserModel::USER_TOKEN));
        $userId = (!empty($this->_user['id'])) ? $this->_user['id'] : null;

        $translate = $this->getTranslate();

        $listingsWatchService = new Service\ListingsWatch();

        if (!$listing->isWatched()) {
            $listingsWatchService->save(array(
                'user_token' => $userToken,
                'user_id'    => $userId,
                'listing_id' => $listing['id'],
            ));

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been added to your wishlist."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $listingsWatchService->delete($id, $userId, $userToken);

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been removed from your wishlist."), $id),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->gotoUrl(
            $this->_view->url($listing->link()));
    }

    public function CalculatePostage()
    {
        $data = array();
        $errors = null;

        $user = null;

        $translate = $this->getTranslate();

        $ids = (array)$this->getRequest()->getParam('ids');
        $qnt = (array)$this->getRequest()->getParam('quantity');

        $listingsService = new Service\Listings();

        $ownerId = null;

        foreach ($ids as $key => $id) {
            $listing = $listingsService->findBy('id', $id);

            $quantity = 1;

            if (isset($qnt[$key])) {
                if ($qnt[$key] > 1) {
                    $quantity = $qnt[$key];
                }
            }

            if ($ownerId === null || $listing['user_id'] == $ownerId) {
                $data[] = array(
                    'listing'  => $listing,
                    'quantity' => $quantity,
                );

                if ($ownerId === null) {
                    $user = $listing->findParentRow('\Ppb\Db\Table\Users');
                    $ownerId = $listing['user_id'];
                }
            }
        }

        $postage = array();

        $view = clone $this->_view;

        $view->setNoLayout();

        if ($user instanceof UserModel) {
            $shippingModel = new ShippingModel($user);

            $shippingModel->setLocationId(
                $this->getRequest()->getParam('locationId'))
                ->setPostCode(
                    $this->getRequest()->getParam('postCode'));

            foreach ($data as $row) {
                $shippingModel->addData($row['listing'], $row['quantity']);
            }

            try {
                $postage = $shippingModel->calculatePostage();
            } catch (\RuntimeException $e) {
                $errors = $e->getMessage();
            }

            $view->setVariables(array(
                'enableSelection' => $this->getRequest()->getParam('enableSelection'),
                'postageSettings' => $shippingModel->getPostageSettings(),
                'postageType'     => $shippingModel->getPostageType(),
                'postage'         => $postage,
                'postageId'       => $this->getRequest()->getParam('postageId'),
            ));
        }
        else {
            $errors = $translate->_('Error: cannot instantiate shipping calculation module - invalid seller selected.');
        }

        $view->setVariable('errors', $errors)
            ->process('/listings/listing/calculate-postage.phtml');

        return $view;
    }

    public function EmailFriend()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        $form = null;

        $form = new Form\EmailFriend();

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

                $mail = new \Listings\Model\Mail\BuyerNotification();

                $emails = explode(',', $this->getRequest()->getParam('emails'));
                $message = $this->getRequest()->getParam('message');

                foreach ($emails as $email) {
                    $email = trim($email);
                    $mail->emailFriend($listing, $this->_user, $email, $message)->send();
                }

                $this->_helper->redirector()->gotoUrl(
                    $this->_view->url($listing->link()));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline' => $this->_('Email Listing to Friend'),
            'form'     => $form,
            'listing'  => $listing,
            'messages' => $this->_flashMessenger->getMessages(),
        );

    }
}

