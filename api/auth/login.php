<?php

    require "../../connect.php";

    if ($_SERVER['REQUEST_METHOD']=="POST") {
        #code
        $response = array();
        $username = $_POST['username'];
        $password = $_POST['password'];

        $cek = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $result = mysqli_fetch_array(mysqli_query($con, $cek));

        if(isset($result)) {
            #code
            $response['value']=1;
            $response['message']="Login Berhasil";
            $response['username']=$result['username'];
            $response['fullname']=$result['fullname'];
            $response['id']=$result['id'];
            $response['level']=$result['level'];
            echo json_encode($response);
        } else {
            #code
            $response['value']=0;
            $response['message']="Login Gagal";
            echo json_encode($response);
        }        
    }

?>