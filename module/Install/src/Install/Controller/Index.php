<?php

/**
 *
 * PHP Pro Bid $Id$ 5tXTQRUmBmY8Yv2BtEI+Quf8X4snXqO4F/EWyUb/NAM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * install controller
 */

namespace Install\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Controller\Request,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Install\Form,
    Install\Model,
    Ppb\Db\Table,
    Ppb\Service,
    Cube\Session;

class Index extends AbstractAction
{

    /**
     * installer session namespace
     */
    const SESSION_NAMESPACE = 'PpbInstaller';
    const SESSION_SECRET = 'PpbInstallerSecret';

    /**
     *
     * sql files to be parsed on a new installation
     *
     * @var array
     */
    protected $_sqlFiles = array(
        '7.0' => 'stock-7.0.sql',
        '7.1' => '7.1-update.sql',
        '7.2' => '7.2-update.sql',
        '7.3' => '7.3-update.sql',
        '7.4' => '7.4-update.sql',
        '7.5' => '7.5-update.sql',
        '7.6' => '7.6-update.sql',
        '7.7' => '7.7-update.sql',
    );

    /**
     *
     * added in v7.4 - the upgrade installer will also check for and remove any obsolete stock installation files
     *
     * @var array
     */
    protected $_obsoleteFiles = array(
        /**
         * v7.4 - OBSOLETE FILES
         */
        /**
         * login.phtml - Admin
         */
        'module/Admin/view/forms/login.phtml',
        /**
         * form-element.phtml - App & Install
         */
        'module/Admin/view/partials/form-element.phtml',
        'module/Listings/view/partials/form-element.phtml',
        'module/Members/view/partials/form-element.phtml',
        /**
         * generic-horizontal.phtml - App
         */
        'module/Admin/view/forms/generic-horizontal.phtml',
        'module/Install/view/forms/generic-horizontal.phtml',
        'module/Members/view/forms/generic-horizontal.phtml',
        /**
         * browse-filter.phtml - App
         */
        'module/Admin/view/navigation/browse-filter.phtml',
        /**
         * left-side.phtml - Admin & App
         */
        'module/Members/view/navigation/left-side.phtml',
        /**
         * pagination.phtml - (+) App
         */
        'module/Admin/view/partials/pagination.phtml',
        'module/Listings/view/partials/pagination.phtml',
        'module/Members/view/partials/pagination.phtml',

        /**
         * v7.5 - OBSOLETE FILES
         */
        'library/Ppb/Form/Element/CustomField.php',
        'library/Ppb/Form/Element/CustomFields.php',
        'library/Ppb/Service/Table/Locations.php',
        'library/Ppb/Service/SalesListings.php',

        /**
         * v7.6 - OBSOLETE FILES
         * + js/colorbox folder!
         */
        'module/Listings/view/emails/buyer-offer-accepted.phtml',
        'module/Listings/view/emails/buyer-offer-declined.phtml',
        /**
         * popup-form.phtml - Admin
         */
        'module/Admin/view/forms/popup-form.phtml',

        /**
         * v7.7 - OBSOLETE FILES
         */
        'module/Listings/src/Listings/View/Helper/ListingStatus.php',
        /**
         * form-element.phtml - App only
         */
        'module/Install/view/partials/form-element.phtml',
        /**
         * *-list.phtml, *-grid.phtml
         */
        'module/Listings/view/partials/listing-list.phtml',
        'module/Listings/view/partials/listing-grid.phtml',
        'module/Members/view/partials/store-list.phtml',
        'module/Members/view/partials/store-grid.phtml',
        /**
         * jquery.printarea.js
         */
        'js/jquery.printarea.js',
    );

    /**
     *
     * check if we have a successful database connection
     *
     * @var bool
     */
    protected $_connected;

    public function init()
    {
        $this->_connected = Front::getInstance()->getBootstrap()->getResource('connected');

        $installMessagesSession = new Session(array(
            'namespace' => self::SESSION_NAMESPACE,
            'secret'    => self::SESSION_SECRET,
        ));
        $this->_flashMessenger->setSession($installMessagesSession);
    }

    public function Index()
    {
        $formId = ($this->_connected) ? 'upgraded' : 'install';

        $form = new Form\Install($formId);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getParams());

