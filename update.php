<?php
include_once("config.php");
mysql_query("ALTER TABLE  `".$_SYSTEM_USERS_TABLE."` ADD  `om_id` BIGINT( 11 ) NULL DEFAULT NULL AFTER  `password` ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` ADD `uid` BIGINT NOT NULL AFTER  `class` ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` ADD  `lesson` BIGINT NOT NULL AFTER  `type` ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TEACHES_TABLE."` ADD  `class` BIGINT NOT NULL AFTER  `uid` ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_DELAY_TABLE."` CHANGE  `type`  `typ` ENUM(  '1',  '2',  '3',  '4' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_DELAY_TABLE."` CHANGE  `value`  `delay` INT( 11 ) NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_DELAY_TABLE."` CHANGE  `from`  `fromc` INT( 11 ) NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_DELAY_TABLE."` CHANGE  `to`  `toc` INT( 11 ) NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_GARDES_TABLE."` CHANGE  `value`  `garde` ENUM(  '-',  '1',  '2',  '3',  '4',  '5' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_GARDES_TABLE."` CHANGE  `type`  `typ` ENUM(  '1',  '2',  '3',  '4',  '5',  '6',  '7',  '8',  '9' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_GARDES_TABLE."` CHANGE  `date`  `added` DATE NOT NULL ;");
mysql_query("ALTER TABLE `".$_SYSTEM_USERS_TABLE."` CHANGE `password` `pass` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
mysql_query("ALTER TABLE  `".$_SYSTEM_USERS_TABLE."` CHANGE  `status`  `rank` INT( 1 ) NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE  `".$_SYSTEM_USERS_TABLE."` CHANGE  `date`  `added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` CHANGE  `type`  `typ` INT( 11 ) NOT NULL ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` CHANGE  `from`  `fromd` DATE NOT NULL ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` CHANGE  `to`  `tod` DATE NOT NULL ;");
mysql_query("ALTER TABLE  `".$_SYSTEM_TIMETABLE_TABLE."` CHANGE  `day`  `dayn` ENUM(  '1',  '2',  '3',  '4',  '5',  '6',  '7' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1';");
mysql_query("ALTER TABLE ".$_SYSTEM_TIMETABLE_TABLE." DROP INDEX day;");
?>
Megtettem amit lehetett :)