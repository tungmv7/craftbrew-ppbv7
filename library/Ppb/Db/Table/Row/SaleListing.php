<?php

/**
 *
 * PHP Pro Bid $Id$ cYd4xWUkCSDgwCjw1Y2eXYpVmttUSu5ubWQzaHEaeeQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * sales listings table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service,
    Cube\Controller\Front,
    Cube\Crypt;

class SaleListing extends AbstractRow
{

    /**
     *
     * crypt object
     *
     * @var \Cube\Crypt
     */
    protected $_crypt;

    /**
     *
     * set crypt object
     *
     * @param \Cube\Crypt $crypt
     *
     * @return $this
     */
    public function setCrypt(Crypt $crypt)
    {
        $this->_crypt = $crypt;

        return $this;
    }

    /**
     *
     * get crypt object
     *
     * @return \Cube\Crypt
     */
    public function getCrypt()
    {
        if (!$this->_crypt instanceof Crypt) {
            $options = Front::getInstance()->getOption('session');

            $crypt = new Crypt();
            $crypt->setKey($options['secret']);

            $this->setCrypt($crypt);
        }

        return $this->_crypt;
    }

    /**
     *
     * return the price of a sale listing, after applying the voucher code
     *
     * @param boolean $applyVoucher
     *
     * @return float
     */
    public function price($applyVoucher = false)
    {
        $price = $this->getData('price');

        if ($applyVoucher) {
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->findParentRow('\Ppb\Db\Table\Sales');
            $voucher = $sale->getVoucher();

            if ($voucher instanceof Voucher) {
                $price = $voucher->apply($price, $sale['currency'], $this->getData('listing_id'));
            }
        }

        return $price;
    }

    /**
     *
     * calculates the total amount of a sale row
     *
     * @param boolean $applyVoucher
     *
     * @return float
     */
    public function calculateTotal($applyVoucher)
    {
        return ($this->price($applyVoucher) * $this->getData('quantity'));
    }

    /**
     *
     * get digital downloads rowset or false if no downloads are available
     *
     * @param int $listingMediaId the id of a specific download file we want to retrieve
     *
     * @return \Ppb\Db\Table\Rowset\ListingsMedia|false
     */
    public function getDigitalDownloads($listingMediaId = null)
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->findParentRow('\Ppb\Db\Table\Listings');
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->findParentRow('\Ppb\Db\Table\Sales');

        if ($listing) {
            $select = $this->getTable()->select()
                ->where('type = ?', Service\ListingsMedia::TYPE_DOWNLOAD)
                ->order('id DESC');

            if ($listingMediaId) {
                $select->where('id = ?', $listingMediaId);
            }

            $rowset = $listing->findDependentRowset('\Ppb\Db\Table\ListingsMedia', null, $select);

            $downloadsData = $this->getDownloadsData();

            // we get all downloads associated with the listing
            if (count($rowset)) {
                foreach ($rowset as $row) {
                    if (!$sale->isActive() || !$this->getData('downloads_active')) {
                        $row['active'] = 0;
                        $row['download_link'] = null;
                    }
                    else {
                        $row['active'] = isset($downloadsData[$row['id']]['active']) ?
                            $downloadsData[$row['id']]['active'] : $this->getData('downloads_active');
                        $row['download_link'] = array(
                            'module'     => 'members',
                            'controller' => 'buying',
                            'action'     => 'download',
                            'key'        => $this->_generateDownloadKey($row['id'], $this->getData('id')),
                        );
                    }

                    $row['nb_downloads'] = isset($downloadsData[$row['id']]['nb_downloads']) ?
                        $downloadsData[$row['id']]['nb_downloads'] : 0;
                }

                if ($listingMediaId) {
                    return $rowset->getRow(0);
                }

                return $rowset;
            }
        }

        return false;
    }

    /**
     *
     * update downloads data serializable field
     *
     * @param int  $listingMediaId
     * @param int  $active
     * @param bool $download
     *
     * @return $this
     */
    public function updateDownloadsData($listingMediaId, $active = null, $download = false)
    {
        $downloadsData = $this->getDownloadsData();

        if ($active !== null) {
            $downloadsData[$listingMediaId]['active'] = $active;
        }
        if ($download !== false) {
            $downloadsData[$listingMediaId]['nb_downloads']++;
        }

        $this->save(array(
            'downloads_data' => $downloadsData,
        ));

        return $this;
    }

    /**
     *
     * get download data for a certain listing media id
     *
     * @param int $listingMediaId
     *
     * @return array|null
     */
    public function getDownloadsData($listingMediaId = null)
    {
        $downloadsData = \Ppb\Utility::unserialize($this->getData('downloads_data'), array());

        if ($listingMediaId !== null) {
            return isset($downloadsData[$listingMediaId]) ? $downloadsData[$listingMediaId] : null;
        }

        return $downloadsData;
    }

    /**
     *
     * count a downloaded file
     *
     * @param $listingMediaId
     *
     * @return $this
     */
    public function countDownload($listingMediaId)
    {
        $downloadsData = $this->getDownloadsData();

        if (array_key_exists($listingMediaId, $downloadsData)) {
            if (array_key_exists('nb_downloads', $downloadsData[$listingMediaId])) {
                $downloadsData[$listingMediaId]['nb_downloads']++;
            }
            else {
                $downloadsData[$listingMediaId]['nb_downloads'] = 1;
            }
        }
        else {
            $downloadsData[$listingMediaId] = array(
                'nb_downloads' => 1,
            );
        }

        $this->save(array(
            'downloads_data' => serialize($downloadsData)
        ));

        return $this;
    }

    /**
     *
     * delete the sale listing row and its parent sale it has no other listings in it
     *
     * @return int
     */
    public function delete()
    {
        $salesListings = $this->_table->fetchAll(
            $this->_table->select()
                ->where('sale_id = ?', $this->getData('sale_id'))
                ->where('id != ?', $this->getData('id')));

        if (!count($salesListings)) {
            $this->findParentRow('\Ppb\Db\Table\Sales')->delete(true);
        }

        return parent::delete();
    }

    /**
     *
     * generate an encrypted download key
     *
     * @param int $listingMediaId
     * @param int $saleListingId
     *
     * @return string
     */
    protected function _generateDownloadKey($listingMediaId, $saleListingId)
    {
        return $this->getCrypt()->encrypt(
            $listingMediaId . Service\Table\SalesListings::KEY_SEPARATOR . $saleListingId);
    }
}

