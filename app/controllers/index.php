<?php
session_start();

import("lib.ext.RExtController");
class IndexController extends RExtController {
	private $_servers;
	private $_server;
	/**
	 * Enter description here...
	 *
	 * @var Mongo
	 */
	private $_mongo;
	
	function onBefore() {
		global $MONGO;
		$this->_servers = $MONGO["servers"];
		
		if ($this->action() != "login") {
			//是否已经登录
			if (!isset($_SESSION["login"])) {
				$this->redirect("login");
			}
			$server = $this->_servers[$_SESSION["login"]["host"]];
			$this->_server = $server;
			$link = "mongodb://";
			if ($server["username"]) {
				$link .= $server["username"] . ":" . $server["password"] . "@";
			}
			$link .= $server["host"] . ":" . $server["port"];
			$this->_mongo = new Mongo($link);
		}
		
		if ($this->action() != "admin") {
			$this->display("header");
		}
	}
	
	function onAfter() {
		if ($this->action() != "admin") {
			$this->display("footer");
		}
	}
	
	function doIndex() {
		$this->redirect("admin");
	}
	
	function doLogin() {
		$this->servers = $this->_servers;
		$this->username = x("username");
		
		if ($this->isPost()) {
			$index = xi("host");
			if (!isset($this->_servers[$index])) {
				$this->message = "invalid server";
				$this->display();
				return;
			}
			$server = $this->_servers[$index];
			if (!isset($server["admins"][$this->username])) {
				$this->message = "invalid username";
				$this->display();
				return;
			}
	
			$password = $server["admins"][$this->username];
			if ($password != x("password")) {
				$this->message = "wrong password";
				$this->display();
				return;
			}
			
			//记住登录
			$_SESSION["login"] = array(
				"username" => $this->username,
				"host" => $index
			);
		
			//跳转
			$this->redirect("admin");
		}
		else {
			$this->display();
		}
	}
	
	function doLogout() {
		session_destroy();
		$this->redirect("login");
	}
	
	function doAdmin() {
		$this->topUrl = $this->path("top");
		$this->leftUrl = $this->path("dbs");
		$this->rightUrl = $this->path("listDbs");
		
		$this->display();
	}
	
	function doTop() {
		$this->logoutUrl = $this->path("logout");
		$this->server = $this->_server;
		$this->display();
	}
	
	function doAbout() {
		$this->display();
	}
	
	function doDbs() {
		$dbs = $this->_mongo->listDBs();
		$this->dbs = array_values(rock_array_sort($dbs["databases"], "name"));
		$this->baseUrl = $this->path("dbs");
		$this->tableUrl = $this->path("collection");
		
		$db = x("db");
		if (!empty($this->dbs) && !$db) {
			$db = $this->dbs[0]["name"];
			x("db", $db);
		}
		
		$this->tables = array();
		if ($db) {
			$tables = $this->_mongo->selectDB($db)->listCollections();
			foreach ($tables as $table) {
				$this->tables[] = $table->getName();	
			}
			sort($this->tables);
		}
		
		$this->display();
	}
	
	function doListDbs() {
		$dbs = $this->_mongo->listDBs();
		$this->dbs = $dbs["databases"];
		$this->display();
	}
	
	function doCollection() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		//字段
		$fields = xn("field");
		$orders = xn("order");
		if (empty($fields)) {
			$fields = array(
				"_id", "", "", ""
			);
			$orders = array(
				"desc", "asc", "asc", "asc"
			);
			x("field", $fields);
			x("order", $orders);
		}
		
		
		//条件
		$criteria = xn("criteria");
		if (empty($criteria)) {
			$criteria = array();
		}
		else {
			$row = null;
			eval("\$row={$criteria};");
			if (!is_array($row)) {
				$this->message = "Criteria must be an array.";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		x("criteria", var_export($criteria, true));
		
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//排序
		foreach ($fields as $index => $field) {
			if (!empty($field)) {
				if ($orders[$index] == "asc") {
					$query->asc($field);
				}
				else {
					$query->desc($field);
				}
			}
		}
		
		//命令
		$command = x("command");
		if (!$command) {
			$command = "findAll";
			x("command", $command);
		}
		$limit = xi("limit");
		if ($limit > 0) {
			$query->limit($limit);
		}
		$count = ($limit > 0 && $command == "findAll") ? $query->count(true) : $query->count();
		
		switch ($command) {
			case "findAll":
				break;
			case "remove":
				$microtime = microtime(true);	
				$query->delete();
				$this->cost = microtime(true) - $microtime;
				break;
			case "modify":
				$microtime = microtime(true);	
				$row = null;
				$newobj = xn("newobj");
				eval("\$row={$newobj};");
				if (is_array($row)) {
					$query->upsert($row);
				}
				$this->cost = microtime(true) - $microtime;
				break;
		}
		
		if ($command != "findAll") {
			$this->count = $count;
			$this->display();
			return;
		}

		//分页
		import("lib.page.RPageStyle1");
		$page = new RPageStyle1();
		$page->setTotal($count);
		$page->setSize(10);
		$page->setAutoQuery();

		$this->page = $page;
		$query->offset($page->offset());
		if ($limit > 0) {
			$query->limit(min($limit, $page->size()));
		}
		else {
			$query->limit($page->size());
		}
		
		$microtime = microtime(true);	
		$this->rows = $query->findAll();
		$this->cost = microtime(true) - $microtime;
		foreach ($this->rows as $index => $row) {
			$row["data"] = $this->_highlight($row);
			$this->rows[$index] = $row;
		}
		
		$this->display();
	}
	
	function doDeleteRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->id(x("id"))->delete();
		
		$this->redirectUrl(xn("uri"));
	}
	
