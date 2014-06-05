<?php

class MDb {
	/**
	 * Execute a piece of javascript code
	 *
	 * @param MongoDB $db DB
	 * @param string $code javascript code
	 * @param array $params javascript function parameters
	 * @return array 
	 */
	static function exec(MongoDB $db, $code, array $params = array()) {
		$query = $db->execute($code, $params);
		if (!$query["ok"]) {
			exit("Execute failed:<font color=\"red\">" . $query["errmsg"] . "</font><br/>\n<pre>" . $code . "</pre>");
		}
		return $query["retval"];
	}
	
	/**
	 * List collections in a DB
	 * 
	 * @param MongoDB $db DB
	 * @return array<MongoCollection>
	 */
	static function listCollections(MongoDB $db) {
		$server = MServer::currentServer();
		
		$names = array();
		$query = $db->execute("function (){ return db.getCollectionNames(); }", array());
        if ($query["ok"]) {
            $names= $query["retval"];
        } 
        else{
            $colls = $db->listCollections(true);
            foreach($colls as $coll){
                $names[] = $coll->getName();
            }               
        }

		$ret = array();
		foreach ($names as $name) {
			if ($server->shouldHideCollection($name)) {
				continue;
			}
			if (preg_match("/^system\\./", $name)) {
				continue;
			}
			$ret[] = $name;
		}
		sort($ret);
		
		//system collections
		if (!$server->uiHideSystemCollections()) {
			foreach ($names as $name) {
				if ($server->shouldHideCollection($name)) {
					continue;
				}
				if (preg_match("/^system\\./", $name)) {
					$ret[] = $name;
				}
			}
		}
		$collections = array();
		foreach ($ret as $v) {
			if ($v === "") {//older MongoDB version (maybe before 1.7) allow empty collection name
				continue;
			}
			$collections[] = $db->selectCollection($v);
		}
		return $collections;
	}
}

?>