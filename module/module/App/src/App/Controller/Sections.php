<?php

/**
 *
 * PHP Pro Bid $Id$ 4TV3EfU1KXl/onwvvSZx3ZkLd2lZ0GCqSG3qlP8qhpI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * content sections controller
 */

namespace App\Controller;

use Cube\Controller\Front,
    Ppb\Controller\Action\AbstractAction,
    Ppb\Service;

class Sections extends AbstractAction
{

    public function View()
    {
        $id = $this->getRequest()->getParam('id');

        $contentPagesService = new Service\ContentPages();

        $select = $contentPagesService->getTable()->select()
            ->order(array('order_id ASC', 'title ASC'));

        $container = null;
        $leftSideContainer = null;

        if ($id) {
            $contentSectionsService = new Service\Table\Relational\ContentSections();

            $container = $contentSectionsService->getData()->findOneBy('id', $id);
            $metaTitle = ($container->get('meta_title')) ?
                $container->get('meta_title') : implode(' > ', $contentSectionsService->getBreadcrumbs($id));
            $metaDescription = $container->get('meta_description');

            $root = $contentSectionsService->getRoot($id);
            $leftSideContainer = $contentSectionsService->getData()->findOneBy('id', $root['id']);

            $bootstrap = Front::getInstance()->getBootstrap();
            $locale = $bootstrap->getResource('locale')->getLocale();

            // META TAGS
            $view = $bootstrap->getResource('view');
            $view->headTitle()->prepend(strip_tags($metaTitle));
            if ($metaDescription) {
                $view->headMeta()->setName('description', strip_tags($metaDescription));
            }

            $select->where('section_id = ?', $id)
                ->where('language = ?', $locale);
        }
        else {
            $this->_helper->redirector()->redirect('index', 'index');
        }

        return array(
            'container'         => $container,
            'leftSideContainer' => $leftSideContainer,
            'pages'             => $contentPagesService->fetchAll($select),
        );
    }
}

