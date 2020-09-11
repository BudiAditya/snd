<?php

    require "../../connect.php";

    $response = array();

    $sql = $con->query("SELECT * FROM customer");
    
    while ($rowdata = $sql->fetch_assoc()) {
        $response[] = $rowdata;
    }

    echo json_encode($response);
    
?>