<?php
/**
 * @version 7.3
 */

return array(
    'routes' => array(
        'home-index' => array(
            '/home',
            array(
                'controller' => 'home',
                'action' => 'index',
            ),
        )
    ),
    'view' => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path' => __DIR__ . '/../view',
        'layout_file' => 'layout-home.phtml',
    ),
);
