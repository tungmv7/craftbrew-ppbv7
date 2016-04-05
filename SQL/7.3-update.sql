SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `{%TABLE_PREFIX%}categories`
ADD `html_header` TEXT NOT NULL;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_custom_start_time', '1'),
  ('enable_custom_end_time', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('search_subtitle', '1'),
  ('search_description', '0'),
  ('search_category_name', '0');

ALTER TABLE `{%TABLE_PREFIX%}fees`
ADD `type` VARCHAR(50) NOT NULL DEFAULT 'default'
AFTER `name`,
CHANGE `type` `calculation_type` ENUM('flat', 'percent') NOT NULL
AFTER `amount`;

UPDATE `{%TABLE_PREFIX%}fees` SET `type` = 'default' WHERE `type` = '';

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('recaptcha_email_friend', '0');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_reputation', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('bcc_emails', '0');