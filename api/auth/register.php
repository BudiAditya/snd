<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $username = $_POST['username'];
        $password = $_POST['password'];
        $fullname = $_POST['fullname'];

        $cek = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_fetch_array(mysqli_query($con, $cek));

        if(isset($result)) {
            #code
            $response['value']=2;
            $response['message']="Username Sudah Digunakan";
            echo json_encode($response);
        } else {
            $insert = "INSERT INTO users VALUE(NULL,'$fullname','$username','$password','1','1',NOW())";
            if (mysqli_query($con, $insert)) {
                #code
                $response['value']=1;
                $response['message']="Berhasil Daftar";
                echo json_encode($response);
            } else {
                #code
                $response['value']=0;
                $response['message']="Gagal Daftar";
                echo json_encode($response);
            }
        }        
    }

?>