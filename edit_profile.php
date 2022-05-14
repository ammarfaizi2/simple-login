<?php

if (!isset($pdo)) {
	header("Location: profile.php");
	exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

	/*
	 * 
	 */
	// $em = explode("@", $_POST["email"]);
	// $em = $em[count($em) - 1];
	// if (strtolower($em) !== "gmail.com") {
	// 	$msg = "Email must be using gmail.com address";
	// 	goto show_html;
	// }

	if (strtolower($_POST["username"]) !== strtolower($u["username"])) {
		$st = $pdo->prepare("SELECT `id` FROM `users` WHERE username = ?");
		$st->execute([$_POST["username"]]);
		if ($st->fetch(\PDO::FETCH_NUM)) {
			$msg = "Username \"{$_POST["username"]}\" has already been registered, please use another username";
			goto show_html;
		}
	}

	if (strtolower($_POST["email"]) !== strtolower($u["email"])) {
		$st = $pdo->prepare("SELECT `id` FROM `users` WHERE email = ?");
		$st->execute([$_POST["email"]]);
		if ($st->fetch(\PDO::FETCH_NUM)) {
			$msg = "Email \"{$_POST["email"]}\" has already been registered, please use another email";
			goto show_html;
		}
	}

	$st = $pdo->prepare(<<<SQL
		UPDATE `users` SET
			`first_name` = ?, `last_name` = ?, `username` = ?,
			`email` = ?, `updated_at` = ?
		WHERE `id` = ?
SQL);
	$st->execute([
		$_POST["first_name"],
		$_POST["last_name"],
		$_POST["username"],
		$_POST["email"],
		date("Y-m-d H:i:s"),
		$_SESSION["user"]
	]);
	header("Location: profile.php");
	exit;
}


show_html:

if (isset($msg)) {
	$hex = str_split(bin2hex($msg), 2);
	$msg = "";
	foreach ($hex as $c)
		$msg .= "\\x{$c}";

}

?>
<?php if (isset($msg)): ?>
<script type="text/javascript">alert("<?php echo $msg; ?>");</script>
<?php endif ?>
<a href="profile.php"><h3>Back to Profile</h3></a>
<div class="photo-cage">
	<img class="photo" src="<?php echo htmlspecialchars($photo, ENT_QUOTES, "UTF-8"); ?>" />
</div>
<form method="POST" action="">
<table class="table-info">
	<tr><td align="left">First Name</td><td>:</td><td align="left"><input type="text" name="first_name" value="<?php echo htmlspecialchars($u["first_name"]); ?>" required="1"/></td></tr>
	<tr><td align="left">Last Name</td><td>:</td><td align="left"><input type="text" name="last_name" value="<?php echo htmlspecialchars($u["last_name"]); ?>" required="1"/></td></tr>
	<tr><td align="left">Username</td><td>:</td><td align="left"><input type="text" name="username" value="<?php echo htmlspecialchars($u["username"]); ?>" required="1"/></td></td></tr>
	<tr><td align="left">Email</td><td>:</td><td align="left"><input type="text" name="email" value="<?php echo htmlspecialchars($u["email"]); ?>" required="1"/></td></td></tr>
	<tr><td colspan="3" align="center"><input type="submit" name="save" value="Save"></td></tr>
</table>
</form>
