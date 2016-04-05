## @version 7.4
## DISABLE FOREIGN KEY CHECKS
SET foreign_key_checks = 0;

SET @default_currency = (SELECT
                           `currency`
                         FROM `{%OLD_PREFIX%}gen_setts`
                         LIMIT 1);

## USERS - [ OK ]
# ================
# COMMENTS
# ================
# last_login	datetime - CANNOT TRANSFER AS THERE IS NOTHING SPECIFIC TO TRANSFER
# global_settings	text - WILL NOT TRANSFER
# postage_settings = USE DEFAULT DATA [ OK ]

# UPDATE THE IDS OF CURRENT USERS TO BE > THE GREATEST ID FROM THE OLD USERS TABLE
DELETE FROM `{%NEW_PREFIX%}users`
WHERE role != 'Admin';

UPDATE `{%NEW_PREFIX%}users`
SET `{%NEW_PREFIX%}users`.`id` = (
  SELECT
    max(`{%OLD_PREFIX%}users`.`user_id`) + 100
  FROM `{%OLD_PREFIX%}users`
);

INSERT INTO `{%NEW_PREFIX%}users`
(`id`, `email`, `username`, `password`, `created_at`, `salt`, `birthdate`, `newsletter_subscription`,
 `active`, `approved`, `mail_activated`, `payment_status`, `listing_approval`,
 `user_verified`, `user_verified_last_payment`, `user_verified_next_payment`, `business_account`,
 `company_name`, `bank_details`, `account_mode`, `items_sold`, `items_bought`,
 `balance`, `max_debit`, `preferred_seller`, `preferred_seller_expiration`, `is_seller`,
 `store_active`, `store_subscription_id`, `store_name`, `store_last_payment`, `store_next_payment`, `store_settings`)
  SELECT
    `user_id`,
    `email`,
    `username`,
    `password`,
    FROM_UNIXTIME(`reg_date`),
    `salt`,
    IF(`birthdate` = '', MAKEDATE(`birthdate_year`, 1), `birthdate`),
    `newsletter`,
    `active`,
    `approved`,
    `mail_activated`,
    IF(`payment_status` = 'confirmed', 'confirmed', 'failed'),
    `auction_approval`,
    `seller_verified`,
    IF(`seller_verif_last_payment` > 0, FROM_UNIXTIME(`seller_verif_last_payment`), NULL),
    IF(`seller_verif_next_payment` > 0, FROM_UNIXTIME(`seller_verif_next_payment`), NULL),
    `tax_account_type`,
    `tax_company_name`,
    `default_bank_details`,
    IF(`payment_mode` = '1', 'live', 'account'),
    `items_sold`,
    `items_bought`,
    `balance`,
    `max_credit`,
    `preferred_seller`,
    IF(`preferred_seller_exp_date` > 0, FROM_UNIXTIME(`preferred_seller_exp_date`), NULL),
    `is_seller`,
    `shop_active`,
    IF(`shop_account_id` > 0, `shop_account_id`, NULL),
    `shop_name`,
    IF(`shop_last_payment` > 0, FROM_UNIXTIME(`shop_last_payment`), NULL),
    IF(`shop_next_payment` > 0, FROM_UNIXTIME(`shop_next_payment`), NULL),
    IF(`shop_name` != '', CONCAT(
        'a:6:{',
        's:17:"store_description";s:', LENGTH(`shop_mainpage`), ':"', `shop_mainpage`, '";',
        's:22:"store_meta_description";s:', LENGTH(`shop_metatags`), ':"', `shop_metatags`, '";',
        's:15:"store_logo_path";s:', LENGTH(`shop_logo_path`), ':"', `shop_logo_path`, '";',
        's:11:"store_about";s:', LENGTH(`shop_about`), ':"', `shop_about`, '";',
        's:26:"store_shipping_information";s:', LENGTH(`shop_shipping_info`), ':"', `shop_shipping_info`, '";',
        's:22:"store_company_policies";s:', LENGTH(`shop_company_policies`), ':"', `shop_company_policies`, '";',
        '}'),
       '')
  FROM `{%OLD_PREFIX%}users`;

