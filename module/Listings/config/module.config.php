<?php
/**
 * @version 7.3
 */

return array(
    'routes' => array(
        'listings-create'                      => array(
            'sell',
            array(
                'controller' => 'listing',
                'action'     => 'create',
            ),
        ),
        'listings-creation-confirm'            => array(
            'sell/confirm',
            array(
                'controller' => 'listing',
                'action'     => 'confirm',
            ),
        ),
        'listings-create-similar'              => array(
            'sell/similar/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'similar',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-edit'                        => array(
            'edit/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'edit',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-list-draft'                  => array(
            'list-draft/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'list-draft',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-search'                      => array(
            'search',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
        ),
        'listings-all'                         => array(
            'browse',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
        ),
        'listings-filtered-type'               => array(
            'browse/:show',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
        ),

        'listings-categories'                  => array(
            'categories',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
        ),
        'listings-categories-browse'           => array(
            'categories/:category_name/:parent_id',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
            array(
                'parent_id' => '[\d]+',
            ),
        ),
        'listings-categories-browse-sluggable' => array(
            'categories/:category_slug',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
            array(
                'category_slug' => '[a-zA-Z0-9_\-]+',
            ),
        ),

        'listings-browse-category'             => array(
            'category/:category_name/:parent_id',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
            array(
                'parent_id' => '[\d]+',
            ),
        ),
        'listings-browse-category-sluggable'   => array(
            'category/:category_slug',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
            array(
                'category_slug' => '[a-zA-Z0-9_\-]+',
            ),
        ),
        'listings-browse-store'                => array(
            'store/:name/:user_id',
            array(
                'controller' => 'browse',
                'action'     => 'index',
                'show'       => 'store',
            ),
            array(
                'user_id' => '[\d]+',
            ),
        ),
        'listings-browse-store-sluggable'      => array(
            'store/:store_slug',
            array(
                'controller' => 'browse',
                'action'     => 'index',
                'show'       => 'store',
            ),
        ),
        'listings-browse-other-items'          => array(
            'other-items/:username/:user_id',
            array(
                'controller' => 'browse',
                'action'     => 'index',
                'show'       => 'other-items',
            ),
            array(
                'user_id' => '[\d]+',
            ),
        ),
        'listings-listing-details'             => array(
            'listing/:name/:id',
            array(
                'controller' => 'listing',
                'action'     => 'details',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-advanced-search'             => array(
            'advanced-search',
            array(
                'controller' => 'search',
                'action'     => 'advanced',
            ),
        ),
        // purchase forms
        'listings-bid'                         => array(
            'bid/:action/:id',
            array(
                'controller' => 'purchase',
                'type'       => 'bid',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-buy'                         => array(
            'buy/:action/:id',
            array(
                'controller' => 'purchase',
                'type'       => 'buy',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-offer'                       => array(
            'offer/:action/:id',
            array(
                'controller' => 'purchase',
                'type'       => 'offer',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-cart'                        => array(
            'cart',
            array(
                'controller' => 'cart',
                'action'     => 'index',
            ),
        ),
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
);
