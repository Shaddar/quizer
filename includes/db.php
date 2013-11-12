<?php
$hostname = "localhost";
$username = "username";
$password = "password";
$dbName = "quizer";

mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");
mysql_select_db($dbName) or die (mysql_error());
mysql_query('SET character_set_database = utf8');
mysql_query('SET NAMES utf8');

$copyrights = "&#169; <i>All rights belong to <a href='http://shaddar.com/'>Shaddar</a>, 2013";
//$copyrights .= "<br>Content of this site is licensed under a Creative Commons Attribution Non-commercial No Derivatives version 3.0 Unported License (by-nc-nd)</i>";
//$copyrights .= "<br>«QUIZer»";
?>