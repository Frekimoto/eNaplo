<?php
include_once("config.php");
mysql_query("ALTER TABLE  `".$_PREFIX."users` ADD  `om_id` BIGINT( 11 ) NULL DEFAULT NULL AFTER  `password` ;");
mysql_query("ALTER TABLE  `".$_PREFIX."timetable` ADD `uid` BIGINT NOT NULL AFTER  `class` ;");
mysql_query("ALTER TABLE  `".$_PREFIX."timetable` ADD  `lesson` BIGINT NOT NULL AFTER  `type` ;");
?>
Megtettem amit lehetett :)