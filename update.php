<?php
include_once("config.php");
mysql_query("ALTER TABLE  `users` ADD  `om_id` BIGINT( 11 ) NULL DEFAULT NULL AFTER  `password` ;");
?>
Megtettem amit lehetett :)