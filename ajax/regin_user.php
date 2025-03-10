<?php
session_start();
include("../settings/connect_datebase.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$login = trim($_POST['login']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($login) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Все поля обязательны для заполнения."]);
    exit;
}

function validatePassword($password) {
    $errors = [];
    if (strlen($password) <= 8) {
        $errors[] = "Пароль должен содержать более 8 символов.";
    }
    if (!preg_match('/[a-zA-Z]/', $password)) {
        $errors[] = "Пароль должен содержать латинские буквы.";
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну цифру.";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы один специальный символ.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну заглавную букву.";
    }
    return $errors;
}

$errors = validatePassword($password);
if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => implode(" ", $errors)]);
    exit;
}

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '" . $mysqli->real_escape_string($login) . "' OR `email` = '" . $mysqli->real_escape_string($email) . "'");
if ($query_user->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Пользователь с таким логином или email уже существует."]);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

$insert_query = $mysqli->query("INSERT INTO `users` (`login`, `email`, `password`, `roll`, `verification_code`) VALUES ('" . $mysqli->real_escape_string($login) . "', '" . $mysqli->real_escape_string($email) . "', '" . $hashed_password . "', 0, '" . $mysqli->real_escape_string($code) . "')");
if (!$insert_query) {
    echo json_encode(["status" => "error", "message" => "Ошибка при регистрации."]);
    exit;
}

$new_user_id = $mysqli->insert_id;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'milomabs@yandex.ru';
    $mail->Password = '4cfeeb97fec7545f1e8dfe62567a7bea';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('milomabs@yandex.ru', 'Титов Дмтрий Антонович');
    $mail->addAddress($email);
    $mail->isHTML(false);
    $mail->Subject = 'Код подтверждения регистрации';
    $mail->Body = "Ваш код подтверждения: $code";
    $mail->send();

    $_SESSION['temp_user'] = [
        'id' => $new_user_id,
        'code' => $code,
        'email' => $email
    ];

    echo json_encode(["status" => "success", "message" => "Код подтверждения отправлен на вашу почту."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Не удалось отправить код на почту."]);
}
?>