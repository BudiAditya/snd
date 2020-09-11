<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $customer = $_POST['customer'];
        $produk = $_POST['produk'];
        $idUsers = $_POST['idUsers'];
        $idCustomer = $_POST['idCustomer'];

        $insert = "INSERT INTO sales_order VALUE(NULL,'$customer','$produk','$idUsers','$idCustomer')";
        if (mysqli_query($con, $insert)) {
            #code
            $response['value']=1;
            $response['message']="Berhasil disimpan";
            echo json_encode($response);
        } else {
            #code
            $response['value']=0;
            $response['message']="Gagal disimpan";
            echo json_encode($response);
        }  
    }

?>