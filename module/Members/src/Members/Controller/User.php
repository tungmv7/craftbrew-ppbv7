<?php

/**
 *
 * PHP Pro Bid $Id$ tEjlngvwvLF7KFwpv3OUuoP3ePAms+STun7lqkZ8qH8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Members\Controller;

use Cube\Controller\Front;
use Members\Controller\Action\AbstractAction,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Service\Fees as FeesService,
    Ppb\Service\Users as UsersService,
    Ppb\Service\UsersAddressBook as UsersAddressBookService,
    Ppb\Service\BlockedUsers as BlockedUsersService,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel,
    Members\Form,
    Members\Model\Mail;

class User extends AbstractAction
{

    public function Index()
    {
        $this->_helper->redirector()->redirect('index', 'summary');
    }

    public function Register()
    {
        $type = $this->getRequest()->getParam('type');

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $id = $userId = null;
        $user = null;
        $formId = array();
        $signupFee = null;
        $controller = 'User';
        $formTitle = null;
        $displaySubtitles = true;
        $data = array();

        $isMembersModule = false;

        if (!empty($this->_user['id'])) {
            // edit form
            $isMembersModule = true;
            $id = $this->_user['id'];
            $user = $this->_users->findBy('id', $id, true);
            $data = $user->toArray();

            switch ($type) {
                case 'account-settings':
                    $formId = array('user', 'payment-gateways');
                    $controller = 'My Account';
                    $formTitle = $this->_('Account Settings');
                    break;
                case 'payment-gateways':
                    $formId = array('payment-gateways');
                    break;
                case 'manage-address':
                    $formId = array('address');
                    $controller = 'My Account';
                    $formTitle = $this->_('Add Address');
                    $displaySubtitles = false;

                    $addressId = $this->getRequest()->getParam('address_id');
                    $address = $user->getAddress($addressId);

                    if ($addressId && $address !== null) {
                        $formTitle = $this->_('Edit Address');


                        if (($result = $address->canEdit()) !== true) {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $result,
                                'class' => 'alert-danger',
                            ));

                            $this->_helper->redirector()->redirect('address-book', 'account', 'members', array());
                        }


                        $data = array_merge($data, $address->getData());
                    }
                    else {
                        $addressBookService = new UsersAddressBookService();
                        $data = array_merge($data,
                            array_map(function () {
                            }, array_flip($addressBookService->getAddressFields())));
                    }


                    break;
                default:
                    $formId = array('basic', 'advanced', 'address');
                    $controller = 'My Account';
                    $formTitle = $this->_('Personal Information');
                    break;
            }
        }
        else {

            switch ($type) {
                case 'forgot-username':
                    $formId = array('forgot-username');
                    $formTitle = $this->_('Retrieve Username');
                    break;
                case 'forgot-password':
                    $formId = array('forgot-password');
                    $formTitle = $this->_('Reset Password');
                    break;
                default:
                    $formId[] = 'basic';
                    $displaySubtitles = false;

                    if ($this->_settings['registration_type'] == 'full') {
                        $formId[] = 'advanced';
                        $formId[] = 'address';
                        $displaySubtitles = true;
                    }

                    if ($this->_settings['payment_methods_registration']) {
                        $formId[] = 'payment-gateways';
                    }
                    break;
            }
        }

        $form = new Form\Register($formId, null, $user, $displaySubtitles);

        if ($this->getRequest()->getParam('popup')) {
            $this->_setNoLayout();
            $view->script()
                ->clearHeaderCode()
                ->clearBodyCode();
            $form->setPartial('forms/popup-form.phtml');
        }

        if ($id) {
            $form->setData($data)
                ->generateEditForm($id);
        }

        if ($formTitle) {
            $form->setTitle($formTitle);
        }

        $form->setDisplaySubtitles($displaySubtitles);

        $blockedUsersService = new BlockedUsersService();
        $blockedUser = $blockedUsersService->check(
            BlockedUserModel::ACTION_REGISTER,
            array(
                'ip'       => $_SERVER['REMOTE_ADDR'],
                'username' => $this->getRequest()->getParam('username'),
                'email'    => $this->getRequest()->getParam('email'),
            ));

        if ($blockedUser !== null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $view->blockStatus($blockedUser)->blockMessage(),
                'class' => 'alert-danger',
            ));
        }
        else if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                if ($type == 'forgot-username') {
                    $email = $this->getRequest()->getParam('email');
                    $user = $this->_users->findBy('email', $email);

                    if ($user) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('An email containing the username associated with your email address has been sent.'),
                            'class' => 'alert-success',
                        ));

                        $mail = new Mail\User();
                        $mail->forgotUsername($user)->send();
                    }
                    else {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The email address you have submitted is not associated to any account.'),
                            'class' => 'alert-danger',
                        ));
                    }
                }
                else if ($type == 'forgot-password') {
                    $email = $this->getRequest()->getParam('email');
                    $username = $this->getRequest()->getParam('username');

                    /** @var \Ppb\Db\Table\Row\User $user */
                    $user = $this->_users->fetchAll(
                        $this->_users->getTable()->select()
                            ->where('username = ?', $username)
                            ->where('email = ?', $email)
                    )->getRow(0);

                    if ($user) {
                        $password = substr(md5(rand(0, 100000)), 0, 8);
                        $this->_users->savePassword($user, $password);

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('An email containing your updated login details has been sent.'),
                            'class' => 'alert-success',
                        ));

                        $mail = new Mail\User();
                        $mail->forgotPassword($user, $password)->send();
                    }
                    else {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The username / email address combination you have submitted doesnt exist.'),
                            'class' => 'alert-danger',
                        ));
                    }
                }
                else {
                    $userId = $this->_users->save($params, $id);

                    if ($id === null) { // new user registration related actions
                        $params['partial'] = true;
                        $params['registration_key'] = $this->_users->generateRegistrationKey($userId,
                            $params['username']);

                        $mail = new Mail\Register($params);

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('Thank you for registering.'),
                            'class' => 'alert-success',
                        ));

                        $feesService = new FeesService();
                        $feesService->setUser($userId);

                        $signupFee = $feesService->getFeeAmount('signup');

                        if ($signupFee <= 0) {
                            $params['payment_status'] = 'confirmed';
                            $params['active'] = 1;
                        }

                        switch ($this->_settings['signup_settings']) {
                            case 0:
                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $this->_('Registration completed.'),
                                    'class' => 'alert-info',
                                ));
                                $params['approved'] = $params['mail_activated'] = 1;

                                $mail->registerDefault()->send();

                                break;
                            case 1:
                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $this->_('An email has been sent to the address you have submitted with details on how to activate your account.'),
                                    'class' => 'alert-info',
                                ));

                                $params['approved'] = 1;

                                $mail->registerConfirm()->send();

                                break;
                            case 2:
                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $this->_('Your account will be approved after it will be reviewed by an administrator.'),
                                    'class' => 'alert-info',
                                ));

                                $mail->registerApprovalUser()->send();
                                $mail->registerApprovalAdmin()->send();

                                break;
                        }

                        $this->_users->save($params, $userId);

                        // log user in
                        Authentication::getInstance()->authenticate(new Adapter(array(), $userId));
                        $this->_helper->redirector()->redirect('index', 'summary', 'members');
                    }
                    else {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('Your details have been edited successfully.'),
                            'class' => 'alert-success',
                        ));
                    }
                }

                $form->clearElements();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline'        => $form->getTitle(),
            'form'            => $form,
            'signupFee'       => $signupFee,
            'userId'          => $userId,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => $isMembersModule,
            'controller'      => $controller,
        );
    }

    public function Login()
    {
        $form = new Form\Login();

        $username = $this->getRequest()->getParam('username');
        $email = $this->getRequest()->getParam('username');
        $password = $this->getRequest()->getParam('password');

        $blockedUsersService = new BlockedUsersService();
        $blockedUser = $blockedUsersService->check(
            BlockedUserModel::ACTION_REGISTER,
            array(
                'ip'       => $_SERVER['REMOTE_ADDR'],
                'username' => $username,
                'email'    => $email,
            ));

        if ($blockedUser !== null) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');

            $this->_flashMessenger->setMessage(array(
                'msg'   => $view->blockStatus($blockedUser)->blockMessage(),
                'class' => 'alert-danger',
                'local' => true
            ));
        }
        else if ($form->isPost(
            $this->getRequest())
        ) {

            $form->setData($this->getRequest()->getParams());

            $adapter = new Adapter(array(
                'username' => $username,
                'email'    => $email,
                'password' => $password
            ), null, array(), UsersService::getAdminRoles());

            $authentication = Authentication::getInstance();
            $authentication->authenticate($adapter);

            if ($authentication->hasIdentity()) {
                $user = $authentication->getIdentity();
                $this->_users->save(array(
                    'last_login' => new \Cube\Db\Expr('now()')
                ), $user['id']);


                if ($this->getRequest()->getParam('remember_me')) {
                    /** @var \Cube\Session $session */
                    $session = Front::getInstance()->getBootstrap()->getResource('session');
                    $user = $authentication->getStorage()->read();
                    $session->setCookie(UserModel::REMEMBER_ME, $user['id']);
                }

                $redirectUrl = $this->getRequest()->getParam('redirect');

                if (empty($redirectUrl)) {
                    $redirectUrl = $this->getRequest()->getBaseUrl() .
                        $this->getRequest()->getRequestUri();
                }
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The login details you have submitted are invalid.'),
                    'class' => 'alert-danger',
                    'local' => true
                ));
            }
        }

        return array(
            'form'            => $form,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function Logout()
    {
        Authentication::getInstance()->clearIdentity();

        /** @var \Cube\Session $session */
        $session = Front::getInstance()->getBootstrap()->getResource('session');
        $session->unsetCookie(UserModel::REMEMBER_ME);

        $url = $this->_settings['site_path'];
        $this->_helper->redirector()->gotoUrl($url);
    }

    public function Activate()
    {
        if ($this->getRequest()->getParam('resend_email')) {
            $this->_resendActivationEmail();
        }

        return array(
            'headline'        => $this->_('Activate Account'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'user'            => $this->_user,
            'isMembersModule' => false,
        );
    }

    public function ConfirmRegistration()
    {
        $key = $this->getRequest()->getParam('key');
        $verified = $this->_users->verifyEmailAddress($key);

        if ($verified !== true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Email verification failed. The activation key is invalid, or the email for this account has already been verified.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Confirm Registration'),
            'verified'        => $verified,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function Verification()
    {
        if (!$this->_settings['user_verification']) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('User verification is disabled.'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('index', 'summary');
        }

        $form = new Form\Register(array('advanced', 'address'), null, $this->_user, false);

        $form->setData($this->_user->getData())
            ->generateEditForm($this->_user['id']);

        $form->addSubmitElement($this->_('Get Verified'));

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_users->save($params, $this->_user['id']);
                $this->_helper->redirector()->redirect('user-verification', 'payment', 'app');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline'        => $this->_('User Verification'),
            'form'            => $form,
            'messages'        => $this->_flashMessenger->getMessages(),
            'user'            => $this->_user,
            'isMembersModule' => false,
        );
    }

    public function NewsletterUnsubscribe()
    {
        $username = $this->getRequest()->getParam('username');
        $email = $this->getRequest()->getParam('email');

        $unsubscribed = $this->_users->newsletterUnsubscribe($username, $email);

        if ($unsubscribed === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('You have been successfully unsubscribed from our newsletter.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Unsubscription failed. Your account could not be found, or your newsletter subscription is not active.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Newsletter Subscription'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function ForgotUsername()
    {
        $this->_forward('register', null, null, array('type' => 'forgot-username'));
    }

    public function ForgotPassword()
    {
        $this->_forward('register', null, null, array('type' => 'forgot-password'));
    }

    public function EditPaymentGateway()
    {
        $this->_forward('register', null, null, array('type' => 'payment-gateways'));
    }


    public function RegisterModal()
    {
        $this->_setNoLayout();

        return array();
    }

    public function LoginModal()
    {
        $this->_setNoLayout();

        return array();
    }

    protected function _resendActivationEmail()
    {
        $identity = Authentication::getInstance()->getIdentity();

        if (isset($identity['id'])) {
            $user = $this->_users->findBy('id', $identity['id']);

            $mail = new Mail\Register($user->toArray());

            switch ($this->_settings['signup_settings']) {
                case '1':
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The verification email has been sent.'),
                        'class' => 'alert-info',
                    ));

                    $mail->registerConfirm()->send();

                    break;
                case '2':
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The verification email has been sent.'),
                        'class' => 'alert-info',
                    ));

                    $mail->registerApprovalUser()->send();

                    break;
                default:
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The email could not be sent, because no email verification is necessary.'),
                        'class' => 'alert-danger',
                    ));
                    break;
            }
        }
    }


}

