<?php

class RMongo {
	private static $_lastId;
	
	private $_mongo;
	
	public function __construct($server, array $options = array()) {
		if (class_exists("MongoClient")) {
			$this->_mongo = new MongoClient($server, $options);
		}
		else {
			$this->_mongo = new Mongo($server, $options);
		}
	}
	
	/**
	 * Closes this connection
	 * 
	 * @param boolean|string $connection Connection
	 * @return boolean
	 */
	public function close($connection) {
		return $this->_mongo->close($connection);
	}
	
	/**
	 * Connects to a database server
	 */
	public function connect() {
		return $this->_mongo->connect();
	}
	
	/**
	 * Drops a database
	 * 
	 * @param mixed $db The database to drop. Can be a MongoDB object or the name of the database
	 * @return array
	 */
	public function dropDB($db) {
		if (!is_object($db)) {
			$db = $this->selectDB($db);
		} 
		if (method_exists($db, "drop")) {
			return $db->drop();
		}
		if (method_exists($this->_mongo, "dropDB")) {
			$this->_mongo->dropDB($db);
		}
	}
	
	/**
	 * Force server to response error
	 */
	public function forceError() {
		if (method_exists($this->_mongo, "forceError")) {
			return $this->_mongo->forceError();
		}
		return false;
	}
	
	/**
	 * Gets a database
	 * 
	 * @param string $dbname The database name
	 * @return MongoDB
	 */
	public function __get($dbname) {
		return $this->_mongo->$dbname;
	}
	
	/**
	 * Updates status for all associated hosts
	 * 
	 * @return array
	 * @todo implement it under different versions
	 */
	public function getHosts() {
		if (method_exists($this->_mongo, "getHosts")) {
			return $this->_mongo->getHosts();
		}
		return array();
	}
	
	/**
	 * Get the read preference for this connection
	 * 
	 * @return array
	 * @todo implement it under different versions
	 */
	public function getReadPreference() {
		if (method_exists($this->_mongo, "getReadPreference")) {
			return $this->_mongo->getReadPreference();
		}
		return array();
	}
	
	/**
	 * Get last erro
	 *
	 * @return array
	 */
	public function lastError() {
		if (method_exists($this->_mongo, "lastError")) {
			return $this->_mongo->lastError();
		}
		return array();
	}
	
	/**
	 * Lists all of the databases available
	 * 
	 * @return array
	 */
	public function listDBs() {
		return $this->_mongo->listDBs();
	}
	
	/**
	 * Connect pair servers
	 * 
	 * @return boolean
	 */
	public function pairConnect() {
		if (method_exists($this->_mongo, "pairConnect")) {
			return $this->_mongo->pairConnect();
		} 
		return false;
	}
	
	/**
	 * Create pair persist connection
	 * 
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function pairPersistConnect($username = "" , $password = "") {
		if (method_exists($this->_mongo, "pairPersistConnect")) {
			return $this->_mongo->pairPersistConnect($username, $password);
		}
		return false;
	}
	
	/**
	 * Create persist connection
	 * 
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function persistConnect($username = "" , $password = "" ) {
		if (method_exists($this->_mongo, "persistConnect")) {
			return $this->_mongo->persistConnect($username, $password);
		}
		return false;
	}
	
	/**
	 * Get previous error
	 * 
	 * @return array
	 */
	public function prevError() {
		if (method_exists($this->_mongo, "prevError")) {
			return $this->_mongo->prevError();
		}
		return array();
	}
	
	/**
	 * Reset error
	 * 
	 * @return array
	 */
	public function resetError() {
		if (method_exists($this->_mongo, "resetError")) {
			return $this->_mongo->resetError();
		}
		return array();
	}
	
	/**
	 * Gets a database collection
	 * 
	 * @param string $db The database name
	 * @param string $collection The collection name
	 * @return MongoCollection
	 */
	public function selectCollection($db, $collection) {
		return $this->_mongo->selectCollection($db, $collection);
	}
	
	/**
	 * Gets a database
	 * 
	 * @param string $db The database name
	 * @return MongoDB
	 */
	public function selectDB($db) {
		return $this->_mongo->selectDB($db);
	}
	
	/**
	 * Set the read preference for this connection
	 * 
	 * @param int $readPreference The read preference mode: Mongo::RP_PRIMARY, Mongo::RP_PRIMARY_PREFERRED, Mongo::RP_SECONDARY, Mongo::RP_SECONDARY_PREFERRED, or Mongo::RP_NEAREST
	 * @param array $tags An array of zero or more tag sets, where each tag set is itself an array of criteria used to match tags on replica set members
	 * @return boolean
	 */
	public function setReadPreference($readPreference, array $tags = array()) {
		if (method_exists($this->_mongo, "setReadPreference")) {
			return $this->_mongo->setReadPreference($readPreference, $tags);
		}
		return false;
	}
	
	/**
	 * Change slaveOkay setting for this connection
	 *
	 * @param boolean $ok If reads should be sent to secondary members of a replica set for all possible queries using this Mongo instance
	 * @return boolean
	 */
	public function setSlaveOkay($ok) {
		if (method_exists($this->_mongo, "setSlaveOkay")) {
			return $this->_mongo->setSlaveOkay($ok);
		}
		return false;
	}
	
	/**
	 * String representation of this connection
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->_mongo->__toString();
	}
	
	/**
	 * Get mongo driver version
	 * 
	 * @return string
	 * @since 1.1.4
	 */
	public static function getVersion() {
		if (class_exists("MongoClient")) {
			return MongoClient::VERSION;
		}
		if (class_exists("Mongo")) {
			return Mongo::VERSION;
		}
		return "0";
	}
	
	/**
	 * Compare another version with current version
	 * 
	 * @param string $version Version to compare
	 * @return integer -1,0,1
	 * @since 1.1.4
	 */
	public static function compareVersion($version) {
		$currentVersion = self::getVersion();
		preg_match("/^[\\.\\d]+/", $currentVersion, $match);
		$number = $match[0];
		return version_compare($number, $version); 
	}
	
	static function setLastInsertId($lastId) {
		self::$_lastId = $lastId;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	static function lastInsertId() {
		return self::$_lastId;
	}
}

?>