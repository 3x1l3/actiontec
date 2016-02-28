<?php
error_reporting(E_ALL);

//:: Define the database connection
mysql_connect("localhost","actiontec","actiontec");
mysql_select_db("actiontec");	

//::includes 
require_once('db.php');

//::Classes
$DB = new DB();

//::Define the string we are going to look in for passwords.
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_+-={}[]|\\;:<>,./?';

var_dump($DB->SelectWhere('passwords'));

die();

function getUrlContent($username, $password, $html = false){
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '192.168.1.254/login.cgi');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_COOKIESESSION,false);
curl_setopt($ch, CURLOPT_POST, 3);
curl_setopt($ch, CURLOPT_POSTFIELDS, "inputUserName=".$username."&inputPassword=".$password."&nothankyou=1");
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: en-US,en;q=0.5"));
$data = curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
//return $data;
//return ($httpcode>=200 && $httpcode<300) ? $data : false;
if ($html) {
	return nl2br(htmlspecialchars($data));
} else {
	return $data;
}
}

//echo '<script>Navigator.cookieEnabled = false;</script>';
$return = getUrlContent('admin','n4x');

$failed = strstr($return, 'index.html?msg=err');



