<?php

import("lib.mongo.RMongo");

class MServer {
	private $_mongoName = null;
	private $_mongoHost = "127.0.0.1";
	private $_mongoPort = 27017;
	private $_mongoUser = "";
	private $_mongoPass = "";
	private $_mongoAuth = false;
	private $_mongoTimeout = 0;
	private $_mongoDb;
	private $_mongoOptions = array();
	private $_controlAuth = true;
	private $_controlUsers = array();
	private $_uiDefaultDb;//not be used yet
	private $_uiOnlyDbs;
	private $_uiHideDbs;
	private $_uiHideCollections;
	private $_uiHideSystemCollections = false;
	
	/**
	 * the server you are operating
	 * 
	 * @var MServer
	 */
	private static $_currentServer;
	private static $_servers = array();
	
	/**
	 * Mongo connection object
	 * 
	 * @var RMongo
	 */
	private $_mongo;
	
	public function __construct(array $config) {
		foreach ($config as $param => $value) {
			switch ($param) {
				case "mongo_name":
					$this->_mongoName = $value;
					break;
				case "mongo_host":
					$this->_mongoHost = $value;
					break;
				case "mongo_port":
					$this->_mongoPort = $value;
					break;
				case "mongo_user":
					$this->_mongoUser = $value;
					break;
				case "mongo_pass":
					$this->_mongoPass = $value;
					break;
				case "mongo_auth":
					$this->_mongoAuth = $value;
					break;
				case "mongo_timeout":
					$this->_mongoTimeout = $value;
					break;
				case "mongo_db":
					$this->_mongoDb = $value;
					break;
				case "mongo_options":
					$this->_mongoOptions = $value;
					break;
				case "control_auth":
					$this->_controlAuth = $value;
					break;
				case "control_users":
					$this->_controlUsers = $value;
					break;
				case "ui_default_db":
					$this->_uiDefaultDb = $value;
					break;
				case "ui_only_dbs":
					$this->_uiOnlyDbs = $value;
					break;
				case "ui_hide_dbs":
					$this->_uiHideDbs = $value;
					break;
				case "ui_hide_collections":
					$this->_uiHideCollections = $value;
					break;
				case "ui_hide_system_collections":
					$this->_uiHideSystemCollections = $value;
					break;
			}
		}
		if (empty($this->_mongoName)) {
			$this->_mongoName = $this->_mongoHost . ":" . $this->_mongoPort;
		}
	}
	
	public function mongoName() {
		return $this->_mongoName;
	}
	
	public function setMongoName($mongoName) {
		$this->_mongoName = $mongoName;
	}
	
	public function mongoAuth() {
		return $this->_mongoAuth;
	}
	
	public function setMongoAuth($mongoAuth) {
		$this->_mongoAuth = $mongoAuth;
	}
	
	public function mongoHost() {
		return $this->_mongoHost;
	}
	
	public function setMongoHost($mongoHost) {
		$this->_mongoHost = $mongoHost;
	}
	
	public function mongoPort() {
		return $this->_mongoPort;
	}
	
	public function setMongoPort($mongoPort) {
		$this->_mongoPort = $mongoPort;
	}
	
	public function mongoUser() {
		return $this->_mongoUser;
	}
	
	public function setMongoUser($mongoUser) {
		$this->_mongoUser = $mongoUser;
	}
	
	public function mongoPass() {
		return $this->_mongoPass;
	}
	
	public function setMongoPass($mongoPass) {
		$this->_mongoPass = $mongoPass;
	}
	
	public function mongoTimeout() {
		return $this->_mongoTimeout;
	}
	
	public function setMongoTimeout($timeout) {
		$this->_mongoTimeout = $timeout;
	}
	
	public function mongoDb() {
		return $this->_mongoDb;
	}
	
	public function setMongoDb($db) {
		$this->_mongoDb = $db;
	}
	
	public function controlAuth() {
		return $this->_controlAuth;
	}
	
	public function setControlAuth($controlAuth) {
		$this->_controlAuth = $controlAuth;
	}
	
	public function addControlUser($user, $pass) {
		$this->_controlUsers[$user] = $pass;
	}
	
	public function controlUsers() {
		return $this->_controlUsers;
	}
	
	public function setControlUsers(array $users) {
		$this->_controlUsers = $users;
	}
	
	public function uiOnlyDbs() {
		if (empty($this->_uiOnlyDbs)) {
			return array();
		}
		if (!is_array($this->_uiOnlyDbs)) {
			return preg_split("/\\s*,\\s*/", $this->_uiOnlyDbs);
		}
		return $this->_uiOnlyDbs;
	}
	
	public function setUIOnlyDbs($dbs) {
		$this->_uiOnlyDbs = $dbs;
	}
	
	public function uiHideDbs() {
		if (empty($this->_uiHideDbs)) {
			return array();
		}
		if (!is_array($this->_uiHideDbs)) {
			return preg_split("/\\s*,\\s*/", $this->_uiHideDbs);
		}
		return $this->_uiHideDbs;
	}
	
	public function setUIHideDbs($dbs) {
		$this->_uiHideDbs = $dbs;
	}
	
	public function uiHideCollections() {
		if (is_array($this->_uiHideCollections)) {
			return $this->_uiHideCollections;
		}
		return preg_split("/\\s*,\\s*/", $this->_uiHideCollections);
	}
	
	public function setUIHideCollections($collections) {
		$this->_uiHideCollections = $collections;
	}
	
