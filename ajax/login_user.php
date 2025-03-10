<?php
session_start();
require_once("../settings/connect_datebase.php");
$login = $_POST["login"] ?? '';
$password = $_POST["password"] ?? '';

if (empty($login) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Логин и пароль обязательны."]);
    exit;
}
$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `login` = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (!password_verify($password, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "Неверный логин или пароль."]);
        exit;
    }
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `login` = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (!empty($user['verification_code'])) {
                echo json_encode(["status" => "error", "message" => "подтвердите почту."]);
                exit;
            }
        } 
    $password_updated_at = strtotime($user['password_updated_at']);
    $current_time = time();
    $days_since_update = ($current_time - $password_updated_at) / (60 * 60 * 24);
    $password_expiration_days = 30;

    if ($days_since_update > $password_expiration_days) {
        echo json_encode([
            "status" => "expired",
            "message" => "Срок действия вашего пароля истёк. Пожалуйста, восстановите пароль.",
            "redirect" => "recovery.php"
        ]);
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
    if ($user['last_latitude'] && $user['last_longitude']) {
        $earthRadius = 6371; 
        $dLat = deg2rad($latitude - $user['last_latitude']);
        $dLon = deg2rad($longitude - $user['last_longitude']);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($user['last_latitude'])) * cos(deg2rad($latitude)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        if ($distance > 500) {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            sendVerificationCode($user['email'], $code, $user['id']);
            $update_stmt = $mysqli->prepare("UPDATE `users` SET `verification_code` = ? WHERE `id` = ?");
            $update_stmt->bind_param("si", $code, $user['id']);

            if (!$update_stmt->execute()) {
                echo json_encode(["status" => "error", "message" => "Ошибка при обновлении кода подтверждения."]);
                exit;
            }
            echo json_encode([
                "status" => "ip",
                "message" => "Обнаружен вход с нового местоположения. Код подтверждения выслан на вашу почту.",
                "redirect" => "verify_code.php"
            ]);
            exit;
        }
    }
    $token = bin2hex(random_bytes(32));
    $update_stmt = $mysqli->prepare("UPDATE `users` SET `session_token` = ?, `last_latitude` = ?, `last_longitude` = ? WHERE `id` = ?");
    $update_stmt->bind_param("sddi", $token, $latitude, $longitude, $user['id']);

    if (!$update_stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Ошибка при обновлении токена."]);
        exit;
    }
   
    $_SESSION['user'] = $user['id'];
    $_SESSION['token'] = $token;
    
    if ($user['roll'] == 0) { 
        echo json_encode([
            "status" => "success",
            "message" => "Авторизация успешна.",
            "redirect" => "user.php"
        ]);
    } elseif ($user['roll'] == 1) { 
        echo json_encode([
            "status" => "success",
            "message" => "Авторизация успешна.",
            "redirect" => "admin.php"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Неизвестная роль пользователя."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Пользователь не найден."]);
}
function sendVerificationCode($email, $code, $idsss) {
    require_once("../phpmailer/src/PHPMailer.php");
    require_once("../phpmailer/src/SMTP.php");

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.yandex.ru';
        $mail->SMTPAuth = true;
        $mail->Username = 'shilova.1138@yandex.ru';
        $mail->Password = 'cldchunnvgezyjbj';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('shilova.1138@yandex.ru', 'Шилова Валерия Дмитриевна');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Подтверждение входа';
        $mail->Body = "Ваш код подтверждения: $code";

         $_SESSION['temp_user'] = [
        'id' => $idsss,
        'code' => $code,
        'email' => $email
    ];

        $mail->send();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Не удалось отправить письмо с подтверждением."]);
    }
}