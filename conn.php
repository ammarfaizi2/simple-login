<?php

ini_set("display_errors", 1);

/**
 * @return \PDO
 * @throws \PDOException
 */
function getPDO(): \PDO
{
	$username = "root";
	$password = "...";
	$options = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];
	$dbname = "simple-login";
	$pdo = new \PDO("mysql:host=127.0.0.1;port=3306;dbname={$dbname}", $username, $password, $options);
	return $pdo;
}

return getPDO();