	public function uiHideSystemCollections() {
		return $this->_uiHideSystemCollections;
	}
	
	public function setUIHideSystemCollections($bool) {
		$this->_uiHideSystemCollections = $bool;
	}
	
	public function auth($username, $password, $db = "admin") {
		if ($db === "") {
			if (!$this->_mongoAuth && $this->_mongoDb) {
				$db = $this->_mongoDb;
			}
			else {
				$db = "admin";
			}
		}
		$server = $this->_mongoHost . ":" . $this->_mongoPort;
		if (!$this->_mongoPort) {
			$server = $this->_mongoHost;
		}
		try {
			$options = $this->_mongoOptions;
			if ($this->_mongoAuth) {
				$options["username"] = $username;
				$options["password"] = $password;
			}
			$this->_mongo = new RMongo($server, $options);
			$this->_mongo->setSlaveOkay(true);
		}
		catch(Exception $e) {
			if (preg_match("/authenticate/i", $e->getMessage())) {
				return false;
			}
			echo "Unable to connect MongoDB, please check your configurations. MongoDB said:" . $e->getMessage() . ".";
			exit();
		}
		
		// changing timeout to the new value
		MongoCursor::$timeout = $this->_mongoTimeout;
		
		//auth by mongo
		if ($this->_mongoAuth) {
			// "authenticate" can only be used between 1.0.1 - 1.2.11
			if (RMongo::compareVersion("1.0.1") >= 0 && RMongo::compareVersion("1.2.11") < 0) {
				$dbs = $db;
				if (!is_array($dbs)) {
					$dbs = preg_split("/\\s*,\\s*/", $dbs);
				}
				foreach ($dbs as $db) {
					$ret = $this->_mongo->selectDb($db)->authenticate($username, $password);
					if (!$ret["ok"]) {
						return false;
					}
				}
			}
		}
		//auth by rock
		else if ($this->_controlAuth) {
			if (!isset($this->_controlUsers[$username]) || $this->_controlUsers[$username] != $password) {
				return false;
			}
			
			//authenticate
			if (!empty($this->_mongoUser)) {
				// "authenticate" can only be used between 1.0.1 - 1.2.11
				if (RMongo::compareVersion("1.0.1") >= 0 && RMongo::compareVersion("1.2.11") < 0) {
					return $this->_mongo
						->selectDB($db)
						->authenticate($this->_mongoUser, $this->_mongoPass);
				}
			}
		}
		else {
			//authenticate
			if (!empty($this->_mongoUser)) {
				// "authenticate" can only be used between 1.0.1 - 1.2.11
				if (RMongo::compareVersion("1.0.1") >= 0 && RMongo::compareVersion("1.2.11") < 0) {
					return $this->_mongo
						->selectDB($db)
						->authenticate($this->_mongoUser, $this->_mongoPass);
				}
			}
		}
		return true;
	}
	
	/**
	 * Current Mongo object
	 *
	 * @return Mongo
	 */
	public function mongo() {
		return $this->_mongo;
	}
	
	/**
	 * List databases on the server
	 *
	 * @return array
	 */
	public function listDbs() {
		$dbs = $this->_mongo->listDBs();
		if (!$dbs["ok"]) {
			$user = MUser::userInSession();
			
			$dbs = array(
				"databases" => array(),
				"totalSize" => 0,
				"ok" => 1
			);
			foreach ($user->dbs() as $db) {
				$dbs["databases"][] = array( "name" => $db, "empty" => false, "sizeOnDisk" => 0);
			}
		}
		
		//@todo: should we show user input databases only?
		
		$onlyDbs = $this->uiOnlyDbs();
		$hideDbs = $this->uiHideDbs();
		foreach ($dbs["databases"] as $index => $database) {
			$name = $database["name"];
			if (!empty($hideDbs) && in_array($name, $hideDbs)) {
				unset($dbs["databases"][$index]);
			}
			if (!empty($onlyDbs) && !in_array($name, $onlyDbs)) {
				unset($dbs["databases"][$index]);
			}
		}
		return $dbs;
	}
	
	/**
	 * Construct mongo server connection URI
	 *
	 * @return string
	 */
	public function uri() {
		$host = $this->_mongoHost . ":" . $this->_mongoPort;
		if ($this->_mongoAuth) {
			$user = MUser::userInSession();
			return $user->username() . ":" . $user->password() . "@" . $host;
		}
		if (empty($this->_mongoUser)) {
			return $host;
		}
		return $this->_mongoUser . ":" . $user->_mongoPass . "@" . $host;
	}
	
	/**
	 * Should we hide the collection
	 *
	 * @param unknown_type $collection collection name
	 * @return boolean
	 */
	public function shouldHideCollection($collection) {
		foreach ($this->uiHideCollections() as $v) {
			if (preg_match("/^" . $v . "$/", $collection)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @param unknown_type $hostIndex
	 * @return MServer
	 */
	public static function serverWithIndex($hostIndex) {
		global $MONGO;
		if (!isset($MONGO["servers"][$hostIndex])) {
			return null;
		}
		if (!isset(self::$_servers[$hostIndex])) {
			self::$_servers[$hostIndex] = new MServer($MONGO["servers"][$hostIndex]);
		}
		self::$_currentServer = self::$_servers[$hostIndex];
		return self::$_servers[$hostIndex];
	}	
	
	/**
	 * Enter description here ...
	 *
	 * @return MServer
	 */
	public static function currentServer() {
		return self::$_currentServer;
	}
}

?>