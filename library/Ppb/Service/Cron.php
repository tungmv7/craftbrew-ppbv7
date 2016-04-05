<?php

/**
 *
 * PHP Pro Bid $Id$ sAxP+ABTYR0jrNJdArlsyLiZED/4Jz+l3Zj5iN47qYQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * cron jobs service class
 */

namespace Ppb\Service;

use Cube\Controller\Request,
    Cube\Db\Adapter\AbstractAdapter,
    Cube\Db\Expr,
    Ppb\Db\Table,
    Cube\Config,
    Ppb\Service\Table\SalesListings as SalesListingsService;

class Cron extends AbstractService
{
    /**
     * maximum number of rows to be selected in a transaction
     */
    const SELECT_TRANSACTION_LIMIT = 50;

    /**
     * old cache files removal time limit = 3 days (in seconds) (7 days = 604800)
     */
    const CACHE_FILES_REMOVAL_TIME_LIMIT = 259200;

    /**
     * unused files from the uploads folder are to be removed after 6 hours
     */
    const UNUSED_FILES_REMOVAL_TIME_LIMIT = 21600;

    /**
     * default number of days after which unused autocomplete tags are removed
     */
    const AUTOCOMPLETE_TAGS_PURGE_DAYS = 60;

    /**
     * default number of days when the subscription expiration emails are to be sent
     */
    const DEFAULT_EXPIRATION_DAYS = 3;

    /**
     * number of minutes after which data is purged from the users statistics table for online users.
     */
    const ONLINE_USERS_STATS_PURGE = 5;

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * sales service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * sales listings service
     *
     * @var \Ppb\Service\Table\SalesListings
     */
    protected $_salesListings;

    /**
     *
     * get listings service
     *
     * @return \Ppb\Service\Listings
     */
    public function getListings()
    {
        if (!$this->_listings instanceof Listings) {
            $this->setListings(
                new Listings());
        }

        return $this->_listings;
    }

    /**
     *
     * set listings service
     *
     * @param \Ppb\Service\Listings $listings
     *
     * @return $this
     */
    public function setListings(Listings $listings)
    {
        $this->_listings = $listings;

        return $this;
    }

    /**
     *
     * get users service
     *
     * @return \Ppb\Service\Users
     */
    public function getUsers()
    {
        if (!$this->_users instanceof Users) {
            $this->setUsers(
                new Users());
        }

        return $this->_users;
    }

    /**
     *
     * set users service
     *
     * @param \Ppb\Service\Users $users
     *
     * @return $this
     */
    public function setUsers(Users $users)
    {
        $this->_users = $users;

        return $this;
    }

    /**
     *
     * get sales service
     *
     * @return \Ppb\Service\Sales
     */
    public function getSales()
    {
        if (!$this->_sales instanceof Sales) {
            $this->setSales(
                new Sales());
        }

        return $this->_sales;
    }

    /**
     *
     * set sales service
     *
     * @param \Ppb\Service\Sales $sales
     *
     * @return $this
     */
    public function setSales(Sales $sales)
    {
        $this->_sales = $sales;

        return $this;
    }

    /**
     *
     * get sales listings service
     *
     * @return \Ppb\Service\Table\SalesListings
     */
    public function getSalesListings()
    {
        if (!$this->_salesListings instanceof SalesListingsService) {
            $this->setSalesListings(
                new SalesListingsService());
        }

        return $this->_salesListings;
    }

    /**
     *
     * set sales listings service
     *
     * @param \Ppb\Service\Table\SalesListings $salesListings
     *
     * @return $this
     */
    public function setSalesListings(SalesListingsService $salesListings)
    {
        $this->_salesListings = $salesListings;

        return $this;
    }

