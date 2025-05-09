<?php
	session_start();
	include("./settings/connect_datebase.php");
	
	if (isset($_SESSION['user'])) {
		if($_SESSION['user'] != -1) {
			$user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']); // проверяем
			while($user_read = $user_query->fetch_row()) {
				if($user_read[3] == 0) header("Location: index.php");
			}
		} else header("Location: login.php");
 	} else {
		header("Location: login.php");
		echo "Пользователя не существует";
	}
	include("check_auth.php");
?>
<!DOCTYPE HTML>
<html>
	<head> 
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<meta charset="utf-8">
		<title> Admin панель </title>
		
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">

			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
				<input type="button" class="button" value="Выйти" onclick="logout()"/>
				
				<div class="name">Административная панель</div>
			
				Административная панель служит для создания, редактирования и удаления записей на сайте.
			
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
			function Logout() {
				$.ajax({
					url: 'ajax/logout.php',
					type: 'POST',
					cache: false,
					dataType: 'html',
					processData: false,
					contentType: false,
					success: function (response) {
						try {
							var data = JSON.parse(response);
							if (data.status === "success") {
								alert(data.message);
								window.location.href = "login.php";
							} else {
								alert(data.message);
							}
						} catch (e) {
							console.error("Ошибка при обработке ответа сервера:", e);
							alert("Произошла ошибка. Пожалуйста, попробуйте позже.");
						}
					},
					error: function () {
						console.log('Системная ошибка!');
						alert("Не удалось выполнить запрос к серверу.");
					}
				});
			}

		</script>
	</body>
</html>