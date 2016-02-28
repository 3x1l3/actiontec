<?php
var_dump("FART");
echo 'test';
error_reporting(E_ALL);
echo 'test';
var_dump(mysql_connect("localhost","actiontec","actiontec"));
var_dump(mysql_select_db("actiontec"));	
var_dump(mysql_query("INSERT INTO passwords VALUES"));
mysql_close();
die();
