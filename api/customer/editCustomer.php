<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $namaCustomer = $_POST['namaCustomer'];
        $alamat = $_POST['alamat'];
        $idCustomer = $_POST['idCustomer'];

        $insert = "UPDATE customer SET namaCustomer = '$namaCustomer', alamat = '$alamat' WHERE id = '$idCustomer'";
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