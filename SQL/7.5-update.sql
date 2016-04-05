SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('crop_images', '');

ALTER TABLE `{%TABLE_PREFIX%}custom_fields`
ADD `product_attribute` TINYINT(4) NOT NULL;

ALTER TABLE `{%TABLE_PREFIX%}sales_listings`
ADD `product_attributes` TEXT
COLLATE 'utf8_general_ci' NULL
AFTER `quantity`;

ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD `product_attributes` TEXT
COLLATE 'utf8_general_ci' NULL
AFTER `amount`;

ALTER TABLE `{%TABLE_PREFIX%}listings`
ADD `stock_levels` TEXT
COLLATE 'utf8_general_ci' NULL
AFTER `quantity`;

ALTER TABLE `{%TABLE_PREFIX%}users`
ADD `sale_invoices_content` TEXT
COLLATE 'utf8_general_ci' NOT NULL
AFTER `bank_details`;