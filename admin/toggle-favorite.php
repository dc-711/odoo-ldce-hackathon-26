<?php
session_start();
include("db.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$city_id = (int)($_GET['city_id'] ?? 0);

if ($city_id > 0) {
    $stmt = $conn->prepare("SELECT id FROM cities WHERE id=?");
    $stmt->bind_param("i",$city_id); $stmt->execute();
    $valid = $stmt->get_result()->num_rows === 1; $stmt->close();

    if ($valid) {
        $stmt=$conn->prepare("SELECT id FROM favorites WHERE user_id=? AND city_id=? LIMIT 1");
        $stmt->bind_param("ii",$user_id,$city_id);$stmt->execute();$r=$stmt->get_result();
        if($r->num_rows){
            $fav=$r->fetch_assoc();$stmt->close();
            $del=$conn->prepare("DELETE FROM favorites WHERE id=? AND user_id=?");
            $del->bind_param("ii",$fav['id'],$user_id);$del->execute();$del->close();
        } else {
            $stmt->close();
            $ins=$conn->prepare("INSERT INTO favorites (user_id,city_id,activity_id) VALUES (?, ?, NULL)");
            $ins->bind_param("ii",$user_id,$city_id);$ins->execute();$ins->close();
        }
    }
}

$back = $_SERVER['HTTP_REFERER'] ?? 'cities.php';
header("Location: ".$back);
exit;
?>
