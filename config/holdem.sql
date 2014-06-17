CREATE DATABASE holdem;
USE holdem;

CREATE TABLE `session_store` (
  `id`                  bigint(20)    NOT NULL AUTO_INCREMENT,
  `utid`                varchar(127)  DEFAULT NULL,
  `hand`                varchar(255)  DEFAULT NULL,
  `winner`              varchar(127)  DEFAULT NULL,
  `active`              tinyint(1)    NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;


CREATE TABLE `current_game` (
  `id`                  bigint(20)    NOT NULL AUTO_INCREMENT,
  `utid`                varchar(127)  DEFAULT NULL,
  `players`             int(1)        NOT NULL DEFAULT 1,
  `serial`              varchar(255)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

Create User TexasHoldEmUser@'%' Identified By 'TexasHoldEmPass';
Grant Insert, Select, Update, Delete On holdem.* To TexasHoldEmUser@'%';

FLUSH PRIVILEGES;
