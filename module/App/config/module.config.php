<?php

/**
 * @version 7.6
 */

return array(
    'routes' => array(
        // route to home page
        'app-home'                             => array(
            '/',
            array(
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
        'app-ipn'                              => array(
            'payment/ipn/:gateway',
            array(
                'controller' => 'payment',
                'action'     => 'ipn',
            ),
        ),
        'app-advert-redirect'                  => array(
            'advert/:id',
            array(
                'controller' => 'index',
                'action'     => 'advert-redirect',
            ),
        ),
        'app-content-sections'                 => array(
            'section/:name/:id',
            array(
                'controller' => 'sections',
                'action'     => 'view',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'app-content-pages'                    => array(
            'page/:name/:id',
            array(
                'controller' => 'pages',
                'action'     => 'view',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'app-rss-index'                        => array(
            'rss',
            array(
                'controller' => 'rss',
                'action'     => 'index',
            ),
        ),
        'app-rss-feed'                         => array(
            'rss/feed/:type',
            array(
                'controller' => 'rss',
                'action'     => 'feed',
            ),
        ),
        'app-sitemap'                          => array(
            'sitemap.xml',
            array(
                'controller' => 'index',
                'action'     => 'sitemap',
            ),
        ),
        'link-play-video'                      => array(
            'play-video/:id',
            array(
                'controller' => 'index',
                'action'     => 'play-video',
            ),
        ),
        'app-payment-completed'                => array(
            'payment/completed',
            array(
                'controller' => 'payment',
                'action'     => 'completed',
            ),
        ),
        'app-payment-failed'                   => array(
            'payment/failed',
            array(
                'controller' => 'payment',
                'action'     => 'failed',
            ),
        ),
        'app-payment-completed-transaction-id' => array(
            'payment/completed/:transaction_id',
            array(
                'controller' => 'payment',
                'action'     => 'completed',
            ),
        ),
        'app-payment-failed-transaction-id'    => array(
            'payment/failed/:transaction_id',
            array(
                'controller' => 'payment',
                'action'     => 'failed',
            ),
        ),
        'app-error-notfound'                   => array(
            'not-found',
            array(
                'controller' => 'error',
                'action'     => 'not-found',
            ),
        ),
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
);
