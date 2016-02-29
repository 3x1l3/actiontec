<?php

require_once ('../sequence.php');

/**
 * Test 1:
 * Start with no string.
 * Prerequisits: null string
 * Postrequesits: a
 */
/*
 $test1 = new Sequence();
 $test1->run();
 assert($test1->getStr() == 'a');
 */

/**
 * Test1.1
 * Prerequesits: 'a'
 * PostRequests: 'b'
 */
/*
 $test1->run();
 assert($test1->getStr() == 'b');
 */

/**
 * Test 2
 * Prereq: '?'
 * PostReq: 'aa'
 */
/*
 $test2 = new Sequence('?');
 $test2->run();
 assert($test2->getstr() == 'aa');
 var_dump($test2->getstr());*/

/**
 * Test 3
 * Prereq: 'c?'
 * PostReq: 'da';
 */
/*
 $test3 = new Sequence('c?');
 $test3->run();
 assert($test3->getStr() == 'da');
 var_dump($test3->getStr());
 */
/**
 * Test 4
 * Prereq: '??'
 * PostReq: 'aa';
 */

/*
 $test4 = new Sequence('??');
 $test4->run();
 assert($test4->getStr() == 'aaa');
 var_dump($test4->getStr());*/

/**
 * Test 5
 * Prereq: '????'
 * PostReq: 'aaaaa';
 */

/*
 $test5 = new Sequence('????');
 $test5->run();
 assert($test5->getStr() == 'aaaaa');
 var_dump($test5->getStr());*/

/**
 * Test 6
 * Prereq: '?????'
 * PostReq: 'aaaaaa';
 */
/*

 $test6 = new Sequence('abcd???');
 $test6->run();
 assert($test6->getStr() == 'abceaaa');
 var_dump($test6->getStr());
 */

$test7 = new Sequence('a');
$start = time();
while (true) {
	$test7->run();

	if (time() - $start >= 10) {
		var_dump($test7->getStr());

		break;
	}

}
