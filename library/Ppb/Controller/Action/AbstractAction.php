<?php

/**
 *
 * PHP Pro Bid $Id$ EfVzxPeEAVCMJgcyTqCKeNy/91QknThQEmRDYUsEMIA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * the class will be extended by every controller in all modules
 */

namespace Ppb\Controller\Action;

use Cube\Controller\Action\AbstractAction as CubeAbstractAction,
    Cube\Controller\Action\Helper,
    Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\ResponseInterface,
    Cube\Authentication\Authentication,
    Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Ppb\Service\Users as UsersService;

abstract class AbstractAction extends CubeAbstractAction
{

    /**
     *
     * flash messenger helper
     *
     * @var \Cube\Controller\Action\Helper\FlashMessenger
     */
    protected $_flashMessenger;

    /**
     *
     * logged in user
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user = null;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * class constructor
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     */
    public function __construct(AbstractRequest $request, ResponseInterface $response)
    {
        $this->setRequest($request)
            ->setResponse($response);

        $this->_helper = new Helper\Broker($this);

        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');

        $bootstrap = Front::getInstance()->getBootstrap();
        $this->_settings = $bootstrap->getResource('settings');
        $this->_user = $bootstrap->getResource('user');

        $this->init();
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    protected function _setNoLayout()
    {
        Front::getInstance()->getBootstrap()->getResource('view')->setNoLayout();
    }

    /**
     *
     * checks if an admin is logged in
     * - will only work if using _forward() method or action() helper
     *
     * @param array $roles
     *
     * @return bool
     */
    protected function _loggedInAdmin($roles = array())
    {
        $authentication = Authentication::getInstance();

        if ($authentication->hasIdentity()) {
            $storage = $authentication->getStorage()->read();

            if (empty($roles)) {
                $roles = array_keys(UsersService::getAdminRoles());
            }
            if (in_array($storage['role'], $roles)) {
                return true;
            }
        }

        return false;
    }


    /**
     *
     * dummy function used as a placeholder for translatable sentences
     *
     * @param $string
     *
     * @return string
     */
    protected function _($string)
    {
        return $string;
    }
}

