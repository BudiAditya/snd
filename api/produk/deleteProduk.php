<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $idProduk = $_POST['idProduk'];

        $insert = "DELETE FROM produk WHERE id='$idProduk'";
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