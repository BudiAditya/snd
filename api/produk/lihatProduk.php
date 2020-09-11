<?php

    require "../../connect.php";

    $response = array();

    $sql = mysqli_query($con, "SELECT a.*, b.fullname FROM produk a 
    left join users b on a.idUsers = b.id");
    while ($a = mysqli_fetch_array($sql)) {
        # code...

        $b['id'] = $a['id'];
        $b['namaProduk'] = $a['namaProduk'];
        $b['qty'] = $a['qty'];
        $b['harga'] = $a['harga'];
        $b['createdDate'] = $a['createdDate'];
        $b['idUsers'] = $a['idUsers'];
        $b['image'] = $a['image'];
        $b['fullname'] = $a['fullname'];
        $b['expDate'] = $a['expDate'];

        array_push($response, $b);
    }

    echo json_encode($response);
    
?>