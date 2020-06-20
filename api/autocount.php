<?php
 $mydb1 = new mysqli("localhost","rekapos","P4ssw0rd","db_rekapos_erdita1");
 $sql1 = "Select fc_ic_stockautocorrection(1) as recAffected;"
 
 //create connection1
 if ($mydb1->connect_errno){
	exit();
 }else{
	// Perform query
	if ($result = $mydb1->query($sql1)) {
		//echo "Returned rows are: " . $result -> num_rows;
		// Free result set
		$result -> free_result();
	}
	$mydb1->close();
 }
?>