    /**
     *
     * close expired listings and assign winners
     * limit to 50 per transaction
     * forUpdate means that the rows will be locked until the transaction is complete
     *
     * @return $this
     */
    public function closeExpiredListings()
    {
        $listingsService = $this->getListings();
        $usersService = $this->getUsers();

        $select = $listingsService->getTable()->select()
            ->forUpdate()
            ->where('closed = ?', 0)
            ->where('deleted = ?', 0)
            ->where('draft = ?', 0)
            ->where('start_time < ?', new Expr('now()'))
            ->where('end_time < ?', new Expr('now()'))
            ->where('end_time is not null')
            ->limit(self::SELECT_TRANSACTION_LIMIT);

        $expiredListings = $listingsService->fetchAll($select)
            ->setAutomatic(true)
            ->close();

        $noSale = array();
        $noSaleReserve = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($expiredListings as $listing) {
            if ($listing->getClosedFlag() === true) {
                $saleId = $listing->assignWinner();

                if ($saleId === false) {
                    if ($listing->countDependentRowset('\Ppb\Db\Table\Bids')) {
                        $noSaleReserve[$listing['user_id']][] = $listing;
                    }
                    else {
                        $noSale[$listing['user_id']][] = $listing;
                    }
                }
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($noSale as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->noSale()
                ->send();
        }

        foreach ($noSaleReserve as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->noSaleReserve()
                ->send();
        }

        $expiredListings->relist();


        return $this;
    }

    /**
     *
     * start scheduled listings
     *
     * @return $this
     */
    public function startScheduledListings()
    {
        $listingsService = $this->getListings();
        $adapter = $listingsService->getTable()->getAdapter();

        $where = array(
            $adapter->quoteInto('closed = ?', 1),
            $adapter->quoteInto('deleted = ?', 0),
            $adapter->quoteInto('draft = ?', 0),
            $adapter->quoteInto('start_time < ?', new Expr('now()')),
            'end_time > ' . new Expr('now()') . ' OR end_time is null',
        );

        $listingsService->getTable()->update(array('closed' => 0, 'updated_at' => new Expr('now()')), $where);

        return $this;
    }

    /**
     *
     * method that deletes old cache images files (files that are more than 3 days old)
     *
     * @return $this
     */
    public function purgeOldCacheFiles()
    {
        $counter = null;
        $cacheFolder = \Ppb\Utility::getPath('cache');

        if ($handle = opendir($cacheFolder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $filePath = $cacheFolder . DIRECTORY_SEPARATOR . $file;
                    $stat = stat($filePath);

                    if (($stat['mtime'] + self::CACHE_FILES_REMOVAL_TIME_LIMIT) < time()) {
                        @unlink($filePath);
                    }
                }
            }
            closedir($handle);
            clearstatcache();
        }

        return $this;
    }

    /**
     *
     * delete expired rows from the sales listings table for which the
     * corresponding sale is marked as pending
     *
     * @return $this
     */
    public function deletePendingSalesListings()
    {
        $settings = $this->getSettings();
        $salesListingsService = $this->getSalesListings();

        $select = $salesListingsService->getTable()->getAdapter()
            ->select()
            ->from(array('sl' => 'sales_listings'), 'sl.id')
            ->joinLeft(array('s' => 'sales'), 's.id = sl.sale_id', '')
            ->where('sl.created_at < ?',
                new Expr('(now() - interval ' . intval($settings['pending_sales_listings_expire_hours']) . ' minute)'))
            ->where('s.pending = ?', 1);

        $rows = $salesListingsService->fetchAll($select);

        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }

        $salesListingsService->delete($ids);

        return $this;
    }

    /**
     *
     * suspend users for which the debit balance date has been exceeded
     *
     * @return $this
     */
    public function suspendUsersDebitExceededDate()
    {
        $usersService = $this->getUsers();
        $settings = $this->getSettings();

        if ($settings['user_account_type'] == 'personal' || $settings['payment_mode'] == 'account') {
            $select = $usersService->getTable()->select()
                ->where('active = ?', 1)
                ->where('role NOT IN (?)', array_keys(Users::getAdminRoles()))
                ->where('balance > max_debit')
                ->where('debit_exceeded_date is not null')
                ->where('debit_exceeded_date < ?',
                    new Expr('(now() - interval ' . intval($settings['suspension_days']) . ' day)'))
                ->limit(self::SELECT_TRANSACTION_LIMIT);

            if ($settings['user_account_type'] == 'personal') {
                $select->where('account_mode = ?', 'account');
            }

            $users = $usersService->fetchAll($select);

            $mail = new \Members\Model\Mail\User();

            /** @var \Ppb\Db\Table\Row\User $user */
            foreach ($users as $user) {
                $user->updateActive(0);
                $mail->accountBalanceExceeded($user)->send();
            }
        }

        return $this;
    }

