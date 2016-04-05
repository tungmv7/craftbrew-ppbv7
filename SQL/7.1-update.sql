SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `{%TABLE_PREFIX%}users_statistics` (
  `id`                   INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`              INT(11) DEFAULT NULL,
  `remote_addr`          VARCHAR(50)  NOT NULL,
  `request_uri`          VARCHAR(255) NOT NULL,
  `page_title`           VARCHAR(255) NOT NULL,
  `http_user_agent`      VARCHAR(255) NOT NULL,
  `http_accept_language` VARCHAR(50)  NOT NULL,
  `http_referrer`        VARCHAR(255) NOT NULL,
  `created_at`           DATETIME     NOT NULL,
  `updated_at`           DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `{%TABLE_PREFIX%}users_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{%TABLE_PREFIX%}users` (`id`)
    ON DELETE SET NULL
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

ALTER TABLE `{%TABLE_PREFIX%}advertising`
ADD `language` VARCHAR(255) NULL
AFTER `nb_clicks`;

ALTER TABLE `{%TABLE_PREFIX%}advertising`
ADD `active` tinyint NOT NULL AFTER `language`;

ALTER TABLE `{%TABLE_PREFIX%}transactions`
CHANGE `name` `name` text COLLATE 'utf8_general_ci' NOT NULL AFTER `id`;