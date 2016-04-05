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
 * the class will be extended by every controller from the members module
 */

namespace Members\Controller\Action;

use Ppb\Controller\Action\AbstractAction as PpbAbstractAction,
    Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\ResponseInterface,
    Ppb\Service\Users as UsersService;

abstract class AbstractAction extends PpbAbstractAction
{

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * class constructor
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     */
    public function __construct(AbstractRequest $request, ResponseInterface $response)
    {
        parent::__construct($request, $response);

        $this->_users = new UsersService();
    }
}

