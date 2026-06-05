<?php
include("../connection.php");

if($_POST){

    $name = trim($_POST['name']);
    $nic = trim($_POST['nic']);
    $spec = (int) $_POST['spec'];
    $email = trim($_POST['email']);
    $tele = trim($_POST['tele']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_stmt = $database->prepare("SELECT * FROM webuser WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check = $check_stmt->get_result();
    $check_stmt->close();

    if($check->num_rows == 0){
        $doctor_stmt = $database->prepare("INSERT INTO doctor(docemail, docname, docpassword, docnic, doctel, specialties) VALUES (?, ?, ?, ?, ?, ?)");
        $doctor_stmt->bind_param("sssssi", $email, $name, $password, $nic, $tele, $spec);
        $doctor_stmt->execute();
        $doctor_stmt->close();

        $webuser_stmt = $database->prepare("INSERT INTO webuser(email, usertype) VALUES (?, 'd')");
        $webuser_stmt->bind_param("s", $email);
        $webuser_stmt->execute();
        $webuser_stmt->close();
    }

    header("location: doctors.php");
}
?>