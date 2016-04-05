<?php

return array(
    'routes'     => array(
        'admin-settings-route'              => array(
            'admin/settings/:page',
            array(
                'controller' => 'settings',
                'action'     => 'index',
            ),
        ),
        'admin-tables-route'                => array(
            'admin/tables/table/:table',
            array(
                'controller' => 'tables',
                'action'     => 'index',
            ),
            array(
                'table' => '[a-zA-Z\.]+',
            )
        ),
        'admin-fees'                        => array(
            'admin/fees/edit/:name',
            array(
                'controller' => 'fees',
                'action'     => 'index',
            ),
        ),
        'admin-admin-users-browse'          => array(
            'admin/browse-users/:view',
            array(
                'controller' => 'users',
                'action'     => 'browse',
            ),
        ),
        'admin-users-add'                   => array(
            'admin/users/add/:view',
            array(
                'controller' => 'users',
                'action'     => 'add',
            ),
        ),
        'admin-users-edit'                  => array(
            'admin/users/manage/:view',
            array(
                'controller' => 'users',
                'action'     => 'manage',
            ),
        ),
        'admin-listings-browse'             => array(
            'admin/browse-listings/:listing_type',
            array(
                'controller' => 'listings',
                'action'     => 'browse',
            ),
        ),
        'admin-listings-sales'              => array(
            'admin/listings/sales',
            array(
                'controller' => 'listings',
                'action'     => 'sales',
            ),
        ),
        'admin-users-reputation-management' => array(
            'admin/users/reputation',
            array(
                'controller' => 'users',
                'action'     => 'reputation',
            ),
        ),
        'admin-messaging-browse'            => array(
            'admin/tools/messaging',
            array(
                'controller' => 'tools',
                'action'     => 'messaging',
            ),
        ),
        'admin-messaging-topic'             => array(
            'admin/tools/messaging-topic',
            array(
                'controller' => 'tools',
                'action'     => 'messaging-topic',
            ),
        ),
        'admin-newsletters'                 => array(
            'admin/tools/newsletters',
            array(
                'controller' => 'tools',
                'action'     => 'newsletters',
            ),
        ),
        'admin-accounting'                  => array(
            'admin/tools/accounting',
            array(
                'controller' => 'tools',
                'action'     => 'accounting',
            ),
        ),
        'admin-accounting-view-invoice'     => array(
            'admin/tools/view-invoice',
            array(
                'controller' => 'tools',
                'action'     => 'view-invoice',
            ),
        ),
    ),
    'view'       => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
    'navigation' => array(
        'data_type'  => 'xml',
        'data_file'  => __DIR__ . '/data/navigation/navigation.xml',
        'views_path' => __DIR__ . '/../view',
    ),
    'session'    => array(
        'namespace' => 'CubeAdmin',
        'secret'    => 'CookieSecretAdmin',
    ),
);
