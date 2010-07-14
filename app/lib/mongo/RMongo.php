<?php

class RMongo extends Mongo  {
	private static $_lastId;
	
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