    public function sendNewsletters()
    {
        $newslettersRecipientsService = new NewslettersRecipients();
        $newslettersService = new Newsletters();

        $recipients = $newslettersRecipientsService->fetchAll(
            $newslettersRecipientsService->getTable()->getAdapter()->select()
                ->from(array('nr' => 'newsletters_recipients'))
                ->joinLeft(array('n' => 'newsletters'), 'n.id = nr.newsletter_id', array('title', 'content'))
                ->joinLeft(array('u' => 'users'), 'u.id = nr.user_id', array('username', 'email'))
                ->limit(200));

        $mail = new \Members\Model\Mail\User();

        $ids = array();
        $newslettersIds = array();

        /** @var \Cube\Db\Table\Row $data */
        foreach ($recipients as $data) {
            $ids[] = $data['id'];
            $newslettersIds[] = $data['newsletter_id'];
            $mail->newsletter($data['title'], $data['content'], $data['email'], $data['username'])->send();
        }

        if (count($ids)) {
            $newslettersRecipientsService->delete($ids);
            $adapter = $newslettersService->getTable()->getAdapter();
            $newslettersService->getTable()->update(array(
                'updated_at' => new Expr('now()')
            ),
                $adapter->quoteInto('id IN (?)', array_unique($newslettersIds)));
        }
    }

    /**
     *
     * remove marked deleted listings from the database
     *
     * @return $this
     */
    public function removeMarkedDeletedListings()
    {
        $listingsService = $this->getListings();

        $select = $listingsService->getTable()->select()
            ->where('deleted = ?', 1)
            ->limit(self::SELECT_TRANSACTION_LIMIT);

        $listingsService->fetchAll($select)->setAdmin(true)->delete();

        return $this;
    }

    /**
     *
     * mark as deleted closed listings
     *
     * @return $this
     */
    public function markDeletedClosedListings()
    {
        $listingsService = $this->getListings();
        $settings = $this->getSettings();

        $adapter = $listingsService->getTable()->getAdapter();
        $where = array(
            'end_time is not null',
            $adapter->quoteInto('deleted = ?', 0),
            $adapter->quoteInto('closed = ?', 1),
            $adapter->quoteInto('end_time < ?',
                new Expr('(now() - interval ' . intval($settings['closed_listings_deletion_days']) . ' day)'))
        );

        $listingsService->getTable()->update(array(
            'deleted' => 1,
        ), $where);

        return $this;
    }


    /**
     *
     * notify users on subscriptions that are about to expire
     * only notify users for which the "re-bill if in account mode" setting doesnt apply
     *
     * @param array $subscription
     *
     * @return $this
     */
    public function notifyUsersOnSubscriptionsAboutToExpire(array $subscription)
    {
        $usersService = $this->getUsers();
        $settings = $this->getSettings();

        $select = $usersService->getTable()->select()
            ->where("{$subscription['active']} = ?", 1)
            ->where("{$subscription['expirationDate']} < ?",
                new Expr('(now() + interval ' . self::DEFAULT_EXPIRATION_DAYS . ' day)'))
            ->where("{$subscription['expirationDate']} > ?", 0)
            ->where("{$subscription['emailFlag']} = ?", 0)
            ->limit(self::SELECT_TRANSACTION_LIMIT);

        $runQuery = true;

        if ($settings['rebill_expired_subscriptions']) {
            if ($settings['user_account_type'] == 'personal') {
                $select->where('account_mode = ?', 'live');
            }
            else if ($settings['user_account_type'] == 'global' && $settings['payment_mode'] == 'account') {
                    $runQuery = false;
            }
        }

        if ($runQuery) {
            $users = $usersService->fetchAll($select);

            /** @var \Ppb\Db\Table\Row\User $user */
            $mail = new \Members\Model\Mail\User();

            foreach ($users as $user) {
                $mail->subscriptionExpirationNotification($subscription, $user, self::DEFAULT_EXPIRATION_DAYS)->send();

                $user->save(array(
                    "{$subscription['emailFlag']}" => 1,
                ));
            }
        }

        return $this;
    }

    /**
     *
     * process expired user subscriptions
     * notify users of the expired subscription
     * re-bill if in account mode, and activate automatically
     *
     * @param array $subscription subscription type
     *
     * @return $this
     */
    public function processExpiredSubscriptions(array $subscription)
    {
        $usersService = $this->getUsers();
        $accountingService = new Accounting();
        $settings = $this->getSettings();

        $select = $usersService->getTable()->select()
            ->where("{$subscription['active']} = ?", 1)
            ->where("{$subscription['expirationDate']} < ?", new Expr('now()'))
            ->where("{$subscription['expirationDate']} > ?", 0)
            ->limit(self::SELECT_TRANSACTION_LIMIT);

        $users = $usersService->fetchAll($select);

        $mail = new \Members\Model\Mail\User();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($users as $user) {
            $user->save(array(
                "{$subscription['active']}" => 0
            ));

            if ($settings['rebill_expired_subscriptions'] && $user->userPaymentMode() == 'account') {
                // bill subscription in account mode
                /** @var \Ppb\Service\Fees $feesService */
                $feesService = new $subscription['feesService']($user);
                $totalAmount = $feesService->getTotalAmount();

                $user->save(array(
                    'balance' => ($user['balance'] + $totalAmount)
                ));

                $accountingService->save(array(
                    'name'     => array(
                        'string' => 'Automatic Renewal -  %s',
                        'args'   => array($subscription['name']),
                    ),
                    'amount'   => $totalAmount,
                    'user_id'  => $user['id'],
                    'currency' => $settings['currency'],
                ));
                // activate subscription
                $user->$subscription['updateMethod'](1);
                $mail->subscriptionRenewed($subscription, $user)->send();
            }
            else {
                $mail->subscriptionExpired($subscription, $user)->send();
            }
        }

        return $this;
    }

