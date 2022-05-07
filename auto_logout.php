<?php

if (isset($_SESSION["login_at"])) {
	if ($_SESSION["login_at"] + 3600 < time()) {
		?><script type="text/javascript">
			alert("Session expired");
			window.location="logout.php";
		</script><?php
		exit;
	}
}
