<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/schema.guard.php";
require_once "get.model.php";

class Connection
{

	/*=============================================
	Database credentials from config/config.local.php
	=============================================*/

	static public function infoDatabase()
	{

		$infoDB = array(

			"host" => Config::get("db_host"),
			"database" => Config::get("db_name"),
			"user" => Config::requireSecret("db_user"),
			"pass" => Config::requireSecret("db_password"),
			"charset" => Config::get("db_charset")

		);

		return $infoDB;
	}

	/*=============================================
	Shared key the CMS sends in the Authorization header
	=============================================*/

	static public function apikey()
	{

		return Config::requireSecret("api_key");
	}

	/*=============================================
	Public access tables
	=============================================*/

	static public function publicAccess()
	{

		$tables = [""];

		return $tables;
	}

	/*=============================================
	Columns writable without a session token
	=============================================*/

	static public function unauthenticatedWriteAllowlist()
	{

		$allowlist = array(

			// Password recovery and e-mailed security code
			"admins" => array("scode_admin", "password_admin")

		);

		/*=============================================
		The builder installer seeds records through this same path
		=============================================*/

		if (Config::get("allow_installer")) {

			$allowlist["pages"]   = array("*");
			$allowlist["modules"] = array("*");
			$allowlist["folders"] = array("*");
			$allowlist["columns"] = array("*");
		}

		return $allowlist;
	}

	/*=============================================
	True only for writes the allowlist above permits
	=============================================*/

	static public function isUnauthenticatedWriteAllowed($table, $except, $columns)
	{

		$allowlist = Connection::unauthenticatedWriteAllowlist();

		if (!isset($allowlist[$table])) {

			return false;
		}

		$allowed = $allowlist[$table];

		if (in_array("*", $allowed, true)) {

			return true;
		}

		if (!in_array($except, $allowed, true)) {

			return false;
		}

		foreach ($columns as $column) {

			if (!in_array($column, $allowed, true)) {

				return false;
			}
		}

		return true;
	}

	/*=============================================
	Database connection, opened once and reused.

	Every model used to open its own, so a single API call cost two or three
	handshakes with MySQL. One request only ever needs one.
	=============================================*/

	private static ?PDO $link = null;

	static public function connect()
	{

		if (Connection::$link instanceof PDO) {

			return Connection::$link;
		}

		$infoDB = Connection::infoDatabase();

		try {

			$link = new PDO(
				"mysql:host=" . $infoDB["host"] . ";dbname=" . $infoDB["database"],
				$infoDB["user"],
				$infoDB["pass"]
			);

			$link->exec("set names " . $infoDB["charset"]);
		} catch (PDOException $e) {

			/*=============================================
			Log the cause, never expose it
			=============================================*/

			error_log("Database connection failed: " . $e->getMessage());

			/*=============================================
			Set the status before the debug branch, or a failure answers 200
			=============================================*/

			http_response_code(503);

			if (Config::isDebug()) {

				die("Error: " . $e->getMessage());
			}

			die(json_encode(array(
				"status" => 503,
				"results" => "Service unavailable"
			)));
		}

		return Connection::$link = $link;
	}

	/*=============================================
	Validate a table and its columns against the catalog
	=============================================*/

	static public function getColumnsData($table, $columns)
	{

		$known = SchemaGuard::columnsOf((string) $table);

		if ($known === []) {

			return null;
		}

		$requested = is_array($columns) ? $columns : [$columns];

		/*=============================================
		Global column selection
		=============================================*/

		if (isset($requested[0]) && $requested[0] === "*") {

			array_shift($requested);
		}

		/*=============================================
		Every requested column must exist
		=============================================*/

		foreach ($requested as $column) {

			if (!in_array($column, $known, true)) {

				return null;
			}
		}

		/*=============================================
		Return the catalog in the shape callers expect
		=============================================*/

		$validate = [];

		foreach ($known as $column) {

			$validate[] = (object) ["item" => $column];
		}

		return $validate;
	}

	/*=============================================
	Build the authentication token payload
	=============================================*/

	static public function jwt($id, $email)
	{

		$time = time();

		$token = array(

			"iat" =>  $time, // issued at
			"exp" => $time + (60 * 60 * 24), // expires in one day
			"data" => [

				"id" => $id,
				"email" => $email
			]

		);

		return $token;
	}

	/*=============================================
	Validate the security token
	=============================================*/

	static public function tokenValidate($token, $table, $suffix)
	{

		/*=============================================
		Find the user by token
		=============================================*/
		$user = GetModel::getDataFilter($table, "token_exp_" . $suffix, "token_" . $suffix, $token, null, null, null, null);

		if (!empty($user)) {

			/*=============================================
			Check the token has not expired
			=============================================*/

			$time = time();

			if ($time < $user[0]->{"token_exp_" . $suffix}) {

				return "ok";
			} else {

				return "expired";
			}
		} else {

			return "no-auth";
		}
	}
}
