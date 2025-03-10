<?php
session_start();
include("../settings/connect_datebase.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$login = trim($_POST['login']);

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '" . $mysqli->real_escape_string($login) . "'");
if ($user = $query_user->fetch_assoc()) {
    $new_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()"), 0, 12);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_query = $mysqli->query("UPDATE `users` SET `password` = '" . $mysqli->real_escape_string($hashed_password) . "', `password_updated_at` = NOW() WHERE `id` = " . $user['id']);
    if (!$update_query) {
        echo json_encode(["status" => "error", "message" => "Ошибка при обновлении пароля."]);
        exit;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.yandex.ru';
        $mail->SMTPAuth = true;
        $mail->Username = 'milomabs@yandex.ru'; 
        $mail->Password = 'cldchunnvgezyjbj'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom('milomabs@yandex.ru', 'Титов Дмитрий Антонович');
        $mail->addAddress($user['email']); 

        $mail->isHTML(true);
        $mail->Subject = 'Восстановление пароля';
        $mail->Body = "
            <h1>Восстановление пароля</h1>
            <p>Здравствуйте!</p>
            <p>Вы запросили восстановление пароля. Ваш новый пароль:</p>
            <p><b>$new_password</b></p>
            <p>Для входа в систему перейдите по ссылке: <a href='http://localhost/pr8tit/security/login.php'>Войти в систему</a>.</p>
            <p>Если вы не запрашивали восстановление пароля, пожалуйста, проигнорируйте это письмо.</p>
        ";

        $mail->send();

        echo json_encode(["status" => "success", "message" => "Новый пароль отправлен на вашу почту."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Не удалось отправить письмо с новым паролем."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Пользователь с таким логином не найден."]);
}
?>