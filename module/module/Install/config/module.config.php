<?php

/**
 * @version 7.6
 */

return array(
    'routes' => array(
        'install-index'  => array(
            'install',
            array(
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
        'install-action' => array(
            'install/:action',
            array(
                'controller' => 'index',
            ),
        ),
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
    'session'    => array(
        'namespace' => 'InstalleCsycUGK',
        'secret'    => 'BogljldF',
    ),
);
