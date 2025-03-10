<?php
	session_start();
	include("check_auth.php");
	include("./settings/connect_datebase.php");
	
	$user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = " . $_SESSION['user']);
if (!$user_query || !$user_query->num_rows) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$user_data = $user_query->fetch_assoc();
?>
<!DOCTYPE HTML>
<html>
	<head> 
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<meta charset="utf-8">
		<title> Личный кабинет </title>
		
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
			<a href=# class = "singin"><img src = "img/ic-login.png"/></a>
		
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
				<div class="name" style="padding-bottom: 0px;">Личный кабинет</div>
				<div class="description">Добро пожаловать: 
				Добро пожаловать: <?php echo htmlspecialchars($user_data['login']); ?><br>
            	Ваш идентификатор: <?php echo htmlspecialchars($user_data['id']); ?>
       			 </div>
			
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
		
			function logout() {
				$.ajax({
					url         : 'ajax/logout.php',
					type        : 'POST', // важно!
					data        : null,
					cache       : false,
					dataType    : 'json',
					processData : false,
					contentType : false, 
					success: function (response) {
					if (response.status === "success") {
						alert(response.message);
						window.location.href = "login.php";
					} else {
						alert(response.message);
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