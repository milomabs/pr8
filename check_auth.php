<?php
session_start();
include("./settings/connect_datebase.php");

if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user'];
$token = $_SESSION['token'];

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `id` = " . $user_id);
if ($user = $query_user->fetch_assoc()) {
    if ($user['session_token'] !== $token) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} else {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>