CREATE TABLE `session_store` (
  `id`                  bigint(20) NOT NULL AUTO_INCREMENT,
  `utid`                varchar(127) DEFAULT NULL,
  `hand`                varchar(255) DEFAULT NULL,
  `winner`              varchar(127) DEFAULT NULL,
  `active`              tinyint(1) NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

Create User TexasHoldEmUser@'%' Identified By 'TexasHoldEmPass';
Grant Insert, Select, Update, Delete On holdem To TexasHoldEmUser@'%';

