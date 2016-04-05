SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD `receiver_id` INT(11) NULL
AFTER `user_id`,
ADD `topic_id` INT NULL
AFTER `id`,
CHANGE `status` `status` ENUM('pending', 'declined', 'accepted', 'withdrawn', 'counter')
COLLATE 'utf8_general_ci' NOT NULL DEFAULT 'pending'
AFTER `amount`;

ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD INDEX `topic_id` (`topic_id`);

ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD FOREIGN KEY (`topic_id`) REFERENCES `{%TABLE_PREFIX%}offers` (`id`)
  ON DELETE CASCADE
  ON UPDATE RESTRICT;

ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD INDEX `receiver_id` (`receiver_id`);

ALTER TABLE `{%TABLE_PREFIX%}offers`
ADD FOREIGN KEY (`receiver_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
  ON DELETE SET NULL
  ON UPDATE RESTRICT;

UPDATE `{%TABLE_PREFIX%}offers`
SET `receiver_id` = (SELECT `user_id`
                     FROM `{%TABLE_PREFIX%}listings`
                     WHERE `{%TABLE_PREFIX%}listings`.`id` = `{%TABLE_PREFIX%}offers`.`listing_id`)
WHERE `receiver_id` IS NULL;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('character_length', ''),
  ('enable_auctions_in_stores', ''),
  ('stores_force_list_in_both', '');
