<?php

const PHOTO_PATH = "/storage/files";

session_start();

if (!isset($_SESSION["user"])) {
	header("Location: login.php");
	exit;
}
require __DIR__."/auto_logout.php";

$pdo = require __DIR__."/conn.php";
$st = $pdo->prepare("SELECT first_name, last_name, username, email, photo FROM users WHERE id = ?");
$st->execute([$_SESSION["user"]]);
$u = $st->fetch(\PDO::FETCH_ASSOC);

$fullname = $u["first_name"];
if (!is_null($u["last_name"])) {
	$fullname .= " ".$u["last_name"];
}

$photo = NULL;
if (!is_null($u["photo"])) {
	$st = $pdo->prepare("SELECT LOWER(HEX(md5_sum)), LOWER(HEX(sha1_sum)), ext FROM files WHERE id = ? LIMIT 1");
	$st->execute([$u["photo"]]);
	$tmp = $st->fetch(PDO::FETCH_NUM);
	if ($tmp)
		$photo = PHOTO_PATH . "/" . $tmp[0] . "_" . $tmp[1] . "." . $tmp[2];
}
if (is_null($photo)) 
	$photo = "default_photo.jpg";

?><!DOCTYPE html>
<html>
<head>
	<title><?php echo htmlspecialchars($fullname); ?></title>
	<style type="text/css">
		.profile-cage {
			width: 500px;
			height: 600px;
			border: 1px solid #000;
			margin: auto;
			text-align: center;
		}
		.profile-info {
			width: 400px;
			height: 400px;
			border: 1px solid #000;
			margin: auto;
			padding-top: 10px;
			text-align: center;
		}
		.table-info {
			margin: auto;
			margin-top: 40px;
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
				<?php if (isset($_GET["action"])) {
					switch ($_GET["action"]) {
					case "edit_photo":
						require __DIR__."/edit_photo.php";
						break;
					}
				} else { ?>
					<div class="photo-cage">
						<img class="photo" src="<?php echo htmlspecialchars($photo, ENT_QUOTES, "UTF-8"); ?>" />
					</div>
					<a href="?action=edit_photo">Edit Photo</a>
				<?php } ?>
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
