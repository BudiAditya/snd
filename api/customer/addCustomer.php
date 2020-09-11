<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $namaCustomer = $_POST['namaCustomer'];
        $alamat = $_POST['alamat'];
        $idUsers = $_POST['idUsers'];

        $insert = "INSERT INTO customer VALUE(NULL,'$namaCustomer','$alamat','$idUsers')";
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