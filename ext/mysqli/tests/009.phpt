--TEST--
mysqli fetch bigint values (ok to fail with 4.1.x)
--SKIPIF--
<?php
	if (PHP_INT_SIZE == 8) {
		echo 'skip test valid only for 32bit systems';
		exit;
	}
	require_once('skipif.inc');
?>
--FILE--
<?php
	include "connect.inc";

	/*** test mysqli_connect 127.0.0.1 ***/
	$link = mysqli_connect($host, $user, $passwd, $db, $port, $socket);

	if (!mysqli_query($link, "SET sql_mode=''"))
		printf("[001] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	if (!mysqli_query($link, "DROP TABLE IF EXISTS test_bind_fetch"))
		printf("[002] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	$rc = mysqli_query($link,"CREATE TABLE test_bind_fetch(c1 bigint default 5,
													c2 bigint,
													c3 bigint not NULL,
													c4 bigint unsigned,
													c5 bigint unsigned,
													c6 bigint unsigned,
													c7 bigint unsigned) ENGINE=" . $engine);
	if (!$rc)
		printf("[003] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	$rc = mysqli_query($link, "INSERT INTO test_bind_fetch (c2,c3,c4,c5,c6,c7) ".
							  "VALUES (-23,4.0,33333333333333,0,-333333333333,99.9)");
	if (!$rc)
		printf("[004] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	$stmt = mysqli_prepare($link, "SELECT * FROM test_bind_fetch");
	mysqli_bind_result($stmt, $c1, $c2, $c3, $c4, $c5, $c6, $c7);
	mysqli_execute($stmt);
	$rc = mysqli_fetch($stmt);

	if (mysqli_get_server_version($link) < 50000) {
		// 4.1 is faulty and will return big number for $c6
		if ($c6 == "18446743740376218283") {
			$c6 = 0;
		}
	}
	$test = array($c1,$c2,$c3,$c4,$c5,$c6,$c7);

	var_dump($test);

	mysqli_stmt_close($stmt);

	if (!mysqli_query($link, "DROP TABLE IF EXISTS test_bind_fetch_uint"))
		printf("[005] [%d] %s\n", mysqli_errno($link), mysqli_error($link));
	$rc = mysqli_query($link, "CREATE TABLE test_bind_fetch_uint(c1 integer unsigned, c2 integer unsigned) ENGINE=" . $engine);
	if (!$rc)
		printf("[006] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	if (!mysqli_query($link, "INSERT INTO test_bind_fetch_uint (c1,c2) VALUES (20123456, 3123456789)"))
		printf("[007] [%d] %s\n", mysqli_errno($link), mysqli_error($link));

	$stmt = mysqli_prepare($link, "SELECT * FROM test_bind_fetch_uint");
	mysqli_bind_result($stmt, $c1, $c2);
	mysqli_execute($stmt);
	$rc = mysqli_fetch($stmt);

	echo $c1, "\n", $c2, "\n";

	mysqli_stmt_close($stmt);
	mysqli_close($link);
	print "done!";
?>

--EXPECTF--
array(7) {
  [0]=>
  int(5)
  [1]=>
  int(-23)
  [2]=>
  int(4)
  [3]=>
  string(14) "33333333333333"
  [4]=>
  int(0)
  [5]=>
  int(0)
  [6]=>
  int(100)
}
20123456
3123456789
done!
--UEXPECTF--
array(7) {
  [0]=>
  int(5)
  [1]=>
  int(-23)
  [2]=>
  int(4)
  [3]=>
  unicode(14) "33333333333333"
  [4]=>
  int(0)
  [5]=>
  int(0)
  [6]=>
  int(100)
}
20123456
3123456789
done!