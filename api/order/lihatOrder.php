<?php

    require "../../connect.php";

    $idUsers = $_GET['idUsers'];
    $response = array();

    $sql = mysqli_query($con, "SELECT * FROM sales_order WHERE idUsers = '$idUsers'");
    while ($a = mysqli_fetch_array($sql)) {
        # code...

        $b['id'] = $a['id'];
        $b['customer'] = $a['customer'];
        $b['produk'] = $a['produk'];
        $b['idUsers'] = $a['idUsers'];

        array_push($response, $b);
    }

    echo json_encode($response);
    
?>