<?php
error_reporting(E_ALL);

//:: Define the database connection
mysql_connect("localhost","actiontec","actiontec");
mysql_select_db("actiontec");	

//::includes 
require_once('db.php');
require_once("curl.php");

//::Classes
$DB = new DB();

//::Define the string we are going to look in for passwords.
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_+-={}[]|\\;:<>,./?';

$curl = new Curl();
$curl->url = "192.168.1.254/login.cgi";

$return = $curl->run(true);

var_dump($return);
var_dump(strstr($return, 'index.html?msg=err'));

die();



