<?php

session_start();

if (!isset($_SESSION["user"])) {
	header("Location: login.php");
	exit;
}
require __DIR__."/auto_logout.php";

$pdo = require __DIR__."/conn.php";
$st = $pdo->prepare("SELECT first_name, last_name, username, email FROM users WHERE id = ?");
$st->execute([$_SESSION["user"]]);
$u = $st->fetch(\PDO::FETCH_ASSOC);

$fullname = $u["first_name"];
if (!is_null($u["last_name"])) {
	$fullname .= " ".$u["last_name"];
}

?><!DOCTYPE html>
<html>
<head>
	<title><?php echo htmlspecialchars($fullname); ?></title>
	<style type="text/css">
		.profile-cage {
			width: 500px;
			height: 500px;
			border: 1px solid #000;
			margin: auto;
			text-align: center;
		}
		.profile-info {
			width: 400px;
			height: 300px;
			border: 1px solid #000;
			margin: auto;
			padding-top: 10px;
			text-align: center;
		}
		.table-info {
			margin: auto;
		}
		a {
			text-decoration: none;
		}
		a:hover {
			text-decoration: underline;
		}
		.photo {
			border: 1px solid #000;
			width: 150px;
			height: 150px;
			border-radius: 100%;
		}
	</style>
</head>
<body>
	<div class="profile-cage">
		<a href="home.php"><h2>Back to Home</h2></a>
		<h1><?php echo htmlspecialchars($fullname); ?></h1>
		<div class="profile-info">
			<?php if (isset($_GET["edit"])) { ?>
			<?php require __DIR__."/edit_profile.php"; ?>
			<?php } else if (isset($_GET["change_password"])) { ?>
			<?php require __DIR__."/change_password.php"; ?>
			<?php } else { ?>
				<div class="photo-cage">
					<img class="photo" />
				</div>
				<table class="table-info">
					<tr><td align="left">First Name</td><td>:</td><td align="left"><?php echo htmlspecialchars($u["first_name"]); ?></td></tr>
					<tr><td align="left">Last Name</td><td>:</td><td align="left"><?php echo htmlspecialchars($u["last_name"]); ?></td></tr>
					<tr><td align="left">Username</td><td>:</td><td align="left"><?php echo htmlspecialchars($u["username"]); ?></td></tr>
					<tr><td align="left">Email</td><td>:</td><td align="left"><?php echo htmlspecialchars($u["email"]); ?></td></tr>
					<tr><td colspan="3" align="center"><a href="?edit=1">Edit Profile</a></td></tr>
					<tr><td colspan="3" align="center"><a href="?change_password=1">Change Password</a></td></tr>
				</table>
			<?php } ?>
		</div>
	</div>
</body>
</html>
