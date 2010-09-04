<?php

class MDb {
	static function exec(MongoDB $db, $code, array $params = array()) {
		$query = $db->execute($code, $params);
		if (!$query["ok"]) {
			exit("Execute failed:<font color=\"red\">" . $query["errmsg"] . "</font><br/>\n<pre>" . $code . "</pre>");
		}
		return $query["retval"];
	}
	
	static function listCollections(MongoDB $db) {
		$names = self::exec($db, 'function (){ return db.getCollectionNames(); }');
		$ret = array();
		foreach ($names as $name) {
			if (!preg_match("/^system\./", $name)) {
				$ret[] = $name;
			}
		}
		sort($ret);
		$collections = array();
		foreach ($ret as $v) {
			$collections[] = $db->selectCollection($v);
		}
		return $collections;
	}
}

?>