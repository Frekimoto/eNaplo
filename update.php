<?php
include_once("config.php");
mysql_query("ALTER TABLE  `".$_PREFIX."lessons` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT");
mysql_query("ALTER TABLE  `".$_PREFIX."lessons` ADD  `enabled` BOOLEAN NOT NULL DEFAULT TRUE");
mysql_query("ALTER TABLE  `".$_PREFIX."teaches` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `lesson`  `lesson` BIGINT( 20 ) NOT NULL ,
CHANGE  `tid`  `tid` BIGINT( 20 ) NOT NULL ,
CHANGE  `uid`  `uid` BIGINT( 20 ) NOT NULL");
mysql_query("ALTER TABLE  `".$_PREFIX."timetable` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `class`  `class` BIGINT( 20 ) NOT NULL ,
CHANGE  `tid`  `tid` BIGINT( 20 ) NOT NULL ,
CHANGE  `lesson`  `lesson` BIGINT( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `parent`  `parent` BIGINT( 20 ) NOT NULL DEFAULT  '0'");
mysql_query("ALTER TABLE  `".$_PREFIX."users` CHANGE  `class`  `class` BIGINT( 20 ) NOT NULL ,
CHANGE  `parent`  `parent` BIGINT( 20 ) NOT NULL");
mysql_query("ALTER TABLE  `".$_PREFIX."gardes` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `uid`  `uid` BIGINT( 20 ) NOT NULL ,
CHANGE  `tid`  `tid` BIGINT( 20 ) NOT NULL ,
CHANGE  `lesson`  `lesson` BIGINT( 20 ) NOT NULL");
mysql_query("ALTER TABLE  `".$_PREFIX."classes` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT");
mysql_query("ALTER TABLE  `".$_PREFIX."classes` ADD  `enabled` BOOLEAN NOT NULL DEFAULT TRUE AFTER  `name`");
mysql_query("ALTER TABLE  `".$_PREFIX."certificates` CHANGE  `id`  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `uid`  `uid` BIGINT( 20 ) NOT NULL ,
CHANGE  `tid`  `tid` BIGINT( 20 ) NOT NULL");
?>
Megtettem amit lehetett :)