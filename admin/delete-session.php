<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){
        include("../connection.php");
        $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
        $stmt = $database->prepare("DELETE FROM schedule WHERE scheduleid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("location: schedule.php");
    }


?>