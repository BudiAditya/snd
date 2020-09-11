<?php

    require "../../connect.php";

    $response = array();

    $sql = mysqli_query($con, "SELECT * FROM customer");
    while ($a = mysqli_fetch_array($sql)) {
        # code...

        $b['id'] = $a['id'];
        $b['namaCustomer'] = $a['namaCustomer'];
        $b['alamat'] = $a['alamat'];

        array_push($response, $b);
    }

    echo json_encode($response);
    
?>