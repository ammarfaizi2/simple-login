<?php

session_start();

if (!isset($_SESSION["user"])) {
	header("Location: login.php");
	exit;
}
require __DIR__."/auto_logout.php";

$pdo = require __DIR__."/conn.php";
$st = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
$st->execute([$_SESSION["user"]]);
$st = $st->fetch(\PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html>
<head>
	<title>Welcome <?php echo htmlspecialchars($st["first_name"]); ?></title>
</head>
<body>
	<a href="logout.php"><h3>Logout</h3></a>
	<h1>Welcome <?php echo htmlspecialchars($st["first_name"]); ?></h1>
	<a href="profile.php"><h3>Open profile</h3></a>
</body>
</html>
