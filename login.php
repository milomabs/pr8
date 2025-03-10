<?php
session_start();
require_once("./settings/connect_datebase.php");

if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
    $user_id = $_SESSION['user'];
    $token = $_SESSION['token'];

    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['session_token'] === $token) {
            if ($user['roll'] == 0) {
                header("Location: user.php");
                exit;
            } elseif ($user['roll'] == 1) {
                header("Location: admin.php");
                exit;
            }
        }
    }
}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Авторизация</title>
    <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-menu">
    <a href="#"><img src="img/logo1.png"/></a>
    <div class="name">
        <a href="index.php">
            <div class="subname">БЕЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
            Пермский авиационный техникум им. А. Д. Швецова
        </a>
    </div>
</div>
<div class="space"></div>
<div class="main">
    <div class="content">
        <div class="login">
            <div class="name">Авторизация</div>
            <div class="sub-name">Логин:</div>
            <input name="_login" type="text" placeholder="Введите логин" required onkeypress="return PressToEnter(event)"/>
            <div class="sub-name">Пароль:</div>
            <input name="_password" type="password" placeholder="Введите пароль" required onkeypress="return PressToEnter(event)"/>
            <a href="regin.php">Регистрация</a>
            <br><a href="recovery.php">Забыли пароль?</a>
            <input type="button" class="button" value="Войти" onclick="LogIn()"/>
            <img src="img/loading.gif" class="loading" style="display: none;"/>
        </div>
        <div class="footer">
            © КГАПОУ "Авиатехникум", 2020
            <a href="#">Конфиденциальность</a>
            <a href="#">Условия</a>
        </div>
    </div>
</div>
<script>
    function LogIn() {
        var loading = document.getElementsByClassName("loading")[0];
        var button = document.getElementsByClassName("button")[0];
        var _login = document.getElementsByName("_login")[0].value.trim();
        var _password = document.getElementsByName("_password")[0].value.trim();

        if (_login === "" || _password === "") {
            alert("Введите логин и пароль.");
            return;
        }

        loading.style.display = "block";
        button.className = "button_diactive";

        var data = new FormData();
        data.append("login", _login);
        data.append("password", _password);

        $.ajax({
            url: 'ajax/login_user.php',
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === "success") {
                    location.reload();
                } else if (response.status === "expired") {
                    window.location.href = response.redirect;
                } else if (response.status === "ip") {
                    window.location.href = response.redirect;
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                // Выводим информацию об ошибке в консоль
                console.error("Ошибка AJAX-запроса:");
                console.error("Статус:", status);
                console.error("Текст ошибки:", error);
                console.error("Ответ сервера:", xhr.responseText);

                // Опционально: показываем сообщение об ошибке пользователю
                alert("Произошла ошибка: " + error);

},
            complete: function () {
                loading.style.display = "none";
                button.className = "button";
            }
        });
    }

    function PressToEnter(e) {
        if (e.keyCode === 13) {
            LogIn();
        }
    }
</script>
</body>
</html>