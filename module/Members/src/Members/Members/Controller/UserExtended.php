<?php

/**
 *
 * PHP Pro Bid $Id$ PKbwu62Fzq76r0B9ApjqzPSKRrL9/wr8R976BrzvqUU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * MOD:- FACEBOOK LOGIN
 */

namespace Members\Controller;

use Cube\Controller\Front,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Service,
    Facebook\FacebookSession,
    Facebook\FacebookRedirectLoginHelper,
    Facebook\FacebookRequestException,
    Facebook\FacebookRequest,
    Facebook\GraphUser;

class UserExtended extends User
{

    public function FacebookLogin()
    {
        if ($this->_settings['enable_facebook_login']) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');
            FacebookSession::setDefaultApplication($this->_settings['facebook_app_id'], $this->_settings['facebook_app_secret']);

            $helper = new FacebookRedirectLoginHelper(
                $view->url(array('module' => 'members', 'controller' => 'user', 'action' => 'facebook-login'))
            );

            $session = null;

            try {
                $session = $helper->getSessionFromRedirect();
            } catch (FacebookRequestException $ex) {
                // When Facebook returns an error
            } catch (\Exception $ex) {
                // When validation fails or other local issues
            }

            if ($session) {

                // Logged in
                $fbRequest = new FacebookRequest($session, 'GET', '/me', ['fields' => 'id, name, email, first_name, last_name']);
                $userProfile = $fbRequest->execute()->getGraphObject(GraphUser::className());

                $params = array();

                if ($userProfile->getId() && $userProfile->getEmail()) {

                    // check exist current user by facebook id
                    $usersService = new Service\Users();
                    $user = $usersService->findBy('facebook_id', $userProfile->getId());

                    // mapping current user with facebook id
                    if (count($user) == 0) {
                        $temp = $usersService->findBy('email', $userProfile->getEmail());
                        if ($temp && $temp->getData('role') == 'User') {
                            $fbData = ['facebook_id' => $userProfile->getId()];
                            $temp->save($fbData);
                            $user = $temp;
                        }
                    }

                    if (count($user) > 0) {
                        Authentication::getInstance()->authenticate(new Adapter(array(), $user->getData('id')));
                        $this->_helper->redirector()->redirect('index', 'summary', 'members');
                    }
                    else {
                        $session = Front::getInstance()->getBootstrap()->getResource('session');
                        $session->set('facebookUserProfile', array(
                            'facebook_id' => $userProfile->getId(),
                            'email' => $userProfile->getEmail(),
                            'name' => array(
                                'first' => $userProfile->getFirstName(),
                                'last' => $userProfile->getLastName(),
                            )
                        ));
                        $this->_helper->redirector()->redirect('register');
                    }
                } else {
                    $this->_helper->redirector()->redirect('register');
                }
            }
            else {
                $loginUrl = $helper->getLoginUrl(array(
                    'scope' => 'public_profile', 'email'
                ));
                $this->_helper->redirector()->gotoUrl($loginUrl);
            }
        }

        $this->_helper->redirector()->redirect('login');
    }

}