            if ($form->isValid() === true) {
                if (!$this->_connected) {
                    // we have a fresh installation
                    // edit global.config.php - add db connection data and session namespace/secret
                    $globalConfigPath = __DIR__ . '/../../../../../config/global.config.php';
                    $adminModuleConfigPath = __DIR__ . '/../../../../Admin/config/module.config.php';
                    $installModuleConfigPath = __DIR__ . '/../../../config/module.config.php';

                    $dbHost = $this->getRequest()->getParam('db_host');
                    $dbName = $this->getRequest()->getParam('db_name');
                    $dbUsername = $this->getRequest()->getParam('db_username');
                    $dbPassword = $this->getRequest()->getParam('db_password');
                    $tablesPrefix = $this->getRequest()->getParam('tables_prefix');

                    $sessionNamespace = $this->getRequest()->getParam('session_namespace');
                    $sessionSecret = $this->getRequest()->getParam('session_secret');

                    $adminEmail = $this->getRequest()->getParam('admin_email');

                    $string = file_get_contents($globalConfigPath);
                    $string = str_replace(
                        array(
                            '%DB_HOST%',
                            '%DB_NAME%',
                            '%DB_USERNAME%',
                            '%DB_PASSWORD%',
                            '%TABLES_PREFIX%',
                            '%SESSION_NAMESPACE%',
                            '%SESSION_SECRET%',
                        ),
                        array(
                            $dbHost,
                            $dbName,
                            $dbUsername,
                            $dbPassword,
                            $tablesPrefix,
                            $sessionNamespace,
                            $sessionSecret
                        ), $string);
                    file_put_contents($globalConfigPath, $string);

                    // edit admin/module.config.php - add session namespace/secret
                    $string = file_get_contents($adminModuleConfigPath);
                    $string = str_replace(
                        array(
                            '%ADMIN_SESSION_NAMESPACE%',
                            '%ADMIN_SESSION_SECRET%'
                        ),
                        array(
                            'Admin' . $sessionNamespace,
                            Model\Elements\Install::generateRandomKey(),
                        ), $string);
                    file_put_contents($adminModuleConfigPath, $string);

                    // edit install/module.config.php - add session namespace/secret
                    $string = file_get_contents($installModuleConfigPath);
                    $string = str_replace(
                        array(
                            '%INSTALL_SESSION_NAMESPACE%',
                            '%INSTALL_SESSION_SECRET%'
                        ),
                        array(
                            'Install' . $sessionNamespace,
                            Model\Elements\Install::generateRandomKey(),
                        ), $string);
                    file_put_contents($installModuleConfigPath, $string);

                    $parser = new Model\Parser();

                    $parser->addPlaceholder('{%TABLE_PREFIX%}', $tablesPrefix)
                        ->addPlaceholder('%SITE_NAME%', $this->getRequest()->getParam('site_name'))
                        ->addPlaceholder('%SITE_PATH%', $this->getRequest()->getParam('site_path'))
                        ->addPlaceholder('%ADMIN_EMAIL%', $adminEmail)
                        ->addPlaceholder('%MOD_REWRITE_URLS%', ((\Ppb\Utility::checkModRewrite()) ? '1' : '0'))
                        ->setDbCredentials(array(
                            'host'     => $dbHost,
                            'dbname'   => $dbName,
                            'username' => $dbUsername,
                            'password' => $dbPassword,
                            'prefix'   => $tablesPrefix,
                        ))
                        ->stopOnError();

                    $result = true;
                    foreach ($this->_sqlFiles as $fileName) {
                        $parser->setFilePath(__DIR__ . '/../../../../../SQL/' . $fileName);
                        $result = $parser->parse(true);

                        if (!$result) {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $parser->getErrors(),
                                'class' => 'alert-danger',
                            ));

                            $this->_helper->redirector()->redirect('failed');
                        }
                    }

