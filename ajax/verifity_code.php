<?php
session_start();
include("../settings/connect_datebase.php");

if (!isset($_SESSION['temp_user'])) {
    echo json_encode(["status" => "error", "message" => "Сессия истекла. Повторите регистрацию."]);
    exit;
}

$url = "http://ipwho.is/";
$response = file_get_contents($url);

if ($response === FALSE) {
    echo json_encode(["status" => "error", "message" => "Ошибка при получении данных от API."]);
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['latitude'], $data['longitude'])) {
    echo json_encode(["status" => "error", "message" => "Ошибка при обработке данных о местоположении."]);
    exit;
}

$latitude = $data['latitude'];
$longitude = $data['longitude'];

$input_code = trim($_POST['code']);
$temp_user = $_SESSION['temp_user'];

if ($input_code === $temp_user['code']) {
    $token = bin2hex(random_bytes(32));
    $update_query = $mysqli->prepare("
        UPDATE `users` 
        SET `verification_code` = NULL, 
            `session_token` = ?, 
            `last_latitude` = ?, 
            `last_longitude` = ? 
        WHERE `id` = ?
    ");
    $update_query->bind_param("sddi", $token, $latitude, $longitude, $temp_user['id']);

    if ($update_query->execute()) {
        $_SESSION['user'] = $temp_user['id'];
        $_SESSION['token'] = $token;

        unset($_SESSION['temp_user']);

        echo json_encode([
            "status" => "success",
            "message" => "Авторизация (регистрация) в аккаунт успешна."
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ошибка при подтверждении кода."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Неверный код."]);
}
?>