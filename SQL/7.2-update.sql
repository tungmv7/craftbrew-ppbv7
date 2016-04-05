SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('preferred_sellers_apply_sale', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('mod_rewrite_urls', '%MOD_REWRITE_URLS%');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('custom_stores_categories', '1');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_recaptcha', ''),
  ('recaptcha_public_key', ''),
  ('recaptcha_private_key', ''),
  ('recaptcha_registration', ''),
  ('recaptcha_contact_us', '');