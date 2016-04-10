<?php

/**
 *
 * GLOBAL CONFIG
 * ===============
 * global configuration file
 *
 * - initialize modules
 * - initialize global resources (db, session, [cache] etc)
 * - module settings will override global settings
 *
 */
return array(
    'modules'    => array(
        'App',
        'Admin',
        'Members',
        'Listings',
        'Install',
        'Home'
    ),
    'locale'     => array(
        'default' => 'en_US',
    ),
    'db'         => array(
        'adapter'  => '\\Cube\\Db\\Adapter\\PDO\\Mysql',
        'host'     => 'localhost',
        'dbname'   => 'craftbrews',
        'username' => 'root',
        'password' => '123@cms',
        'prefix'   => 'ppb_',
        'charset'  => 'utf8'
    ),
    'cache'      => array(
        'folder'   => __DIR__ . '/../cache',
        'queries'  => false,
        'metadata' => true,
    ),
    /* mail is global for all modules */
    'mail'       => array(
        'transport'    => 'smtp',
        'protocol' => 'ssl',
        'layouts_path' => __DIR__ . '/../themes/standard',
        'views_path'   => __DIR__ . '/../module/App/view/emails',
        'layout_file'  => 'email.phtml',
    ),
    /* navigation is global for all modules except Admin */
    'navigation' => array(
        'data_type'  => 'xml',
        'data_file'  => __DIR__ . '/../module/App/config/data/navigation/navigation.xml',
        'views_path' => __DIR__ . '/../module/App/view',
    ),
    /* session is global for all modules except Admin */
    'session'    => array(
        'namespace' => 'eCsycUGK',
        'secret'    => 'KWcObqpJ',
    ),
    /* set folders used by the application (relative paths) */
    'folders'    => array(
        'themes'  => 'themes', // themes folder (relative path)
        'img'     => 'img', // global images folder (relative path)
        'uploads' => 'uploads', // media uploads folder
        'cache'   => 'uploads/cache', // media uploads folder
    ),
    /* set paths used by the application (absolute) */
    'paths'      => array(
        'base'      => __DIR__ . '/..', // base path of the application
        'languages' => __DIR__ . '/data/language', // languages folder
        'themes'    => __DIR__ . '/../themes',
        'img'       => __DIR__ . '/../img', // global images folder
        'uploads'   => __DIR__ . '/../uploads', // media uploads folder
        'cache'     => __DIR__ . '/../uploads/cache', // cached images folder
    ),
    'translate'  => array(
        'adapter'      => '\\Ppb\\Translate\\Adapter\\Composite',
        'translations' => array(
            array(
                'locale'  => 'en_US',
                'path'    => __DIR__ . '/data/language/en_US',
                'img'     => 'flags/en_US.png',
                'desc'    => 'English',
                'sources' => array(
                    array(
                        'adapter'   => '\\Cube\\Translate\\Adapter\\Gettext',
                        'extension' => 'mo',
                    ),
                    array(
                        'adapter'   => '\\Cube\\Translate\\Adapter\\ArrayAdapter',
                        'extension' => 'php',
                    ),
                ),
            ),
        ),
    ),
);
