<?php
error_reporting(E_ALL);

//:: Define the database connection
mysql_connect("localhost", "actiontec", "actiontec");
mysql_select_db("actiontec");

//::includes
require_once ('db.php');
require_once ("curl.php");
require_once ("sequence.php");

//::Classes
$DB = new DB();

//::Define the string we are going to look in for passwords.
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_+-={}[]|\\;:<>,./?';

$curl = new Curl();
$curl->url = "192.168.1.254/login.cgi";
$curl->username = "root";

$sequence = new Sequence();

$sequence->run();

$curl->password = "";

$return = $curl->run();

//::If there is not an error.
if (!$curl->stringExists('index.html?msg=err')) {

} else {//If there is an error.

}

die();