                    if ($result) {
                        $usersTable = new Table\Users();
                        $usersTable->setAdapter($parser->getAdapter())
                            ->setPrefix($tablesPrefix);

                        // create admin user
                        $usersService = new Service\Users();
                        $salt = date('U', time());
                        $adminPassword = $usersService->hashPassword($this->getRequest()->getParam('admin_password'), $salt);

                        $usersTable->insert(array(
                            'username'       => $this->getRequest()->getParam('admin_username'),
                            'password'       => $adminPassword,
                            'salt'           => $salt,
                            'email'          => $adminEmail,
                            'active'         => 1,
                            'approved'       => 1,
                            'mail_activated' => 1,
                            'payment_status' => 'confirmed',
                            'role'           => Service\Users::ADMIN_ROLE_PRIMARY,
                        ));

                        // populate tables if required by admin
                        if ($this->getRequest()->getParam('populate_tables')) {
                            $parser->setFilePath(__DIR__ . '/../../../../../SQL/7.0-sample-data.sql')
                                ->clearPlaceholders()
                                ->addPlaceholder('{%TABLE_PREFIX%}', $tablesPrefix)
                                ->parse(true);
                        }

                        // now remove all obsolete files
                        $this->_deleteObsoleteFiles();

                        $this->_helper->redirector()->redirect('success');
                    }
                }
                else {
                    $this->_helper->redirector()->redirect('failed');
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
            'form'      => $form,
            'messages'  => $this->_flashMessenger->getMessages(),
            'connected' => $this->_connected,
        );
    }


    public function Success()
    {
        return array(
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function Failed()
    {
        return array(
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function Importer()
    {
        $form = null;
        $submitted = false;
        if (!$this->_connected) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Fatal Error: Could not connect to the database.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $form = new Form\Install('v6_importer');

            $submitted = false;

            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getParams());

                if ($form->isValid() === true) {
                    $submitted = true;

                    $parser = new Model\Parser();
                    $parser->setFilePath(__DIR__ . '/../../../../../SQL/6.x-importer.sql');

                    $oldTablesPrefix = $this->getRequest()->getParam('v6_tables_prefix');

                    $options = Front::getInstance()->getOptions();
                    /** @var \Cube\Db\Adapter\PDO\Mysql $dbAdapter */
                    $dbAdapter = Front::getInstance()->getBootstrap()->getResource('db');
                    $adapterConfig = $dbAdapter->getConfig();
                    $parser->setAdapter($dbAdapter)
                        ->addPlaceholder('{%NEW_PREFIX%}', $adapterConfig['prefix'])
                        ->addPlaceholder('{%OLD_PREFIX%}', $oldTablesPrefix)
                        ->stopOnError();

                    $result = $parser->parse(true);

                    if (!$result) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $parser->getErrors(),
                            'class' => 'alert-danger',
                        ));

                        $this->_helper->redirector()->redirect('failed');
                    }
                    else {
                        /* post import operations */
                        // convert custom_fields_data box_value fields to serialized data.
                        $customFieldsService = new Service\CustomFields();
                        $customFields = $customFieldsService->fetchAll(
                            $customFieldsService->getTable()->select()
                                ->where('LOCATE("[]", multiOptions) > 0')
                        );

                        /** @var \Cube\Db\Table\Row $customField */
                        foreach ($customFields as $customField) {
                            $array = explode('[]', $customField['multiOptions']);
                            $multiOptions = array(
                                'key'   => $array,
                                'value' => $array,
                            );
                            $customField->save(array(
                                'multiOptions' => serialize($multiOptions),
                            ));
                        }

                        // update payment_gateways table - id, site_fees and direct_payment fields
                        $statement = $dbAdapter->query("SELECT * FROM `" . $oldTablesPrefix . "payment_gateways`");
                        $rows = $statement->fetchAll(\Cube\Db::FETCH_ASSOC);

                        $paymentGatewaysTable = new Table\PaymentGateways();
                        foreach ($rows as $row) {
                            $pgName = $row['name'];
                            switch ($row['name']) {
                                case 'Worldpay':
                                    $pgName = 'WorldPay';
                                    break;
                                case '2Checkout':
                                    $pgName = 'TCheckout';
                                    break;
                                case 'Protx':
                                    $pgName = 'SagePay';
                                    break;
                                case 'Authorize.net':
                                    $pgName = 'AuthorizeNet';
                                    break;
                                case 'Test Mode':
                                    $pgName = 'PaymentSimulator';
                                    break;
                                case 'Amazon':
                                    $pgName = 'AmazonPayments';
                                    break;
                                case 'Moneybookers':
                                    $pgName = 'Skrill';
                                    break;
                            }

                            $paymentGatewaysTable->update(array(
                                'id'             => $row['pg_id'],
                                'site_fees'      => $row['checked'],
                                'direct_payment' => $row['dp_enabled'],
                            ), "name='{$pgName}'");
                        }

                        // update direct_payment methods

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The import has been completed successfully.'),
                            'class' => 'alert-success',
                        ));
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
        }

        return array(
            'form'      => $form,
            'messages'  => $this->_flashMessenger->getMessages(),
            'submitted' => $submitted,
            'connected' => $this->_connected,
        );
    }

    public function Upgrade()
    {
        $form = null;
        $submitted = false;
        if (!$this->_connected) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Fatal Error: Could not connect to the database.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $form = new Form\Install('upgrade');

            $submitted = false;

            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getParams());

                if ($form->isValid() === true) {
                    // update session keys for the admin module config file
                    $adminModuleConfigPath = __DIR__ . '/../../../../Admin/config/module.config.php';
                    // edit admin/module.config.php - add session namespace/secret
                    $string = file_get_contents($adminModuleConfigPath);
                    $string = str_replace(
                        array(
                            '%ADMIN_SESSION_NAMESPACE%',
                            '%ADMIN_SESSION_SECRET%'
                        ),
                        array(
                            Model\Elements\Install::generateRandomKey(),
                            Model\Elements\Install::generateRandomKey(),
                        ), $string);
                    file_put_contents($adminModuleConfigPath, $string);

                    // update session keys for the install module config file
                    $installModuleConfigPath = __DIR__ . '/../../../../Install/config/module.config.php';
                    // edit install/module.config.php - add session namespace/secret
                    $string = file_get_contents($installModuleConfigPath);
                    $string = str_replace(
                        array(
                            '%INSTALL_SESSION_NAMESPACE%',
                            '%INSTALL_SESSION_SECRET%'
                        ),
                        array(
                            Model\Elements\Install::generateRandomKey(),
                            Model\Elements\Install::generateRandomKey(),
                        ), $string);
                    file_put_contents($installModuleConfigPath, $string);

                    // first delete all files from the /cache/ folder
                    $this->_deleteTableCacheFiles();

                    $submitted = true;

                    $parser = new Model\Parser();

                    $options = Front::getInstance()->getOptions();
                    /** @var \Cube\Db\Adapter\PDO\Mysql $dbAdapter */
                    $dbAdapter = Front::getInstance()->getBootstrap()->getResource('db');
                    $adapterConfig = $dbAdapter->getConfig();
                    $parser->setAdapter($dbAdapter)
                        ->addPlaceholder('{%TABLE_PREFIX%}', $adapterConfig['prefix'])
                        ->addPlaceholder('%MOD_REWRITE_URLS%', ((\Ppb\Utility::checkModRewrite()) ? '1' : '0'))
                        ->stopOnError();

                    $result = true;
                    $currentVersion = doubleval($this->getRequest()->getParam('current_version'));

                    foreach ($this->_sqlFiles as $version => $fileName) {
                        $version = doubleval($version);
                        if ($version > $currentVersion) {

                            $parser->setFilePath(__DIR__ . '/../../../../../SQL/' . $fileName);
                            $result = $parser->parse(true);

                            if (!$result) {
                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $parser->getErrors(),
                                    'class' => 'alert-danger',
                                ));

                                $this->_helper->redirector()->redirect('failed');
                            }
                        }
                    }

                    // now remove all obsolete files
                    $this->_deleteObsoleteFiles();

                    if ($result) {
                        $this->_helper->redirector()->redirect('success');
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
        }

        return array(
            'form'      => $form,
            'messages'  => $this->_flashMessenger->getMessages(),
            'submitted' => $submitted,
            'connected' => $this->_connected,
        );
    }

    public function Mods()
    {
        $form = null;
        $submitted = false;
        if (!$this->_connected) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Fatal Error: Could not connect to the database.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $form = new Form\Install('mods');

            $submitted = false;

            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getParams());

                if ($form->isValid() === true) {
                    // first delete all files from the /cache/ folder
                    $this->_deleteTableCacheFiles();

                    $submitted = true;

                    $parser = new Model\Parser();

                    $options = Front::getInstance()->getOptions();
                    /** @var \Cube\Db\Adapter\PDO\Mysql $dbAdapter */
                    $dbAdapter = Front::getInstance()->getBootstrap()->getResource('db');
                    $adapterConfig = $dbAdapter->getConfig();
                    $parser->setAdapter($dbAdapter)
                        ->addPlaceholder('{%TABLE_PREFIX%}', $adapterConfig['prefix'])
                        ->stopOnError();

                    $fileNames = $this->getRequest()->getParam('file_name');

                    $result = true;

                    foreach ((array)$fileNames as $fileName) {
                        $parser->setFilePath(__DIR__ . '/../../../../../SQL/' . $fileName);
                        $result = $parser->parse(true);

                        if (!$result) {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $parser->getErrors(),
                                'class' => 'alert-danger',
                            ));
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('The process has failed.'),
                                'class' => 'alert-danger',
                            ));
                        }
                    }

                    if ($result) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The sql queries have been run successfully.'),
                            'class' => 'alert-success',
                        ));

                        $form->clearElements();
                    }
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }

        return array(
            'form'      => $form,
            'messages'  => $this->_flashMessenger->getMessages(),
            'submitted' => $submitted,
            'connected' => $this->_connected,
        );
    }

    public function ActivateLicense()
    {
        if ($this->getRequest()->getParam('option') == 'invalid-license') {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The license key for this domain is invalid.'),
                'class' => 'alert-danger',
            ));
        }

        $httpHost = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW);
        if ($httpHost == null) {
            $httpHost = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : null;
        }
        $documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_UNSAFE_RAW);
        if ($documentRoot == null) {
            $documentRoot = (!empty($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : null;
        }

        $form = null;
        $submitted = false;
        if (!$this->_connected) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Fatal Error: Could not connect to the database.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $form = new Form\Install('licensing');

            $submitted = false;

            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getParams());

                if ($form->isValid() === true) {
                    $submitted = true;

                    $settingsService = new Service\Settings();

                    $settingsService->save(array(
                        'license_key' => urlencode($this->getRequest()->getParam('license_key')),
                    ));

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The license activation key has been saved.'),
                        'class' => 'alert-success',
                    ));

                    $form->clearElements();
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }

        return array(
            'form'         => $form,
            'messages'     => $this->_flashMessenger->getMessages(),
            'submitted'    => $submitted,
            'connected'    => $this->_connected,
            'httpHost'     => $httpHost,
            'documentRoot' => $documentRoot,
        );
    }

    public function Login()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setLayout('themes/admin/login.phtml');

        $view->headTitle()->prepend('Login');

        $loginForm = new \Admin\Form\Login();

        if ($this->getRequest()->isPost()) {
            $loginForm->setData($this->getRequest()->getParams());

            $adapter = new Adapter(
                $this->getRequest()->getParams(),
                null,
                \Ppb\Service\Users::getAdminRoles()
            );

            $authentication = Authentication::getInstance();

            $result = $authentication->authenticate($adapter);

            if ($authentication->hasIdentity()) {
                $redirectUrl = $this->getRequest()->getBaseUrl() .
                    $this->getRequest()->getRequestUri();
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Invalid Login Credentials'),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'loginForm' => $loginForm,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }

    public function Logout()
    {
        Authentication::getInstance()->clearIdentity();

        $this->_helper->redirector()->redirect('login');
    }

    protected function _deleteObsoleteFiles()
    {
        foreach ($this->_obsoleteFiles as $file) {
            @unlink(__DIR__ . '/../../../../../' . $file);
        }

        return $this;
    }

    protected function _deleteTableCacheFiles()
    {
        // first delete all files from the /cache/ folder
        $cacheFiles = glob(__DIR__ . '/../../../../../cache/*');
        foreach ($cacheFiles as $cacheFile) {
            if (is_file($cacheFile))
                @unlink($cacheFile);
        }

        return $this;
    }
}
