SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('hpfeat_tabbed', '1'),
  ('hpfeat_carousel', '1'),
  ('hpfeat_box', 'grid'),
  ('recent_tabbed', '1'),
  ('recent_carousel', '1'),
  ('recent_box', 'grid'),
  ('ending_tabbed', '1'),
  ('ending_carousel', '1'),
  ('ending_box', 'grid'),
  ('popular_tabbed', '1'),
  ('popular_carousel', '1'),
  ('popular_box', 'grid');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('payment_methods_registration', '');

CREATE TABLE `{%TABLE_PREFIX%}recently_viewed_listings` (
  `id`         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `listing_id` INT(11)      NOT NULL,
  `user_id`    INT(11)      NULL,
  `user_token` VARCHAR(255) NOT NULL
  COMMENT 'user token (cookie)',
  `created_at` DATETIME     NOT NULL,
  `updated_at` DATETIME     NULL,
  FOREIGN KEY (`listing_id`) REFERENCES `{%TABLE_PREFIX%}listings` (`id`)
    ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
    ON DELETE CASCADE
)
  ENGINE ='InnoDB'
  COLLATE 'utf8_general_ci';

ALTER TABLE `{%TABLE_PREFIX%}recently_viewed_listings`
ADD INDEX `user_token` (`user_token`);

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_recently_viewed_listings', ''),
  ('enable_recently_viewed_listings_expiration', '');

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('enable_bulk_lister', '');

ALTER TABLE `{%TABLE_PREFIX%}listings_watch`
ADD `user_token` varchar(255) NOT NULL AFTER `listing_id`;

ALTER TABLE `{%TABLE_PREFIX%}listings_watch`
ADD INDEX `user_token` (`user_token`);

ALTER TABLE `{%TABLE_PREFIX%}listings_watch`
CHANGE `user_id` `user_id` int(11) NULL AFTER `id`;