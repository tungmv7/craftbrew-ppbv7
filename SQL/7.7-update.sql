SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `{%TABLE_PREFIX%}advertising`
ADD `category_ids` TEXT
COLLATE 'utf8_general_ci' NOT NULL
AFTER `name`;

INSERT INTO `{%TABLE_PREFIX%}settings`
(`name`, `value`) VALUES
  ('address_display_format', 'default');

CREATE TABLE `{%TABLE_PREFIX%}blocked_users` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`         INT(11)               DEFAULT NULL,
  `type`            VARCHAR(255) NOT NULL,
  `value`           VARCHAR(255) NOT NULL,
  `blocked_actions` TEXT         NOT NULL,
  `block_reason`    TEXT         NOT NULL,
  `show_reason`     TINYINT(4)   NOT NULL,
  `created_at`      DATETIME     NOT NULL,
  `updated_at`      DATETIME              DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `{%TABLE_PREFIX%}blocked_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;