	function doCreateRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$id = x("id");
		
		import("lib.mongo.RQuery");		
		
		//如果是复制
		if (!$this->isPost() && $id) {
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$row = $query->id($id)->findOne();
			if (!empty($row)) {
				unset($row["_id"]);
				x("data", var_export($row, true));
			}
		}
		
		if ($this->isPost()) {
			$data = xn("data") . ";";
			$data = str_replace(array(
				"%{created_at}"
			), time(), $data);
			$row = null;
			if (@eval("\$row=" . $data . ";") === false || !is_array($row)) {
				$this->message = "Only array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$ret = $query->insert($row);
			$this->redirect("collection", array( 
				"db" => $this->db,
				"collection" => $this->collection 
			));
		}
		
		$this->display();
	}
	
	function doModifyRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$this->row = $query->id(x("id"))->findOne();
		if (empty($this->row)) {
			$this->message = "Record is not found.";
			$this->display();
			return;
		}
		$this->data = $this->row;
		unset($this->data["_id"]);
		$this->data = var_export($this->data, true);
	
		if ($this->isPost()) {
			$this->data = xn("data");
			$row = null;
			if (@eval("\$row=" . $this->data . ";") === false || !is_array($row)) {
				$this->message = "Only array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$obj = $query->id($this->row["_id"])->find();
			$oldAttrs = $obj->attrs();
			$obj->setAttrs($row);
			foreach ($oldAttrs as $oldAttr => $oldValue) {
				if ($oldAttr == "_id") {
					continue;
				}
				if (!isset($row[$oldAttr])) {
					$obj->remove($oldAttr);
				}
			}
			$obj->save();
			
			$this->message = "Updated successfully.";
		}
		
		$this->display();
	}
	
	function doClearRows() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->delete();
		
		$this->redirect("collection", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	function doRemoveCollection() {
		$this->db = x("db");
		$this->collection = x("collection");
		$db = new MongoDB($this->_mongo, $this->db);
		$db->dropCollection($this->collection);
		$this->display();
	}
	
	function doNewCollection() {
		$this->db = xn("db");
		$this->name = x("name");
		$this->isCapped = xi("is_capped");
		$this->size = xi("size");
		$this->max = xi("max");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$db->createCollection($this->name, $this->isCapped, $this->size, $this->max);
			$this->message = "New collection is created.";
		}
		
		$this->display();
	}
	
	function doCollectionIndexes() {
		$this->db = x("db");
		$this->collection = x("collection");
		$collection = $this->_mongo->selectCollection(new MongoDB($this->_mongo, $this->db), $this->collection);
		$this->indexes = $collection->getIndexInfo();
		foreach ($this->indexes as $_index => $index) {
			$index["data"] = $this->_highlight($index["key"]);
			$this->indexes[$_index] = $index;
		}
		$this->display();
	}
	
	function doDeleteIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$db = new MongoDB($this->_mongo, $this->db);
		$collection = $this->_mongo->selectCollection($db, $this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == trim(xn("index"))) {
				$ret = $db->command(array("deleteIndexes" => $collection->getName(), "index" => $index["name"]));
				break;
			}
		}
		
		$this->redirect("collectionIndexes", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	function doCreateIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$collection = $this->_mongo->selectCollection($db, $this->collection);
			
			$fields = xn("field");
			if (!is_array($fields)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			$orders = xn("order");
			$attrs = array();
			foreach ($fields as $index => $field) {
				$field = trim($field);
				if (!empty($field)) {
					$attrs[$field] = ($orders[$index] == "asc") ? 1 : -1;
				}
			}
			if (empty($attrs)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			
			//是否unique
			$options = array();
			if (x("is_unique")) {
				$options["unique"] = 1;
				if (x("drop_duplicate")) {
					$options["dropDups"] = 1;
				}
			}
			$options["background"] = 1;
			$options["safe"] = 1;
			
			//名称
			$name = trim(xn("name"));
			if (!empty($name)) {
				$options["name"] = $name;
			}
			
			$collection->ensureIndex($attrs, $options);
			
			$this->redirect("collectionIndexes", array(
				"db" => $this->db,
				"collection" => $this->collection
			));
		}
		
		$this->display();
	}
	
	function doCollectionStats() {
		$this->db = x("db");
		$this->collection = x("collection");
		$this->stats = array();
		
		$db = new MongoDB($this->_mongo, $this->db);
		$ret = $db->execute("db.{$this->collection}.stats()");
		if ($ret["ok"]) {
			$this->stats = $ret["retval"];
		}
		
		$this->display();
	}
	
	private function _highlight($var) {
		$string = highlight_string("<?php " . var_export($var, true), true);
		$string = preg_replace("/" . preg_quote('<span style="color: #0000BB">&lt;?php&nbsp;</span>', "/") . "/", '', $string, 1);
		return $string;
	}
}


/**
 * 将一个多维数组按照一个键的值排序
 *
 * @param array $array 数组
 * @param mixed $key string|array
 * @param boolean $asc 是否正排序
 * @return array
 */
function rock_array_sort(array $array, $key = null, $asc = true) {
	if (empty($array)) {
		return $array;
	}
	if (empty($key)) {
		$asc ? asort($array) : arsort($array);
	}
	else {
		$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil] = $key;
		uasort($array, 
			$asc ? create_function('$p1,$p2', '$key=$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1>$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
			:
			create_function('$p1,$p2', '$key=$GLOBALS["rock_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1<$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
		);
		unset($GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil]);
	}	
	return $array;
}

?>