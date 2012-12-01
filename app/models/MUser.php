<?php

class MUser {
	private $_username;
	private $_password;
	private $_hostIndex;
	private $_db;
	private $_timeout;
	
	public function __construct() {
		
	}
	
	public function setUsername($username) {
		$this->_username = $username;
	}
	
	public function username() {
		return $this->_username;
	}
	
	public function setPassword($password) {
		$this->_password = $password;
	}
	
	public function password() {
		return $this->_password;
	}
	
	public function setHostIndex($hostIndex) {
		$this->_hostIndex = $hostIndex;
	}
	
	public function hostIndex() {
		return $this->_hostIndex;
	}
	
	public function setDb($db) {
		$this->_db = $db;
	}
	
	public function defaultDb() {
		$dbs = $this->dbs();
		return $dbs[0]; 
	}
	
	public function dbs() {
		if (empty($this->_db)) {
			import("@.MServer");
			$server = MServer::serverWithIndex($this->_hostIndex);
			$mongoDb = "admin";
			if (!$server->mongoAuth()) {
				$authDb = MServer::serverWithIndex($this->_hostIndex)->mongoDb();
				if ($authDb) {
					$mongoDb = $authDb;
				}
			}
			return array($mongoDb);
		}
		if (is_array($this->_db)) {
			return array_values($this->_db);
		}
		return preg_split("/\\s*,\\s*/", $this->_db);
	}
	
	public function setTimeout($timeout) {
		$this->_timeout = $timeout;
	}
	
	/**
	 * Validate User
	 *
	 * @return boolean
	 */
	public function validate() {
		import("@.MServer");
		$server = MServer::serverWithIndex($this->_hostIndex);
		if (empty($server)) {
			return false;
		}
		return $server->auth($this->_username, $this->_password, $this->_db);
	}
	
	public function servers() {
		global $MONGO;
		return $MONGO["servers"];
	}
	
	public function changeHost($hostIndex) {
		$_SESSION["login"]["index"] = $hostIndex;
	}
	
	public static function login($username, $password, $hostIndex, $db, $timeout) {
		$_SESSION["login"] = array(
			"username" => $username,
			"password" => $password,
			"index" => $hostIndex,
			"db" => $db
		);
		setcookie(session_name(), session_id(), time() + $timeout);
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return MUser
	 */
	public static function userInSession() {
		if (array_key_exists("login", $_SESSION) 
			&& array_key_exists("username", $_SESSION["login"])
			&& array_key_exists("password", $_SESSION["login"])
			&& array_key_exists("index", $_SESSION["login"])
			&& array_key_exists("db", $_SESSION["login"])) {
			
			$user = new MUser();
			$user->setUsername($_SESSION["login"]["username"]);
			$user->setPassword($_SESSION["login"]["password"]);
			$user->setHostIndex($_SESSION["login"]["index"]);
			$user->setDb($_SESSION["login"]["db"]);
			return $user;	
		}
		return null;
	}
}

?>