## USERS ADDRESS BOOK - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}users_address_book`;
INSERT INTO `{%NEW_PREFIX%}users_address_book`
(`user_id`, `address`, `is_primary`)
  SELECT
    `user_id`,
    CONCAT(
        'a:7:{',
        's:4:"name";',
        'a:2:{',
        's:5:"first";s:', LENGTH(`first_name`), ':"', `first_name`, '";',
        's:4:"last";s:', LENGTH(`last_name`), ':"', `last_name`, '";',
        '}',
        's:7:"address";s:', LENGTH(`address`), ':"', CONVERT(`address` USING utf8), '";',
        's:4:"city";s:', LENGTH(`city`), ':"', `city`, '";',
        's:8:"zip_code";s:', LENGTH(`zip_code`), ':"', `zip_code`, '";',
        's:7:"country";s:', LENGTH(`country`), ':"', `country`, '";',
        's:5:"state";s:', LENGTH(`state`), ':"', `state`, '";',
        's:5:"phone";s:', LENGTH(`phone`), ':"', `phone`, '";',
        '}'),
    '1'
  FROM `{%OLD_PREFIX%}users`;

# SET DEFAULT POSTAGE SETTINGS FOR ALL USERS
UPDATE `{%NEW_PREFIX%}users`
SET
  `postage_settings` = 'a:3:{s:18:"shipping_locations";s:8:"domestic";s:12:"postage_type";s:4:"item";s:12:"free_postage";i:0;}';

## PAYMENT GATEWAYS TABLE AND ADMIN / USERS SETTINGS
UPDATE `{%NEW_PREFIX%}payment_gateways`
SET `id` = `id` + 10000;

SET @paypalId = (SELECT
                   `pg_id`
                 FROM `{%OLD_PREFIX%}payment_gateways`
                 WHERE `name` = 'PayPal'
                 LIMIT 1);
SET @worldpayId = (SELECT
                     `pg_id`
                   FROM `{%OLD_PREFIX%}payment_gateways`
                   WHERE `name` = 'Worldpay'
                   LIMIT 1);
SET @twoCheckoutId = (SELECT
                        `pg_id`
                      FROM `{%OLD_PREFIX%}payment_gateways`
                      WHERE `name` = '2Checkout'
                      LIMIT 1);
SET @protxId = (SELECT
                  `pg_id`
                FROM `{%OLD_PREFIX%}payment_gateways`
                WHERE `name` = 'Protx'
                LIMIT 1);
SET @nochexId = (SELECT
                   `pg_id`
                 FROM `{%OLD_PREFIX%}payment_gateways`
                 WHERE `name` = 'Nochex'
                 LIMIT 1);
SET @authNetId = (SELECT
                    `pg_id`
                  FROM `{%OLD_PREFIX%}payment_gateways`
                  WHERE `name` = 'Authorize.net'
                  LIMIT 1);
SET @moneybookersId = (SELECT
                         `pg_id`
                       FROM `{%OLD_PREFIX%}payment_gateways`
                       WHERE `name` = 'Moneybookers'
                       LIMIT 1);
SET @paymateId = (SELECT
                    `pg_id`
                  FROM `{%OLD_PREFIX%}payment_gateways`
                  WHERE `name` = 'Paymate'
                  LIMIT 1);
SET @amazonId = (SELECT
                   `pg_id`
                 FROM `{%OLD_PREFIX%}payment_gateways`
                 WHERE `name` = 'Amazon'
                 LIMIT 1);

# site admin payment gateways details - TO BE INSERTED MANUALLY
# users payment gateways details - setting by setting
TRUNCATE TABLE `{%NEW_PREFIX%}payment_gateways_settings`;
# paypal email address
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'business',
    `pg_paypal_email`,
    @paypalId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_paypal_email` != '';
# worldpay id
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'instId',
    `pg_worldpay_id`,
    @worldpayId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_worldpay_id` != '';
