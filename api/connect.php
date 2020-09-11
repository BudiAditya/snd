<?php

define('HOST','localhost');
define('USER','api');
define('PASS','12345678');
define('DB','db_api');

$con = mysqli_connect(HOST,USER,PASS,DB) or die('unable to connect');

?>