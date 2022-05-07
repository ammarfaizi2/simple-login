<?php

session_start();

if (isset($_SESSION["user"])) {
	header("Location: home.php?ref=login");
	exit;
}

if (isset($_GET["action"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
	$validRequest =
	    isset($_POST["username"], $_POST["password"]) &&
	    is_string($_POST["username"]) &&
	    is_string($_POST["password"]);

	if (!$validRequest) {
		$msg = "Invalid request";
		goto show_html;
	}

	if (filter_var($_POST["username"], FILTER_VALIDATE_EMAIL)) {
		$query = "WHERE `email` = ?";
	} else {
		$query = "WHERE `username` = ?";
	}

	$pdo = require __DIR__."/conn.php";
	$st = $pdo->prepare("SELECT `id`, `password` FROM `users` {$query}");
	$st->execute([$_POST["username"]]);
	$st = $st->fetch(\PDO::FETCH_NUM);
	if (!$st) {
		$msg = "Email or username is invalid";
		goto show_html;
	}

	if (!password_verify($_POST["password"], $st[1])) {
		$msg = "Wrong password";
		goto show_html;	
	}

	$_SESSION["user"] = $st[0];
	$_SESSION["login_at"] = time();
	header("Location: home.php");
	exit;
}

show_html:

?><!DOCTYPE html>
<html>
<head>
	<title>Login</title>
</head>
<body>
	<?php if (isset($msg)): ?>
	<h1>Error: <?php echo htmlspecialchars($msg); ?></h1>
	<?php endif ?>
	<h1>Login</h1>
	<form action="?action=1" method="POST">
		<table>
			<tr><td>Username or Email</td><td>:</td><td><input type="text" name="username" required="1"></td></tr>
			<tr><td>Password</td><td>:</td><td><input type="password" name="password" required="1"></td></tr>
			<tr><td colspan="3" align="center"><input type="submit" name="login" value="Login"></td></tr>
		</table>
		<h3>Don't have an account? <a href="register.php">Register</a></h3>
	</form>
</body>
</html>