# 2checkout id
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'sid',
    `pg_checkout_id`,
    @twoCheckoutId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_checkout_id` != '';
# sagepay (protx) username - Vendor
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'Vendor',
    `pg_protx_username`,
    @protxId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_protx_username` != '';
# sagepay (protx) password - Password
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'Password',
    `pg_protx_password`,
    @protxId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_protx_password` != '';
# nochex merchant id - merchant_id
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'merchant_id',
    `pg_nochex_email`,
    @nochexId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_nochex_email` != '';
# authorize.net id - x_login
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'x_login',
    `pg_authnet_username`,
    @authNetId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_authnet_username` != '';
# authorize.net key - authnet_transaction_key
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'authnet_transaction_key',
    `pg_authnet_password`,
    @authNetId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_authnet_password` != '';
# skrill (moneybookers) email - pay_to_email
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'pay_to_email',
    `pg_mb_email`,
    @moneybookersId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_mb_email` != '';
# paymate id - mid
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'mid',
    `pg_paymate_merchant_id`,
    @paymateId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_paymate_merchant_id` != '';
# amazon access key - aws_access_key_id
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'aws_access_key_id',
    `pg_amazon_access_key`,
    @amazonId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_amazon_access_key` != '';
# amazon secret key - aws_secret_key_id
INSERT INTO `{%NEW_PREFIX%}payment_gateways_settings`
(`name`, `value`, `gateway_id`, `user_id`)
  SELECT
    'aws_secret_key_id',
    `pg_amazon_secret_key`,
    @amazonId,
    `user_id`
  FROM `{%OLD_PREFIX%}users`
  WHERE `pg_amazon_secret_key` != '';


## CATEGORIES TABLE - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}categories`;
INSERT INTO `{%NEW_PREFIX%}categories`
(`id`, `name`, `parent_id`, `order_id`, `user_id`, `custom_fees`, `enable_auctions`, `enable_wanted`, `meta_description`)
  SELECT
    `category_id`,
    `name`,
    IF(`parent_id` > 0, `parent_id`, NULL),
    `order_id`,
    IF(`user_id` > 0, `user_id`, NULL),
    `custom_fees`,
    IF(`parent_id` > 0, '1', `enable_auctions`),
    IF(`parent_id` > 0, '1', `enable_wanted`),
    `meta_description`
  FROM `{%OLD_PREFIX%}categories`
  WHERE `{%OLD_PREFIX%}categories`.`user_id` = '0';

## LOCATIONS/COUNTRIES TABLE - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}locations`;
INSERT INTO `{%NEW_PREFIX%}locations`
(`id`, `name`, `iso_code`, `parent_id`, `order_id`)
  SELECT
    `id`,
    `name`,
    `country_iso_code`,
    IF(`parent_id` > 0, `parent_id`, NULL),
    `country_order`
  FROM `{%OLD_PREFIX%}countries`;

## CURRENCIES TABLE - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}currencies`;
INSERT INTO `{%NEW_PREFIX%}currencies`
(`id`, `iso_code`, `symbol`, `description`, `conversion_rate`)
  SELECT
    `id`,
    `symbol`,
    `currency_symbol`,
    `caption`,
    `convert_rate`
  FROM `{%OLD_PREFIX%}currencies`;

