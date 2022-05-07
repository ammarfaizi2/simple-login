<?php

if (!isset($pdo)) {
	header("Location: profile.php");
	exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$validRequest =
	    isset($_POST["old_password"], $_POST["new_password"], $_POST["cnew_password"]) &&
	    is_string($_POST["old_password"]) &&
	    is_string($_POST["new_password"]) &&
	    is_string($_POST["cnew_password"]);

	if (!$validRequest) {
		$msg = "Invalid request";
		goto show_html;
	}

	$st = $pdo->prepare("SELECT `password` FROM `users` WHERE `id` = ?");
	$st->execute([$_SESSION["user"]]);
	$st = $st->fetch(\PDO::FETCH_NUM);
	if (!$st) {
		$msg = "Invalid user_id";
		goto show_html;
	}

	if (!password_verify($_POST["old_password"], $st[0])) {
		$msg = "Wrong old password";
		goto show_html;	
	}

	$c = strlen($_POST["new_password"]);
	if ($c < 6) {
		$msg = "Password must be at least 6 characters";
		goto show_html;
	}
	if ($c > 512) {
		$msg = "Password is too long, can't be longer than 512 characters";
		goto show_html;
	}

	if ($_POST["new_password"] !== $_POST["cnew_password"]) {
		$msg = "Password must be the same with the Retype Password";
		goto show_html;
	}

	$st = $pdo->prepare("UPDATE `users` SET `password` = ?, `updated_at` = ? WHERE `id` = ?");
	$st->execute([
		password_hash($_POST["new_password"], PASSWORD_BCRYPT),
		date("Y-m-d H:i:s"),
		$_SESSION["user"]
	]);
	?><script type="text/javascript">
		alert("Password has succcesfully been changed!");
		window.location = "profile.php";
	</script><?php
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
<div class="photo-cage">
	<img class="photo" />
</div>
<form method="POST" action="">
<table class="table-info">
	<tr><td align="left">Enter Old Password</td><td>:</td><td align="left"><input type="password" name="old_password" required="1"/></td></tr>
	<tr><td align="left">New Password</td><td>:</td><td align="left"><input type="password" name="new_password" required="1"/></td></tr>
	<tr><td align="left">Retype New Password</td><td>:</td><td align="left"><input type="password" name="cnew_password" required="1"/></td></td></tr>
	<tr><td colspan="3" align="center"><input type="submit" name="submit" value="Save"></td></tr>
</table>
</form>
