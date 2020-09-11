<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $namaProduk = $_POST['namaProduk'];
        $qty = $_POST['qty'];
        $harga = $_POST['harga'];
        $idProduk = $_POST['idProduk'];
        $expDate = $_POST['expDate'];

        $insert = "UPDATE produk SET namaProduk = '$namaProduk', qty = '$qty', harga = '$harga', expDate = '$expDate' WHERE id = '$idProduk'";
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