## AUCTIONS
TRUNCATE TABLE `{%NEW_PREFIX%}listings`;
INSERT INTO `{%NEW_PREFIX%}listings`
(`id`, `listing_type`, `name`, `description`, `user_id`, `list_in`, `category_id`, `addl_category_id`, `currency`,
 `quantity`, `start_price`, `reserve_price`, `buyout_price`, `enable_make_offer`, `make_offer_min`, `make_offer_max`,
 `apply_tax`, `bid_increment`, `start_time`, `end_time`, `duration`, `hpfeat`, `catfeat`, `bold`, `highlighted`,
 `private_auction`, `disable_sniping`, `nb_relists`, `auto_relist_sold`, `is_relisted`, `country`, `state`, `address`,
 `active`, `approved`, `closed`, `deleted`, `draft`, `nb_clicks`, `postage_settings`, `direct_payment`, `offline_payment`, `created_at`)
  SELECT
    `auction_id`,
    IF(`start_price` = `buyout_price`, 'product', 'auction'),
    `name`,
    `description`,
    `owner_id`,
    IF(`list_in` = 'auction', 'site', `list_in`),
    `category_id`,
    IF(`addl_category_id` > 0, `addl_category_id`, NULL),
    `currency`,
    IF(`start_price` = `buyout_price`, `quantity`, '1'),
    `start_price`,
    `reserve_price`,
    `buyout_price`,
    `is_offer`,
    `offer_min`,
    `offer_max`,
    `apply_tax`,
    `bid_increment_amount`,
    FROM_UNIXTIME(`start_time`),
    FROM_UNIXTIME(`end_time`),
    `duration`,
    `hpfeat`,
    `catfeat`,
    `bold`,
    `hl`,
    `hidden_bidding`,
    `disable_sniping`,
    `auto_relist_nb`,
    `auto_relist_bids`,
    `is_relisted_item`,
    `country`,
    `state`,
    `zip_code`,
    `active`,
    `approved`,
    `closed`,
    `deleted`,
    `is_draft`,
    `nb_clicks`,
    CONCAT('a:5:{',
           's:14:"pickup_options";s:10:"no_pickups";',
           's:7:"postage";a:2:{',
           's:5:"price";a:1:{i:0;s:', LENGTH(`postage_amount`), ':"', `postage_amount`, '";}',
           's:6:"method";a:1:{i:0;s:', IF(`type_service` != '', LENGTH(`type_service`), '8'), ':"',
           IF(`type_service` != '', `type_service`, 'Standard'), '";}}',
           's:11:"item_weight";s:', LENGTH(`item_weight`), ':"', `item_weight`, '";',
           's:9:"insurance";s:', LENGTH(`insurance_amount`), ':"', `insurance_amount`, '";',
           's:16:"shipping_details";s:', LENGTH(`shipping_details`), ':"', `shipping_details`, '";}'
    ),
    IF(`direct_payment` = '0', '', `direct_payment`),
    IF(`payment_methods` = '0', '', `payment_methods`),
    FROM_UNIXTIME(`creation_date`)
  FROM `{%OLD_PREFIX%}auctions`
  WHERE `creation_in_progress` = '0';

## AUCTIONS MEDIA - UPLOAD IMAGES, VIDEOS AND DOWNLOADS SEPARATELY - [ SHOULD BE OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}listings_media`;
# IMAGES
INSERT INTO `{%NEW_PREFIX%}listings_media`
(`id`, `value`, `type`, `listing_id`, `created_at`)
  SELECT
    `media_id`,
    `media_url`,
    'image',
    `auction_id`,
    now()
  FROM `{%OLD_PREFIX%}auction_media`
  WHERE `media_type` = '1' AND `upload_in_progress` = '0';
# VIDEOS / DOWNLOADS
INSERT INTO `{%NEW_PREFIX%}listings_media`
(`id`, `value`, `type`, `listing_id`, `created_at`)
  SELECT
    `media_id`,
    `media_url`,
    IF(`media_type` = 2, 'video', 'download'),
    `auction_id`,
    now()
  FROM `{%OLD_PREFIX%}auction_media`
  WHERE `media_type` IN ('2', '3') AND `upload_in_progress` = '0' AND `embedded_code` = '';
# EMBEDDED VIDEOS
INSERT INTO `{%NEW_PREFIX%}listings_media`
(`id`, `value`, `type`, `listing_id`, `created_at`)
  SELECT
    `media_id`,
    `embedded_code`,
    'video',
    `auction_id`,
    now()
  FROM `{%OLD_PREFIX%}auction_media`
  WHERE `media_type` = '2' AND `upload_in_progress` = '0' AND `embedded_code` != '';

