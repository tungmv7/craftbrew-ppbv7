<?php

/**
 *
 * PHP Pro Bid $Id$ 0AcuLoOoL0ECbczuW5cSAwGgPnp5UUCmJIRu3TeA9Kc=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Paginator,
    Ppb\Service,
    Ppb\Service\Table\Relational\Categories as CategoriesService;

class SiteContent extends AbstractAction
{

    /**
     *
     * content pages service
     *
     * @var \Ppb\Service\ContentPages
     */
    protected $_contentPages;

    /**
     *
     * advertising service
     *
     * @var \Ppb\Service\Advertising
     */
    protected $_advertising;

    public function init()
    {
        $this->_contentPages = new Service\ContentPages();
        $this->_advertising = new Service\Advertising();
    }

    public function Pages()
    {
        $title = $this->getRequest()->getParam('title');

        $select = $this->_contentPages->getTable()->select()
            ->order(array('order_id ASC', 'created_at DESC'));

        if ($title !== null) {
            $params = '%' . str_replace(' ', '%', $title) . '%';
            $select->where('title LIKE ?', $params);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbSelect($select));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => 'Site Content',
            'title'      => $title,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }


    public function AddPage()
    {
        $this->_forward('edit-page');
    }

    public function EditPage()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $data = $this->_contentPages->findBy('id', $id)->toArray();
        }

        $form = new \Admin\Form\ContentPage();

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {

                $this->_contentPages->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The content page has been edited successfully') :
                        $this->_('The content page has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('pages');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
        );
    }

    public function DeletePage()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_contentPages->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Content Page ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The content page could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('pages', null, null, $this->getRequest()->getParams());
    }

    public function Advertising()
    {
        if ($this->getRequest()->isPost()) {
            $this->_advertising->saveSettings(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The adverts settings have been updated.'),
                'class' => 'alert-success',
            ));
        }

        $select = $this->_advertising->getTable()->select()
            ->order(array('created_at DESC'));

        $paginator = new Paginator(
            new Paginator\Adapter\DbSelect($select));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        $sections = $this->_advertising->getSections();

        $categoriesService = new CategoriesService();

        return array(
            'paginator'         => $paginator,
            'messages'          => $this->_flashMessenger->getMessages(),
            'controller'        => 'Site Content',
            'categoriesService' => $categoriesService,
            'sections'          => $sections,
        );
    }

    public function CreateAdvert()
    {
        $this->_forward('edit-advert');
    }

    public function EditAdvert()
    {
        $params = array();

        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $params = $this->_advertising->findBy('id', $id)->toArray();
        }

        if ($this->getRequest()->isPost()) {
            $params = array_merge(
                $params, $this->getRequest()->getParams());
        }

        $type = isset($params['type']) ? $params['type'] : null;
        $form = new \Admin\Form\Advert($type);

        if ($id) {
            $form->generateEditForm();
        }

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $this->_advertising->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The advert has been edited successfully') :
                        $this->_('The advert has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('advertising');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
        );
    }

    public function DeleteAdvert()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_advertising->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Advert ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The advert could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('advertising', null, null, $this->getRequest()->getParams());
    }

    public function PreviewAdvert()
    {
        $this->_setNoLayout();

        $advert = $this->_advertising->findBy('id', $this->getRequest()->getParam('id'));

        return array(
            'advert' => $advert,
        );
    }


    public function Emails()
    {
        $fileName = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);

        $text = null;
        if ($fileName) {
            if ($this->getRequest()->getParam('save')) {
                $result = @file_put_contents($fileName, filter_input(INPUT_POST, 'text', FILTER_UNSAFE_RAW));

                if ($result) {
                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_("The %s email file has been edited successfully."), $fileName),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('Error: could not edit the email file. Please check for write permissions.'),
                        'class' => 'alert-danger',
                    ));
                }
            }

            $text = file_get_contents($fileName);
        }

        return array(
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
            'email'      => $fileName,
            'text'       => $text,
        );
    }

    public function Languages()
    {
        $language = $this->getRequest()->getParam('language');

        $data = null;
        if ($language) {
            $fileName = \Ppb\Utility::getPath('languages') . DIRECTORY_SEPARATOR . $language . '.php';

            if ($this->getRequest()->getParam('save_language')) {
                $keys = explode(PHP_EOL, filter_input(INPUT_POST, 'langDataKeys', FILTER_UNSAFE_RAW));
                $values = explode(PHP_EOL, filter_input(INPUT_POST, 'langData', FILTER_UNSAFE_RAW));

                $contents = '<?php' . "\n"
                    . "return array(" . "\n";

                foreach ($keys as $id => $key) {
                    $k = rtrim(str_replace(array('"', "\n", "\r\n"), array('&quot;', '', ''), $key));
                    $val = (!empty($values[$id])) ? $values[$id] : '';
                    $v = rtrim(str_replace(array('"', "\n", "\r\n"), array('&quot;', '', ''), $val));

                    $contents .= '"' . $k . '" => "' . $v . '", ' . "\n";
                }
                $contents .= ');';

                $result = @file_put_contents($fileName, $contents);

                if ($result) {
                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_("The %s language file has been edited successfully."),
                            $language),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('Error: could not edit the language file. Please check for write permissions.'),
                        'class' => 'alert-danger',
                    ));
                }
            }

            $data = include $fileName;
            $data = (array)$data + $this->_fetchDynamicLanguageData();

        }


        return array(
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
            'language'   => $language,
            'data'       => $data,
        );
    }

    /**
     * fetches all dynamic language data:
     * xml:
     * - navigation.xml files (admin & app) [@7.5 modification - navigation labels are translated in .po]
     * tables:
     * - categories [name]
     * - locations [name]
     * - offline payment methods [name]
     * - durations [description]
     * - currencies [description]
     * - shipping_options [name]
     * - stores_subscriptions [name]
     * - tax_types [name, description]
     * - content_sections [name]
     * - custom_fields [label, subtitle, prefix, suffix, description, multiOptions (array)]
     * settings:
     * - [sitename, meta_description, listing_terms_content, cookie_usage_message]
     *
     * return array
     */
    protected function _fetchDynamicLanguageData()
    {
        $data = array();

        $input = array(
//            'xml'      => array(
//                __DIR__ . '/../../../config/data/navigation/navigation.xml',
//                __DIR__ . '/../../../../App/config/data/navigation/navigation.xml',
//            ),
            'table'    => array(
                '\Ppb\Service\Table\Relational\Categories'      => array('name'),
                '\Ppb\Service\Table\Relational\Locations'       => array('name'),
                '\Ppb\Service\Table\Relational\ContentSections' => array('name'),
                '\Ppb\Service\Table\OfflinePaymentMethods'      => array('name'),
                '\Ppb\Service\Table\Durations'                  => array('description'),
                '\Ppb\Service\Table\Currencies'                 => array('description'),
                '\Ppb\Service\Table\StoresSubscriptions'        => array('name'),
                '\Ppb\Service\Table\TaxTypes'                   => array('name', 'description'),
                '\Ppb\Service\CustomFields'                     => array('label', 'description', 'subtitle', 'prefix', 'suffix', 'multiOptions'),
            ),
            'settings' => array(
                'sitename',
//                'meta_description',
                'listing_terms_content',
                'cookie_usage_message',
            ),
        );

        foreach ($input as $type => $array) {
            foreach ($array as $k => $v) {
                switch ($type) {
                    case 'xml':
                        $object = new \Cube\Config\Xml($v);
                        $ar = $object->getData();
                        array_walk_recursive($ar, function (&$value, &$key) use (&$data) {
                            if ($key == 'label') {
                                $data[] = trim(strval($value));
                            }
                        });

                        break;
                    case 'table':
                        /** @var \Ppb\Service\AbstractService $service */
                        $service = new $k();
                        $rowset = $service->fetchAll($service->getTable()->select($v));
                        foreach ($rowset as $row) {
                            foreach ($v as $column) {
                                if ($column == 'multiOptions') {
                                    $multiOptions = \Ppb\Utility::unserialize($row[$column]);
                                    if (!empty($multiOptions['value'])) {
                                        $multiOptionsVal = $multiOptions['value'];
                                        foreach ($multiOptionsVal as $val) {
                                            if (!empty($val)) {
                                                $data[] = strval($val);
                                            }
                                        }
                                    }
                                }
                                else {
                                    $val = strval($row[$column]);
                                    if (!empty($val)) {
                                        $data[] = strval($row[$column]);
                                    }
                                }
                            }
                        }
                        break;
                    case 'settings':
                        $data[] = strval($this->_settings[$v]);
                        break;
                }
            }
        }

        return array_fill_keys(array_keys(array_flip($data)), null);
    }

}

