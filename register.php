<?php

session_start();

if (isset($_SESSION["user"])) {
	header("Location: home.php?ref=register");
	exit;
}

if (isset($_GET["action"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

	$validRequest =
	    isset($_POST["first_name"], $_POST["last_name"], $_POST["username"],
		  $_POST["email"], $_POST["password"], $_POST["cpassword"]) &&
	    is_string($_POST["first_name"]) &&
	    is_string($_POST["last_name"]) &&
	    is_string($_POST["username"]) &&
	    is_string($_POST["email"]) &&
	    is_string($_POST["password"]) &&
	    is_string($_POST["cpassword"]);

	if (!$validRequest) {
		$msg = "Invalid request";
		goto show_html;
	}

	$c = strlen($_POST["first_name"]);
	if ($c < 3) {
		$msg = "First name must be at least 3 characters";
		goto show_html;
	}

	if ($c > 255) {
		$msg = "First name must not be more than 255 characters";
		goto show_html;
	}

	$c = strlen($_POST["last_name"]);
	if ($c > 255) {
		$msg = "Last name must not be more than 255 characters";
		goto show_html;
	}

	$usernamePattern = "/^[a-zA-Z][a-zA-Z0-9\_]{3,63}$/";
	if (!preg_match($usernamePattern, $_POST["username"])) {
		$msg = "Username must match the regex pattern {$usernamePattern}";
		goto show_html;
	}

	if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
		$msg = "Invalid email";
		goto show_html;
	}

	$c = strlen($_POST["password"]);
	if ($c < 6) {
		$msg = "Password must be at least 6 characters";
		goto show_html;
	}
	if ($c > 512) {
		$msg = "Password is too long, can't be longer than 512 characters";
		goto show_html;
	}

	if ($_POST["password"] !== $_POST["cpassword"]) {
		$msg = "Password must be the same with the Retype Password";
		goto show_html;
	}

	$pdo = require __DIR__."/conn.php";

	$st = $pdo->prepare("SELECT `id` FROM `users` WHERE username = ?");
	$st->execute([$_POST["username"]]);
	if ($st->fetch(\PDO::FETCH_NUM)) {
		$msg = "Username \"{$_POST["username"]}\" has already been registered, please use another username";
		goto show_html;
	}

	$st = $pdo->prepare("SELECT `id` FROM `users` WHERE email = ?");
	$st->execute([$_POST["email"]]);
	if ($st->fetch(\PDO::FETCH_NUM)) {
		$msg = "Email \"{$_POST["email"]}\" has already been registered, please use another email";
		goto show_html;
	}

	$st = $pdo->prepare(<<<SQL
		INSERT INTO `users`
		(`first_name`, `last_name`, `username`, `email`, `password`, `created_at`)
		VALUES
		(?, ?, ?, ?, ?, ?);
SQL);
	$st->execute([
		$_POST["first_name"],
		$_POST["last_name"],
		$_POST["username"],
		$_POST["email"],
		password_hash($_POST["password"], PASSWORD_BCRYPT),
		date("Y-m-d H:i:s")
	]);
	header("Location: login.php");
	exit;
}

show_html:

?><!DOCTYPE html>
<html>
<head>
	<title>Register Form</title>
</head>
<body>
	<div>
		<?php if (isset($msg)): ?>
		<h2>Error: <?php echo htmlspecialchars($msg); ?></h2>
		<?php endif; ?>
		<h1>Register Form</h1>
		<form method="POST" action="?action=1">
		<table>
			<tr><td>First Name</td><td>:</td><td><input type="text" name="first_name" required="1"></td></tr>
			<tr><td>Last Name</td><td>:</td><td><input type="text" name="last_name" required="1"></td></tr>
			<tr><td>Username</td><td>:</td><td><input type="text" name="username" required="1"></td></tr>
			<tr><td>Email</td><td>:</td><td><input type="text" name="email" required="1"></td></tr>
			<tr><td>Password</td><td>:</td><td><input type="password" name="password" required="1"></td></tr>
			<tr><td>Retype Password</td><td>:</td><td><input type="password" name="cpassword" required="1"></td></tr>
			<tr><td colspan="3" align="center"><input type="submit" name="register" value="Register"></td></tr>
		</table>
		<h3>Already have an account? <a href="login.php">Login</a></h3>
		</form>
	</div>
</body>
</html>