## AUCTION OFFERS - [ OK - W/O CREATED_AT ]
TRUNCATE TABLE `{%NEW_PREFIX%}offers`;
INSERT INTO `{%NEW_PREFIX%}offers`
(`id`, `listing_id`, `user_id`, `quantity`, `amount`, `status`)
  SELECT
    `offer_id`,
    `auction_id`,
    `buyer_id`,
    `quantity`,
    `amount`,
    IF(`accepted` = 1, 'accepted', 'declined')
  FROM `{%OLD_PREFIX%}auction_offers`;

## AUCTION WATCH - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}listings_watch`;
INSERT INTO `{%NEW_PREFIX%}listings_watch`
(`id`, `user_id`, `listing_id`, `created_at`)
  SELECT
    `id`,
    `user_id`,
    `auction_id`,
    now()
  FROM `{%OLD_PREFIX%}auction_watch`;

## BIDS TABLE - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}bids`;
INSERT INTO `{%NEW_PREFIX%}bids`
(`id`, `listing_id`, `user_id`, `amount`, `maximum_bid`, `outbid`, `created_at`)
  SELECT
    `bid_id`,
    `auction_id`,
    `bidder_id`,
    `bid_amount`,
    `bid_proxy`,
    IF(`bid_invalid` = 1, 1, `bid_out`),
    FROM_UNIXTIME(`bid_date`)
  FROM `{%OLD_PREFIX%}bids`;

## CUSTOM FIELDS BOXES [ OK ] - multiOptions serialized data is converted through code
TRUNCATE TABLE `{%NEW_PREFIX%}custom_fields`;
INSERT INTO `{%NEW_PREFIX%}custom_fields`
(`id`, `type`, `element`, `active`, `order_id`, `category_ids`, `label`, `attributes`, `description`, `multiOptions`, `required`, `searchable`)
  SELECT
    `b`.`box_id`,
    IF(`f`.`page_handle` = 'auction', 'item', 'user'),
    IF(`t`.`box_type` = 'list', 'select', `t`.`box_type`),
    `f`.`active`,
    `f`.`field_order`,
    IF(`f`.`category_id` > 0, CONCAT('a:1:{i:0;s:', LENGTH(`f`.`category_id`), ':"', `f`.`category_id`, '";}'), ''),
    CONCAT(`f`.`field_name`, ' ', `b`.`box_name`),
    'a:2:{s:3:"key";a:2:{i:0;s:5:"class";i:1;s:0:"";}s:5:"value";a:2:{i:0;s:25:"form-control input-medium";i:1;s:0:"";}}',
    `f`.`field_description`,
    `b`.`box_value`,
    `b`.`mandatory`,
    `b`.`box_searchable`
  FROM `{%OLD_PREFIX%}custom_fields_boxes` AS `b`
    LEFT JOIN `{%OLD_PREFIX%}custom_fields_types` AS `t` ON `t`.`type_id` = `b`.`box_type`
    LEFT JOIN `{%OLD_PREFIX%}custom_fields` AS `f` ON `f`.`field_id` = `b`.`field_id`
  WHERE `f`.`page_handle` IN ('auction', 'register');

## CUSTOM FIELDS DATA [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}custom_fields_data`;
INSERT INTO `{%NEW_PREFIX%}custom_fields_data`
(`field_id`, `owner_id`, `type`, `value`)
  SELECT
    `box_id`,
    `owner_id`,
    IF(`page_handle` = 'auction', 'item', 'user'),
    `box_value`
  FROM `{%OLD_PREFIX%}custom_fields_data`
  WHERE `page_handle` IN ('auction', 'register');

