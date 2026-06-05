<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a'){
    header('location: ../login.php');
    exit();
}
include("../connection.php");

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $database->prepare("SELECT * FROM doctor WHERE docid = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if($_POST){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $tele = trim($_POST['tele']);
    $spec = (int) $_POST['spec'];

    $stmt = $database->prepare("UPDATE doctor SET docname = ?, docemail = ?, docnic = ?, doctel = ?, specialties = ? WHERE docid = ?");
    $stmt->bind_param("ssssii", $name, $email, $nic, $tele, $spec, $id);
    $stmt->execute();
    $stmt->close();

    header("location: doctors.php");
    exit();
}
?>

<form method="POST">
<input name="name" value="<?php echo $doc['docname']; ?>">
<input name="email" value="<?php echo $doc['docemail']; ?>">
<input name="nic" value="<?php echo $doc['docnic']; ?>">
<input name="tele" value="<?php echo $doc['doctel']; ?>">
<input name="spec" value="<?php echo $doc['specialties']; ?>">

<button>Update</button>
</form>