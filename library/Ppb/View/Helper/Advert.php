<?php

/**
 *
 * PHP Pro Bid $Id$ FcOWpG2sS1u3w6zMSaC9k5E8UJbB6u70wubbZyF/emg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * advert display view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service\Advertising as AdvertisingService,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    Ppb\Db\Table\Row\Advert as AdvertModel,
    Cube\Db\Select,
    Cube\Db\Expr;

class Advert extends AbstractHelper
{

    /**
     *
     * advertising service
     *
     * @var \Ppb\Service\Advertising
     */
    protected $_advertising;

    /**
     *
     * advert model
     *
     * @var \Ppb\Db\Table\Row\Advert
     */
    protected $_advert;

    /**
     *
     * get advert model
     *
     * @return \Ppb\Db\Table\Row\Advert
     * @throws \InvalidArgumentException
     */
    public function getAdvert()
    {
        if (!$this->_advert instanceof AdvertModel) {
            throw new \InvalidArgumentException("The advert model has not been instantiated");
        }

        return $this->_advert;
    }

    /**
     *
     * set advert model
     *
     * @param \Ppb\Db\Table\Row\Advert $advert
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setAdvert(AdvertModel $advert)
    {
        if (!$advert instanceof AdvertModel) {
            throw new \InvalidArgumentException("The advert model must be an instance of \Ppb\Db\Table\Row\Advert");
        }

        $this->_advert = $advert;

        return $this;
    }


    /**
     *
     * get content sections table service
     *
     * @return \Ppb\Service\Advertising
     */
    public function getAdvertising()
    {
        if (!$this->_advertising instanceof AdvertisingService) {
            $this->setAdvertising(
                new AdvertisingService());
        }

        return $this->_advertising;
    }

    /**
     *
     * set advertising service
     *
     * @param \Ppb\Service\Advertising $advertising
     *
     * @return $this
     */
    public function setAdvertising(AdvertisingService $advertising)
    {
        $this->_advertising = $advertising;

        return $this;
    }

    /**
     *
     * display the currently selected advert
     *
     * return string|null
     */
    public function display()
    {
        $advert = $this->getAdvert();

        $advert->addView();

        switch ($advert['type']) {
            case 'image':
                $view = $this->getView();
                $imageSrc = $view->baseUrl . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER . $advert['content'];

                return '<a href="' . $view->url($advert->link()) . '" target="_blank" class="img-advert">
                    <img src="' . $imageSrc . '" alt="Banner" class="img-advert">
                </a>';
                break;
            case 'code':
                return $advert['content'];
                break;
        }

        return null;

    }

    /**
     *
     * main method, only returns object instance
     *
     * @param \Ppb\Db\Table\Row\Advert $advert
     *
     * @return $this
     */
    public function advert(AdvertModel $advert = null)
    {
        if ($advert !== null) {
            $this->setAdvert($advert);
        }

        return $this;
    }

    /**
     *
     *
     * return one or an array of advert objects
     *
     * @param string $section
     * @param bool   $all if true, return all adverts from the requested section
     * @param array  $categoryIds
     *
     * @return \Ppb\Db\Table\Row\Advert|\Ppb\Db\Table\Rowset\Adverts|null
     */
    public function findBySection($section, $all = false, $categoryIds = array())
    {
        $select = $this->getAdvertising()->getTable()
            ->select(array('nb_rows' => new Expr('count(*)')))
            ->where('section = ?', $section)
            ->where('active = ?', 1);

        $categoriesFilter = array(0);

        $categoryIds = array_filter($categoryIds);

        if (count($categoryIds) > 0) {
            $categoriesService = new CategoriesService();

            foreach ($categoryIds as $categoryId) {
                $categoriesFilter = array_merge($categoriesFilter, array_keys(
                    $categoriesService->getBreadcrumbs($categoryId)));
            }
        }

        $select->where("category_ids REGEXP '\"" . implode('"|"',
                array_unique($categoriesFilter)) . "\"' OR category_ids = ''");

        $locale = Front::getInstance()->getBootstrap()->getResource('locale')->getLocale();

        $select->where("language = '" . $locale . "' OR language IS NULL");

        $stmt = $select->query();

        $nbAdverts = (integer)$stmt->fetchColumn('nb_rows');

        if (!$nbAdverts) {
            return null;
        }

        $select->reset(Select::COLUMNS)
            ->columns('*');

        if ($all !== true) {
            $select = $select->order(new Expr('rand()'))
                ->limit(1);

            return $this->getAdvertising()->fetchAll($select)->getRow(0);
        }

        return $this->getAdvertising()->fetchAll($select);
    }
}

