<?php

if (!isset($pdo)) {
	header("Location: profile.php");
	exit;
}

const ALLOWED_PHOTO_EXTS = [
	"jpg", "jpeg", "png", "bmp", "gif"
];
const MAX_ALLOWED_SIZE = 1024*1024*4;

function edit_photo(PDO $pdo, string &$err): bool
{
	if (!isset($_FILES["photo"])) {
		$err = "";
		return false;
	}

	$ext = explode(".", $_FILES["photo"]["name"]);
	$ext = strtolower($ext[count($ext) - 1]);

	if (!in_array($ext, ALLOWED_PHOTO_EXTS)) {
		$err = sprintf("File extension isn't allowed (allowed extensions: %s, given: %s)",
				implode(", ", ALLOWED_PHOTO_EXTS), $ext);
		return false;
	}

	if ($_FILES["photo"]["size"] > MAX_ALLOWED_SIZE) {
		$err = sprintf("File size is too large, max allowed size is %d bytes",
				MAX_ALLOWED_SIZE);
		return false;
	}

	$md5sum   = md5_file($_FILES["photo"]["tmp_name"]);
	$sha1sum  = sha1_file($_FILES["photo"]["tmp_name"]);
	$filename = $md5sum . "_" . $sha1sum . "." . $ext;
	if (!move_uploaded_file($_FILES["photo"]["tmp_name"], __DIR__."/storage/files/{$filename}")) {
		$err = "Cannot move uploaded file";
		return false;
	}

	$md5sum  = hex2bin($md5sum);
	$sha1sum = hex2bin($sha1sum);

	$pdo->beginTransaction();
	$st = $pdo->prepare("SELECT `id` FROM `files` WHERE `md5_sum` = ? AND `sha1_sum` = ? LIMIT 1");
	$st->execute([$md5sum, $sha1sum]);
	$u = $st->fetch(PDO::FETCH_NUM);

	if (!$u) {
		$st = $pdo->prepare("INSERT INTO `files` (`md5_sum`, `sha1_sum`, `size`, `ext`, `description`, `created_at`) VALUES (?, ?, ?, ?, NULL, ?);");
		$ret = $st->execute([
			$md5sum,
			$sha1sum,
			$_FILES["photo"]["size"],
			$ext,
			date("Y-m-d H:i:s")
		]);
		if (!$ret) {
			$err = "Failed to insert the new file to database";
			goto out_rollback;
		}
		$fileId = $pdo->lastInsertId();
	} else {
		$fileId = $u[0];
	}

	$st = $pdo->prepare("UPDATE `users` SET `photo` = ? WHERE `id` = ? LIMIT 1");
	if (!$st->execute([$fileId, $_SESSION["user"]])) {
		$err = "Failed to update user photo";
		goto out_rollback;
	}
	$pdo->commit();
	return true;

out_rollback:
	$pdo->rollback();
	return false;
}

$err = "";
edit_photo($pdo, $err);

show_html:
?>
<a href="profile.php"><h3>Back to Profile</h3></a>
<?php if ($err !== ""): ?>
<h3>Error: <?php echo htmlspecialchars($err, ENT_QUOTES, "UTF-8"); ?></h3>
<?php endif ?>
<form method="POST" action="" enctype="multipart/form-data">
	<div>
		<input type="file" name="photo"/>
	</div>
	<div>
		<input type="submit" name="submit" value="Upload"/>
	</div>
</form>