## FEES
TRUNCATE TABLE `{%NEW_PREFIX%}fees`;
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('signup', (SELECT
                `signup_fee`
              FROM `{%OLD_PREFIX%}fees`
              WHERE `category_id` = '0'
              LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('images', (SELECT
                `picture_fee`
              FROM `{%OLD_PREFIX%}fees`
              WHERE `category_id` = '0'
              LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('free_images', (SELECT
                     `free_images`
                   FROM `{%OLD_PREFIX%}fees`
                   WHERE `category_id` = '0'
                   LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('highlighted', (SELECT
                     `hlitem_fee`
                   FROM `{%OLD_PREFIX%}fees`
                   WHERE `category_id` = '0'
                   LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('hpfeat', (SELECT
                `hpfeat_fee`
              FROM `{%OLD_PREFIX%}fees`
              WHERE `category_id` = '0'
              LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('catfeat', (SELECT
                 `catfeat_fee`
               FROM `{%OLD_PREFIX%}fees`
               WHERE `category_id` = '0'
               LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('media', (SELECT
               `video_fee`
             FROM `{%OLD_PREFIX%}fees`
             WHERE `category_id` = '0'
             LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('free_media', (SELECT
                    `free_media`
                  FROM `{%OLD_PREFIX%}fees`
                  WHERE `category_id` = '0'
                  LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('addl_category', (SELECT
                       `second_cat_fee`
                     FROM `{%OLD_PREFIX%}fees`
                     WHERE `category_id` = '0'
                     LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('reserve_price', (SELECT
                       `rp_fee`
                     FROM `{%OLD_PREFIX%}fees`
                     WHERE `category_id` = '0'
                     LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('buyout', (SELECT
                `buyout_fee`
              FROM `{%OLD_PREFIX%}fees`
              WHERE `category_id` = '0'
              LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('make_offer_fee', (SELECT
                        `makeoffer_fee`
                      FROM `{%OLD_PREFIX%}fees`
                      WHERE `category_id` = '0'
                      LIMIT 1));
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`) VALUES
  ('digital_downloads_fee', (SELECT
                               `dd_fee`
                             FROM `{%OLD_PREFIX%}fees`
                             WHERE `category_id` = '0'
                             LIMIT 1));

## FEES TIERS
# FEES TIERS > FEES
INSERT INTO `{%NEW_PREFIX%}fees`
(`name`, `amount`, `type`, `category_id`, `tier_from`, `tier_to`)
  SELECT
    IF(`fee_type` = 'setup', 'setup', 'sale'),
    `fee_amount`,
    `calc_type`,
    IF(`category_id` > 0, `category_id`, NULL),
    `fee_from`,
    `fee_to`
  FROM `{%OLD_PREFIX%}fees_tiers`
  WHERE `fee_type` IN ('setup', 'endauction');

# FEES TIERS > STORE SUBSCRIPTIONS
TRUNCATE TABLE `{%NEW_PREFIX%}stores_subscriptions`;
INSERT INTO `{%NEW_PREFIX%}stores_subscriptions`
(`id`, `name`, `price`, `listings`, `recurring_days`, `featured_store`)
  SELECT
    `tier_id`,
    `store_name`,
    `fee_amount`,
    `store_nb_items`,
    `store_recurring`,
    `store_featured`
  FROM `{%OLD_PREFIX%}fees_tiers`
  WHERE `fee_type` = 'store';

## GEN SETTS - [ OK > admin will need to review settings before completing the installation ]
UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `sitename`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'sitename';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `currency`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'currency';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `amount_format`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'currency_format';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `amount_digits`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'currency_decimals';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `currency_position`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'currency_position';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `max_images`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'images_max';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `images_max_size`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'images_size';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = IF((SELECT
                    `account_mode`
                  FROM `{%OLD_PREFIX%}gen_setts`
                  LIMIT 1) = 1, 'live', 'account')
WHERE `name` = 'payment_mode';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `max_credit`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'maximum_debit';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `init_credit`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'signup_credit';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_private_site`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'private_site';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_pref_sellers`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'preferred_sellers';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `pref_sellers_reduction`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'preferred_sellers_reduction';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `preferred_days`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'preferred_sellers_expiration';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `invoice_header`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'invoice_header';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `invoice_footer`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'invoice_footer';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_stores`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'enable_stores';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = IF((SELECT
                    `account_mode_personal`
                  FROM `{%OLD_PREFIX%}gen_setts`
                  LIMIT 1) = 1, 'personal', 'global')
WHERE `name` = 'user_account_type';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_auctions_approval`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'enable_listings_approval';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `signup_settings`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'signup_settings';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `makeoffer_process`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'enable_make_offer';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `makeoffer_private`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'show_make_offer_ranges';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_seller_verification`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'user_verification';


UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `seller_verification_mandatory`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'seller_verification_mandatory';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `bidder_verification_mandatory`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'buyer_verification_mandatory';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_store_only_mode`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'store_only_mode';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `enable_auto_relist`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'auto_relist';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `ga_code`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'google_analytics_code';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `email_admin_title`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'email_admin_title';

UPDATE `{%NEW_PREFIX%}settings`
SET `value` = (SELECT
                 `hide_empty_stores`
               FROM `{%OLD_PREFIX%}gen_setts`
               LIMIT 1)
WHERE `name` = 'hide_empty_stores';

## WINNERS
# sale_data	 - a:1:{s:8:"currency";s:3:"GBP";} < we need the currency for the sale!!!
# WINNERS >> SALES
# 1.- where invoice id = 0
TRUNCATE TABLE `{%NEW_PREFIX%}sales`;
INSERT IGNORE INTO `{%NEW_PREFIX%}sales`
(`id`, `buyer_id`, `seller_id`, `flag_payment`, `flag_shipping`, `sale_data`, `postage_amount`, `insurance_amount`,
 `tax_rate`, `active`, `email_sent`, `seller_deleted`, `buyer_deleted`, `edit_locked`, `messaging_topic_id`, `created_at`)
  SELECT
    IF(`invoice_id` > 0, `invoice_id`, `winner_id`),
    `buyer_id`,
    `seller_id`,
    IF(`direct_payment_paid` = '1', '2', `flag_paid`),
    `flag_status`,
    CONCAT(
        'a:1:{s:8:"currency";s:3:"',
        (SELECT
           `a`.`currency`
         FROM `{%OLD_PREFIX%}auctions` AS `a`
         WHERE `a`.`auction_id` = `auction_id`
         LIMIT 1), '";}'),
    `postage_amount`,
    `insurance_amount`,
    `tax_rate`,
    `active`,
    '1',
    `s_deleted`,
    `b_deleted`,
    '1',
    `messaging_topic_id`,
    FROM_UNIXTIME(`purchase_date`)
  FROM `{%OLD_PREFIX%}winners`
  WHERE `invoice_id` = 0;
#2. where invoice_id > 0, group by invoice id
INSERT IGNORE INTO `{%NEW_PREFIX%}sales`
(`id`, `buyer_id`, `seller_id`, `flag_payment`, `flag_shipping`, `sale_data`, `postage_amount`, `insurance_amount`,
 `tax_rate`, `active`, `email_sent`, `seller_deleted`, `buyer_deleted`, `edit_locked`, `messaging_topic_id`, `created_at`)
  SELECT
    IF(`invoice_id` > 0, `invoice_id`, `winner_id`),
    `buyer_id`,
    `seller_id`,
    IF(`direct_payment_paid` = '1', '2', `flag_paid`),
    `flag_status`,
    CONCAT(
        'a:1:{s:8:"currency";s:3:"',
        (SELECT
           `a`.`currency`
         FROM `{%OLD_PREFIX%}auctions` AS `a`
         WHERE `a`.`auction_id` = `auction_id`
         LIMIT 1), '";}'),
    `postage_amount`,
    `insurance_amount`,
    `tax_rate`,
    `active`,
    '1',
    `s_deleted`,
    `b_deleted`,
    '1',
    `messaging_topic_id`,
    FROM_UNIXTIME(`purchase_date`)
  FROM `{%OLD_PREFIX%}winners`
  WHERE `invoice_id` > 0
  GROUP BY `invoice_id`;


# WINNERS >> SALES_LISTINGS
TRUNCATE TABLE `{%NEW_PREFIX%}sales_listings`;
INSERT INTO `{%NEW_PREFIX%}sales_listings`
(`id`, `listing_id`, `sale_id`, `price`, `quantity`, `downloads_active`, `created_at`)
  SELECT
    `winner_id`,
    `auction_id`,
    IF(`invoice_id` > 0, `invoice_id`, `winner_id`),
    `bid_amount`,
    `quantity_offered`,
    `dd_active`,
    FROM_UNIXTIME(`purchase_date`)
  FROM `{%OLD_PREFIX%}winners`;


## ACCOUNTING TABLE - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}accounting`;
# INVOICES >> ACCOUNTING
INSERT INTO `{%NEW_PREFIX%}accounting`
(`name`, `amount`, `tax_rate`, `currency`, `user_id`, `listing_id`, `created_at`)
  SELECT
    `name`,
    `amount`,
    `tax_rate`,
    @default_currency,
    `user_id`,
    IF(`item_id` > 0, `item_id`, NULL),
    FROM_UNIXTIME(`invoice_date`)
  FROM `{%OLD_PREFIX%}invoices`
  WHERE `user_id` > 0 AND `live_fee` = 0;
# INVOICES >> TRANSACTIONS
TRUNCATE TABLE `{%NEW_PREFIX%}transactions`;
INSERT INTO `{%NEW_PREFIX%}transactions`
(`name`, `amount`, `tax_rate`, `currency`, `user_id`, `paid`, `gateway_transaction_code`, `created_at`)
  SELECT
    `name`,
    `amount`,
    `tax_rate`,
    @default_currency,
    `user_id`,
    '1',
    CONCAT('[OLD]', `processor`),
    FROM_UNIXTIME(`invoice_date`)
  FROM `{%OLD_PREFIX%}invoices`
  WHERE `live_fee` = 1;

## MESSAGING - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}messaging`;
INSERT INTO `{%NEW_PREFIX%}messaging`
(`id`, `topic_id`, `title`, `content`, `sender_id`, `receiver_id`, `listing_id`, `sale_id`, `flag_read`,
 `sender_deleted`, `receiver_deleted`, `created_at`)
  SELECT
    `message_id`,
    `topic_id`,
    `message_title`,
    `message_content`,
    `sender_id`,
    `receiver_id`,
    IF((`message_handle` = 1 AND `auction_id` > 0), `auction_id`, NULL),
    IF(`winner_id` > 0, `winner_id`, NULL),
    `is_read`,
    `sender_deleted`,
    `receiver_deleted`,
    FROM_UNIXTIME(`reg_date`)
  FROM `{%OLD_PREFIX%}messaging`;

## PAYMENT OPTIONS - [ OK ]
TRUNCATE TABLE `{%NEW_PREFIX%}offline_payment_methods`;
INSERT INTO `{%NEW_PREFIX%}offline_payment_methods`
(`id`, `name`, `logo`)
  SELECT
    `id`,
    `name`,
    `logo_url`
  FROM `{%OLD_PREFIX%}payment_options`;

## REPUTATION - winner_id = sale_listing_id - [ OK - reputation_type ~ ]
TRUNCATE TABLE `{%NEW_PREFIX%}reputation`;
INSERT INTO `{%NEW_PREFIX%}reputation`
(`id`, `user_id`, `poster_id`, `sale_listing_id`, `score`, `comments`, `reputation_type`,
 `posted`, `created_at`)
  SELECT
    `reputation_id`,
    `user_id`,
    `from_id`,
    `winner_id`,
    `reputation_rate`,
    `reputation_content`,
    `reputation_type`,
    `submitted`,
    FROM_UNIXTIME(`reg_date`)
  FROM `{%OLD_PREFIX%}reputation`;

## ENABLE FOREIGN KEY CHECKS
SET foreign_key_checks = 1;