    /**
     *
     * delete sales for which the payment due time limit has expired
     * (ref: force payment module)
     *
     * return $this
     */
    public function deleteExpiredSalesForcePayment()
    {
        $salesService = $this->getSales();

        $select = $salesService->getTable()->select()
            ->where('expires_at is not null')
            ->where('expires_at < ?', new Expr('now()'))
            ->limit(self::SELECT_TRANSACTION_LIMIT);

        $sales = $salesService->fetchAll($select);

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($sales as $sale) {
            $sale->revert();
        }

        return $this;
    }

    /**
     *
     * count listings that were not counted since their creation / last update
     *
     * return $this
     */
    public function countListings()
    {
        $listingsService = $this->getListings();

        $select = $listingsService->getTable()->select()
            ->where('counted_at is null OR counted_at < IF(updated_at is null, created_at, updated_at)')
            ->limit(100);

        $listings = $listingsService->fetchAll($select);

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $listing->processCategoryCounter();
        }

        return $this;
    }

    /**
     *
     * remove search phrases that haven't been searched for within the last 6 months
     *
     * @return $this
     */
    public function purgeAutocompleteTags()
    {
        $autocompleteTagsService = new AutocompleteTags();

        $adapter = $autocompleteTagsService->getTable()->getAdapter();

        $where = array(
            $adapter->quoteInto('created_at < ?', new Expr('now() - interval 6 month')),
            $adapter->quoteInto('updated_at < ?', new Expr('now() - interval 6 month')),
        );

        $autocompleteTagsService->getTable()->delete($where);

        return $this;
    }


    /**
     *
     * remove expired rows from the users statistics table
     *
     * @return $this
     */
    public function purgeUsersStatistics()
    {
        $usersStatisticsService = new UsersStatistics();

        $adapter = $usersStatisticsService->getTable()->getAdapter();

        $where = array(
            $adapter->quoteInto('updated_at < ?', new Expr('now() - interval ' . self::ONLINE_USERS_STATS_PURGE . ' minute')),
        );

        $usersStatisticsService->getTable()->delete($where);

        return $this;
    }

    /**
     *
     * remove expired rows from the recently viewed listings table
     *
     * @return $this
     */
    public function purgeRecentlyViewedListings()
    {
        $settings = $this->getSettings();

        $recentlyViewedListingsService = new RecentlyViewedListings();

        $adapter = $recentlyViewedListingsService->getTable()->getAdapter();

        $where = array(
            $adapter->quoteInto('IF(updated_at is null, created_at, updated_at) < ?',
                new Expr('now() - interval ' . intval($settings['enable_recently_viewed_listings_expiration']) . ' hour')),
        );

        $recentlyViewedListingsService->getTable()->delete($where);

        return $this;
    }

    /**
     *
     * method that deletes all unused uploaded files that are older than UNUSED_FILES_REMOVAL_TIME_LIMIT
     * this is to be called by a separate cron call to avoid server load
     * currently it processes 200 images per call
     *
     * @return $this
     */
    public function purgeUnusedUploadedFiles()
    {
        $counter = null;
        $uploadsFolder = \Ppb\Utility::getPath('uploads');

        // we have sorted all files by modified date
        $files = glob($uploadsFolder . '/*.*');
        usort($files, function ($a, $b) {
            return filemtime($a) > filemtime($b);
        });

        $files = array_filter($files);
        $files = array_slice($files, 0, 200);

        $prefix = $this->getListings()->getTable()->getPrefix();
        $adapter = $this->getListings()->getTable()->getAdapter();

        foreach ($files as $filePath) {
            $file = str_replace($uploadsFolder . DIRECTORY_SEPARATOR, '', $filePath);
            $stat = stat($filePath);

            if (($stat['mtime'] + self::UNUSED_FILES_REMOVAL_TIME_LIMIT) < time()) {
                if ($this->_isUsedFile($file, $adapter, $prefix) !== true) {
                    @unlink($filePath);
                }
                else {
                    @touch($filePath);
                }
            }
        }
        clearstatcache();

        return $this;
    }

    /**
     *
     * gets live currency exchange rates and updates the currencies table
     * always gets based on the site's default currency
     *
     * @return $this
     */
    public function updateCurrencyExchangeRates()
    {
        $settings = $this->getSettings();

        $feed = 'http://www.floatrates.com/daily/' . strtolower($settings['currency']) . '.xml';
        $xml = simplexml_load_file($feed);

        $object = new Config\Xml($xml);

        $currencies = $object->getData('item');

        $data = array();

        foreach ($currencies as $currency) {
            $isoCode = $currency['targetCurrency'];
            $conversionRate = number_format($currency['exchangeRate'], 6, '.', '');

            if ($isoCode && $conversionRate > 0) {
                $data[] = array(
                    'data'  => array(
                        'conversion_rate' => $conversionRate,
                    ),
                    'where' => array(
                        "iso_code = '" . $isoCode . "'",
                    )
                );
            }
        }

        $currenciesService = new Table\Currencies();

        foreach ($data as $row) {
            $currenciesService->update($row['data'], $row['where']);
        }

        // reset default currency exchange rate to 1
        $currenciesService->update(array("conversion_rate" => 1), "iso_code = '" . $settings['currency'] . "'");

        return $this;
    }

    /**
     *
     * check if a file from the /uploads/ folder is used by the software
     * the following tables will be checked:
     *
     * listings_media [value]
     * categories [logo_path]
     * advertising [content]
     * settings [value : site_logo_path]
     * users [store_settings : PREG : s:15:"store_logo_path";s:(d+):"FILENAME"]
     *
     * @param string                           $file
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     * @param string                           $prefix
     *
     * @return bool
     */
    protected function _isUsedFile($file, AbstractAdapter $adapter, $prefix)
    {
        $statement = $adapter->query("SELECT `value` as `file_path` FROM `" . $prefix . "listings_media` WHERE `value` = '" . $file . "'
            UNION
            SELECT `logo_path` as `file_path` FROM `" . $prefix . "categories` WHERE `logo_path` = '" . $file . "'
            UNION
            SELECT `content` as `file_path` FROM `" . $prefix . "advertising` WHERE `content` = '" . $file . "' AND `type` = 'image'
            UNION
            SELECT `value` as `file_path` FROM `" . $prefix . "settings` WHERE `value` = '" . $file . "' AND `name` = 'site_logo_path'
            UNION
            SELECT `store_settings` as `file_path` FROM `" . $prefix . "users` WHERE `store_settings` LIKE '%" . $file . "%'");

        return ($statement->rowCount() > 0) ? true : false;
    }

    /**
     *
     * the method that will run all/specified cron jobs
     *
     * @param string|null $command
     *
     * @return $this
     */
    public function run($command = null)
    {
        $settings = $this->getSettings();
        $usersService = $this->getUsers();

        switch ($command) {
            case 'purge-unused-uploaded-files':
                $this->purgeUnusedUploadedFiles();
                break;
            case 'update-currency-exchange-rates':
                $this->updateCurrencyExchangeRates();
                break;
            default:
                $this->closeExpiredListings();
                $this->startScheduledListings();
                $this->purgeOldCacheFiles();
                $this->suspendUsersDebitExceededDate();

                if ($settings['pending_sales_listings_expire_hours']) {
                    $this->deletePendingSalesListings();
                }

                if ($settings['marked_deleted_listings_removal']) {
                    $this->removeMarkedDeletedListings();
                }

                if ($settings['closed_listings_deletion_days'] > 0) {
                    $this->markDeletedClosedListings();
                }

                $subscriptionTypes = $usersService->getSubscriptionTypes();
                foreach ($subscriptionTypes as $subscription) {
                    $this->notifyUsersOnSubscriptionsAboutToExpire($subscription);
                    $this->processExpiredSubscriptions($subscription);
                }

                $this->sendNewsletters();
                $this->deleteExpiredSalesForcePayment();

                $this->countListings();
                $this->purgeAutocompleteTags();
                $this->purgeUsersStatistics();
                $this->purgeRecentlyViewedListings();

                break;
        }


        return $this;
    }


}

