<?php
session_start();
include("./settings/connect_datebase.php");

// Проверка авторизации пользователя
if (isset($_SESSION['user']) && $_SESSION['user'] != -1) {
    $user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = " . $_SESSION['user']);
    if ($user_read = $user_query->fetch_row()) {
        // Перенаправление в зависимости от роли пользователя
        if ($user_read[3] == 0) {
            header("Location: user.php");
            exit;
        } elseif ($user_read[3] == 1) {
            header("Location: admin.php");
            exit;
        }
    }
}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Регистрация</title>
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
            <div class="name">Регистрация</div>

            <form id="registrationForm" onsubmit="return false;">
                <div class="sub-name">Логин:</div>
                <input name="_login" type="text" placeholder="Введите логин" required/>

                <div class="sub-name">Email:</div>
                <input name="_email" type="email" placeholder="Введите email" required/>

                <div class="sub-name">Пароль:</div>
                <input name="_password" type="password" placeholder="Введите пароль" required/>

                <div class="sub-name">Повторите пароль:</div>
                <input name="_passwordCopy" type="password" placeholder="Повторите пароль" required/>

                <a href="login.php">Вернуться</a>
                <input type="button" class="button" value="Зарегистрироваться" onclick="RegIn()" style="margin-top: 0px;"/>
                <img src="img/loading.gif" class="loading" style="margin-top: 0px; display: none;"/>
            </form>
        </div>

        <div class="footer">
            © КГАПОУ "Авиатехникум", 2020
            <a href="#">Конфиденциальность</a>
            <a href="#">Условия</a>
        </div>
    </div>
</div>

<script>
    var loading = document.getElementsByClassName("loading")[0];
    var button = document.getElementsByClassName("button")[0];

    // Функция для валидации пароля
    function validatePassword(password) {
        if (password.length <= 8) {
            return "Пароль должен содержать более 8 символов.";
        }
        if (!/[a-zA-Z]/.test(password)) {
            return "Пароль должен содержать латинские буквы.";
        }
        if (!/\d/.test(password)) {
            return "Пароль должен содержать хотя бы одну цифру.";
        }
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            return "Пароль должен содержать хотя бы один специальный символ.";
        }
        if (!/[A-Z]/.test(password)) {
            return "Пароль должен содержать хотя бы одну заглавную букву.";
        }
        return true;
    }

    // Функция для отправки данных на сервер
    function RegIn() {
        console.log("Функция RegIn вызвана"); // Отладочное сообщение
        var _login = document.getElementsByName("_login")[0].value.trim();
        var _email = document.getElementsByName("_email")[0].value.trim();
        var _password = document.getElementsByName("_password")[0].value.trim();
        var _passwordCopy = document.getElementsByName("_passwordCopy")[0].value.trim();

        if (_login === "") {
            alert("Введите логин.");
            return;
        }
        if (_email === "") {
            alert("Введите email.");
            return;
        }
        if (_password === "") {
            alert("Введите пароль.");
            return;
        }
        if (_password !== _passwordCopy) {
            alert("Пароли не совпадают.");
            return;
        }

        var passwordValidationResult = validatePassword(_password);
        if (passwordValidationResult !== true) {
            alert(passwordValidationResult);
            return;
        }

        loading.style.display = "block";
        button.className = "button_diactive";

        var data = new FormData();
        data.append("login", _login);
        data.append("email", _email);
        data.append("password", _password);

        $.ajax({
            url: 'ajax/regin_user.php',
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function (response) {
                console.log("Ответ сервера:", response); // Отладочное сообщение
                try {
                    var data = JSON.parse(response);
                    if (data.status === "error") {
                        alert(data.message);
                    } else {
                        alert(data.message);
                        window.location.href = "verify_code.php";
                    }
                } catch (e) {
                    console.error("Ошибка при обработке ответа сервера:", e);
                    alert("Произошла ошибка. Пожалуйста, попробуйте позже.");
                }
            },
            error: function () {
                console.log('Системная ошибка!');
                alert("Не удалось выполнить запрос к серверу.");
            },
            complete: function () {
                loading.style.display = "none";
                button.className = "button";
            }
        });
    }
</script>
</body>
</html>
</body>
</html>