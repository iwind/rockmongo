<?php

import("classes.BaseController");

/**
 * collection controller
 * 
 * You should always input these parameters:
 * - db
 * - collection
 * to call the actions.
 * 
 * @author iwind
 *
 */
class CollectionController extends BaseController {
	/**
	 * DB Name
	 * 
	 * @var string
	 */
	public $db;
	
	/**
	 * Collection Name
	 * 
	 * @var string
	 */
	public $collection;
	
	/**
	 * DB instance
	 *
	 * @var MongoDB
	 */
	protected $_mongodb;
	
	public function onBefore() {
		parent::onBefore();
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->_mongodb = $this->_mongo->selectDB($this->db);
	}	
	
	/**
	 * load single record
	 *
	 */
	public function doRecord() {
		$id = rock_real_id(xn("id"));
		$format = xn("format");
		
		$queryFields = x("query_fields");
		$fields = array();
		if (!empty($queryFields)) {
			foreach ($queryFields as $queryField) {
				$fields[$queryField] = 1;
			}
		}
		
		$row = $this->_mongodb->selectCollection($this->collection)->findOne(array( "_id" => $id ), $fields);
		if (empty($row)) {
			$this->_outputJson(array("code" => 300, "message" => "The record has been removed."));
		}
		$exporter = new VarExportor($this->_mongodb, $row);
		$data = $exporter->export($format);
		$html = $this->_highlight($row, $format, true);
		$this->_outputJson(array("code" => 200, "data" => $data, "html" => $html ));
	}
	
	/**
	 * switch format between array and json
	 */
	public function doSwitchFormat() {
		$data = xn("data");
		$format = x("format");
		
		$ret = null;
		if ($format == "json") {//to json
			$eval = new VarEval($data, "array", $this->_mongodb);
			$array = $eval->execute();
			$exportor = new VarExportor($this->_mongodb, $array);
			$ret = json_unicode_to_utf8($exportor->export(MONGO_EXPORT_JSON));
		}
		else if ($format == "array") {//to array
			$eval = new VarEval($data, "json", $this->_mongodb);
			$array = $eval->execute();
			$exportor = new VarExportor($this->_mongodb, $array);
			$ret = $exportor->export(MONGO_EXPORT_PHP);
		}
		$this->_outputJson(array("code" => 200, "data" => $ret));
	}
	
	/** show one collection **/
	public function doIndex() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		//selected format last time
		$this->last_format = rock_cookie("rock_format", "json");
		
		//write query to log
		$params = xn();
		if ($this->_logQuery && count($params) > 3) {//not only "action", "db" and "collection"
			$logDir = dirname(__ROOT__) . DS . "logs";
			if (is_writable($logDir)) {
				$logFile = $this->_logFile($this->db, $this->collection);
				$fp = null;
				if (!is_file($logFile)) {	
					$fp = fopen($logFile, "a+");
					fwrite($fp, '<?php exit("Permission Denied"); ?>' . "\n");
				}
				else {
					$fp = fopen($logFile, "a+");
				}
				fwrite($fp, date("Y-m-d H:i:s") . "\n" . var_export($params, true) . "\n================\n");
				fclose($fp);
			}
		}
		
		//information
		$db = $this->_mongo->selectDB($this->db);
		$info = MCollection::info($db, $this->collection);
		$this->canAddField = !$info["capped"];
		
		//field and sort
		$fields = xn("field");
		$orders = xn("order");
		if (empty($fields)) {
			$fields = array(
				($info["capped"]) ? "\$natural": "_id", "", "", ""
			);
			$orders = array(
				"desc", "asc", "asc", "asc"
			);
			x("field", $fields);
			x("order", $orders);
		}
		
		//format
		$format = x("format");
		if (!$format) {
			$format = $this->last_format;
			x("format", $format);
		}	

		//remember last format choice
		$this->last_format = $format;
		$this->_rememberFormat($format);
		
		//read fields from collection
		import("models.MCollection");
		$this->nativeFields = MCollection::fields($db, $this->collection);
		$this->queryFields = x("query_fields");
		if (!is_array($this->queryFields)) {
			$this->queryFields = array();
		}
		
		$this->indexFields = $db->selectCollection($this->collection)->getIndexInfo();
		$this->recordsCount = $db->selectCollection($this->collection)->count();
		foreach ($this->indexFields as $index => $indexField) {
			$this->indexFields[$index]["keystring"] = $this->_encodeJson($indexField["key"]);
		}
		
		$this->queryHints = x("query_hints");
		if (!is_array($this->queryHints)) {
			$this->queryHints = array();
		}
		
		//new obj in modification
		$newobj = trim(xn("newobj"));
		if (!$newobj) {
			if ($format == "array") {
				x("newobj", 'array(
	\'$set\' => array (
		//your attributes
	)
)');
			}
			else {
				x("newobj", '{
	\'$set\': {
		//your attributes
	}
}');
			}
		}
		
		//conditions
		$native = xn("criteria");
		$criteria = $native;
		if (empty($criteria)) {
			$criteria = array();
			if ($format == "array") {
				$native = "array(\n\t\n)";
			}
			else if ($format == "json") {
				$native = '{
					
}';				
			}
			x("pagesize", 10);
		}
		else {
			$row = null;
			$eval = new VarEval($criteria, $format, $db);
			$row = $eval->execute();
			if (!is_array($row)) {
				$this->message = "Criteria must be a valid " . (($format == "json") ? "JSON object" : "array");
				$this->jsonLink = "#";
				$this->arrayLink = "#";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		
		//remember criteria in cookie (may be replaced by query history some day)
		//setcookie("criteria_" . $this->db . "__" . $this->collection . "__" . $format, $native, time() + );
		x("criteria", $native);
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//sort
		$realOrderedFields = array();
		$realOrderedOrders = array();
		foreach ($fields as $index => $field) {
			if (!empty($field)) {
				$realOrderedFields[] = $field;
				if ($orders[$index] == "asc") {
					$realOrderedOrders[] = "asc";
					$query->asc($field);
				}
				else {
					$realOrderedOrders[] = "desc";
					$query->desc($field);
				}
			}
		}
		x("field", $realOrderedFields);
		x("order", $realOrderedOrders);
		
		//command
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
				if (!empty($this->queryFields)) {
					$query->result($this->queryFields);
				}
				if (!empty($this->queryHints)) {
					foreach ($this->indexFields as $index) {
						if (in_array($index["name"], $this->queryHints)) {
							$query->hint($index["key"]);
						}
					}
				}
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
				$eval = new VarEval($newobj, $format, $db);
				$row = $eval->execute();
				if (is_array($row)) {
					$query->upsert($row);
				}
				$this->cost = microtime(true) - $microtime;
				break;
		}
		
		//construct links
		if ($format == "json") {
			$params = xn();
			unset($params["newobj"]);
			$exportor = new VarExportor($db, $criteria);
			$params["format"] = "array";
			$params["criteria"] = $exportor->export();
			$this->arrayLink = $this->path($this->action(), $params);
			$params = xn();
			unset($params["newobj"]);
			$params["format"] = "json";
			$this->jsonLink = $this->path($this->action(), $params);	
		}
		else if ($format == "array") {
			$params = xn();
			unset($params["newobj"]);
			$params["format"] = "array";
			$this->arrayLink = $this->path($this->action(), $params);
			$params = xn();
			unset($params["newobj"]);
			$params["format"] = "json";
			if (empty($criteria)) {
				$params["criteria"] = "{\n	\n}";
			}
			else {
				$exportor = new VarExportor($db, $criteria);
				$params["criteria"] = $exportor->export(MONGO_EXPORT_JSON);
			}
			$this->jsonLink = $this->path($this->action(), $params);	
		}
		
		if ($command != "findAll") {
			$this->count = $count;
			$this->display();
			return;
		}
		
		//pagination
		$pagesize = xi("pagesize");
		if ($pagesize < 1) {
			$pagesize = 10;
		}
		import("lib.page.RPageStyle1");
		$page = new RPageStyle1();
		$page->setTotal($count);
		$page->setSize($pagesize);
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
		$this->rows = $query->findAll(true);
		$this->cost = microtime(true) - $microtime;
		foreach ($this->rows as $index => $row) {
			$native = $row;
			$exportor = new VarExportor($query->db(), $native);
			$row["text"] = $exportor->export($format);
			$row["data"] = $this->_highlight($native, $format, true);
			$row["can_delete"] = (isset($row["_id"]) && !$info["capped"]);
			$row["can_modify"] = isset($row["_id"]);
			$row["can_duplicate"] = isset($row["_id"]);
			$row["can_add_field"] = (isset($row["_id"]) && !$info["capped"]);
			$row["can_refresh"] = isset($row["_id"]);
			$this->rows[$index] = $row;
		}
		
		$this->display();
	}
	
	/**
	 * output query history
	 *
	 */
	public function doQueryHistory() {
		ob_clean();
		
		$logs = array();
		$criterias = array();
		if ($this->_logQuery) {
			$logFile = $this->_logFile(xn("db"), xn("collection"));
			$this->logs = array();
			if (is_file($logFile)) {
				$size = 10240;
				$fp = fopen($logFile, "r");
				fseek($fp, -$size, SEEK_END);
				$text = fread($fp, $size);
				fclose($fp);
				
				preg_match_all("/(\\d+\\-\\d+\\-\\d+\\s+\\d+:\\d+:\\d+)\n(.+)(={10,})/sU", $text, $match);
				
				foreach ($match[1] as $k => $time) {
					$eval = new VarEval($match[2][$k]);
					$params = $eval->execute();
					if (!in_array($params["criteria"], $criterias)) {
						$logs[] = array(
							"time" => $time,
							"params" => $params,
							"query" => http_build_query($params)
						);
						$criterias[] = $params["criteria"];
					}
				}
			}
		}
		$this->logs = array_slice(array_reverse($logs), 0, 10);
		$this->display();
		exit();
	}
	
	
	/** explain query **/
	public function doExplainQuery() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		//field and sort
		$fields = xn("field");
		$orders = xn("order");
		$format = xn("format");
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
		
		
		//conditions
		$native = xn("criteria");
		$criteria = $native;
		if (empty($criteria)) {
			$criteria = array();
			$native = "array(\n\t\n)";
		}
		else {
			$row = null;
			$eval = new VarEval($criteria, $format, $this->_mongo->selectDB($this->db));
			$row = $eval->execute();
			if (!is_array($row)) {
				$this->error = "To explain a query, criteria must be an array.";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		x("criteria", $native);

		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//sort
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
		
		//command
		$command = x("command");
		if (!$command) {
			$command = "findAll";
			x("command", $command);
		}
		
		$queryHints = x("query_hints");
		if (!empty($queryHints)) {
			$db = $this->_mongo->selectDB($this->db);
			$indexes = $db->selectCollection($this->collection)->getIndexInfo();
			foreach ($indexes as $index) {
				if (in_array($index["name"], $queryHints)) {
					$query->hint($index["key"]);
				}
			}
		}
		
		$cursor = $query->cursor();
		$this->ret = $this->_highlight($cursor->explain(), "json");
		$this->display();
	}
	
	/** delete on row **/
	public function doDeleteRow() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$ret = $query->id(rock_real_id(x("id")))->delete();
		
		$this->redirectUrl(xn("uri"), true);
	}
	
	/** create row **/
	public function doCreateRow() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		$id = rock_real_id(x("id"));
		
		import("lib.mongo.RQuery");	
		
		//selected format last time
		$this->last_format = rock_cookie("rock_format", "json");
		
		//if is duplicating ...
		if (!$this->isPost() && $id) {
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$row = $query->id($id)->findOne();
			if (!empty($row)) {
				unset($row["_id"]);
				import("classes.VarExportor");
				$export = new VarExportor($query->db(), $row);
				x("data", $export->export($this->last_format));
			}
		}
		
		//initialize
		if (!$this->isPost() && !x("data")) {
			x("data", ($this->last_format == "json") ? "{\n\t\n}" : "array(\n\n)");
		}
		
		//try to deal with data
		if ($this->isPost()) {
			$format = x("format");
			$this->last_format = $format;
			
			$data = xn("data");
			$data = str_replace(array(
				"%{created_at}"
			), time(), $data);
			
			$row = null;
			$eval = new VarEval($data, $format, $this->_mongo->selectDb($this->db));
			$row = $eval->execute();
			if ($row === false || !is_array($row)) {
				$this->error = "Data must be a valid {$format}.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			try {
				$ret = $query->insert($row, true);
			} catch (Exception $e) {
				$this->error = $e->getMessage();
				$this->display();
				return;
			}
			
			//remember format choice
			$this->_rememberFormat($format);
			
			$this->redirect("collection.index", array( 
				"db" => $this->db,
				"collection" => $this->collection 
			), true);
		}
		
		$this->display();
	}
	
	/** modify one row **/
	public function doModifyRow() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$id = rock_real_id(xn("id"));
		
		//selected format last time
		$this->last_format = rock_cookie("rock_format", "json");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$this->row = $query->id($id)->findOne();
		if (empty($this->row)) {
			$this->error = "Record is not found.";
			$this->display();
			return;
		}
		$this->data = $this->row;
		unset($this->data["_id"]);
		import("classes.VarExportor");
		$export = new VarExportor($query->db(), $this->data);
		$this->data = $export->export($this->last_format);
		if ($this->last_format == "json") {
			$this->data = json_unicode_to_utf8($this->data);
		}
	
		if ($this->isPost()) {
			$this->data = xn("data");
			$format = x("format");
			$this->last_format = $format;
			
			$row = null;
			$eval = new VarEval($this->data, $format, $this->_mongo->selectDb($this->db));
			$row = $eval->execute();
			if ($row === false || !is_array($row)) {
				$this->error = "Only valid {$format} is accepted.";
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
				if (!array_key_exists($oldAttr, $row)) {
					$obj->remove($oldAttr);
				}
			}
			try {
				$obj->save();
			} catch (Exception $e) {
				$this->error = $e->getMessage();
				$this->display();
				return;
			}
			
			//remember format choice
			$this->_rememberFormat($format);
			
			$this->message = "Updated successfully.";
		}
		
		$this->display();
	}
	
	/** download file in GridFS **/
	public function doDownloadFile() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->id = xn("id");
		$db = $this->_mongo->selectDB($this->db);
		$prefix = substr($this->collection, 0, strrpos($this->collection, "."));
		$file = $db->getGridFS($prefix)->findOne(array("_id" => rock_real_id($this->id)));
		$fileinfo = pathinfo($file->getFilename());
		$extension = strtolower($fileinfo["extension"]);
		import("lib.mime.types", false);
		ob_end_clean();
		if (isset($mime_types[$extension])) {
			header("Content-Type:" . $mime_types[$extension]);
		}
		else {
			header("Content-Type:text/plain");
		}
		header("Content-Disposition: attachment; filename=" . $fileinfo["basename"]);
		header("Content-Length:" . $file->getSize());
		echo $file->getBytes();
		exit;
	}
	
	/** clear rows in collection **/
	public function doClearRows() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->delete();
		
		echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
		
		$this->redirect("collection.index", array(
			"db" => $this->db,
			"collection" => $this->collection
		), true);
	}
	
	/** drop collection **/
	public function doRemoveCollection() {
		$this->db = x("db");
		$this->collection = xn("collection");
		$db = $this->_mongo->selectDB($this->db);
		$db->dropCollection($this->collection);
		$this->display();
	}
	
	/** list collection indexes **/
	public function doCollectionIndexes() {
		$this->db = x("db");
		$this->collection = xn("collection");
		$collection = $this->_mongo->selectCollection($this->_mongo->selectDB($this->db), $this->collection);
		$this->indexes = $collection->getIndexInfo();
		foreach ($this->indexes as $_index => $index) {
			$index["data"] = $this->_highlight($index["key"], "json");
			$this->indexes[$_index] = $index;
		}
		$this->display();
	}
	
	/** drop a collection index **/
	public function doDeleteIndex() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		$db = $this->_mongo->selectDB($this->db);
		$collection = $this->_mongo->selectCollection($db, $this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == trim(xn("index"))) {
				$ret = $db->command(array("deleteIndexes" => $collection->getName(), "index" => $index["name"]));
				break;
			}
		}
		
		$this->redirect("collection.collectionIndexes", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	/** create a collection index **/
	public function doCreateIndex() {
		$this->db = x("db");
		$this->collection = xn("collection");
		$this->nativeFields = MCollection::fields($this->_mongo->selectDB($this->db), $this->collection);
		if ($this->isPost()) {
			$db = $this->_mongo->selectDB($this->db);
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
			
			//if is unique
			$options = array();
			if (x("is_unique")) {
				$options["unique"] = 1;
				if (x("drop_duplicate")) {
					$options["dropDups"] = 1;
				}
			}
			$options["background"] = 1;
			$options["safe"] = 1;
			
			//name
			$name = trim(xn("name"));
			if (!empty($name)) {
				$options["name"] = $name;
			}
			$collection->ensureIndex($attrs, $options);
			
			$this->redirect("collection.collectionIndexes", array(
				"db" => $this->db,
				"collection" => $this->collection
			));
		}
		
		$this->display();
	}
	
	/** collection statistics **/
	public function doCollectionStats() {
		$this->db = x("db");
		$this->collection = xn("collection");
		$this->stats = array();
		
		$db = $this->_mongo->selectDB($this->db);
		$ret = $db->command(array( "collStats" => $this->collection ));
		if ($ret["ok"]) {
			$this->stats = $ret;
			foreach ($this->stats as $index => $stat) {
				if (is_array($stat)) {
					$this->stats[$index] = $this->_highlight($stat, "json");
				}
			}
		}
		
		//top
		$ret = $this->_mongo->selectDB("admin")->command(array( "top" => 1 ));
		$this->top = array();
		$namespace = $this->db . "." . $this->collection;
		if ($ret["ok"] && !empty($ret["totals"][$namespace])) {
			$this->top = $ret["totals"][$namespace];
			foreach ($this->top as $index => $value) {
				$this->top[$index] = $value["count"];
			}
		}
		$this->display();
	}
	
	/** validate collection **/
	public function doCollectionValidate() {
		$this->db = x("db");
		$this->collection = xn("collection");
		
		$db = $this->_mongo->selectDB($this->db);
		$this->ret = $this->_highlight($db->selectCollection($this->collection)->validate(), "json");
		$this->display();
	}
	
	/** rename collection **/
	public function doCollectionRename() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->realName = $this->collection;
		if ($this->isPost()) {
			$oldname = trim(xn("oldname"));
			$newname = trim(xn("newname"));
			$removeExists = trim(xn("remove_exists"));
			if ($newname === "") {
				$this->error = "Please enter a new name.";
				$this->display();
				return;
			}
			if (!$removeExists) {
				//Is there a same name?
				$collections = MDb::listCollections($this->_mongo->selectDB($this->db));
				foreach ($collections as $collection) {
					if ($collection->getName() == $newname) {
						$this->error = "There is already a '{$newname}' collection, you should drop it before renaming.";
						$this->display();
						return;
					}
				}
			}
 			$this->ret = $this->_mongo->selectDB($this->db)->execute('function (coll, newname, dropExists) { db.getCollection(coll).renameCollection(newname, dropExists);}', array( $oldname, $newname, (bool)$removeExists ));
 			if ($this->ret["ok"]) {
 				$this->realName = $newname;
 				$this->message = "Operation success.";
 			}
 			else {
 				$this->error = "Operation failure";
 			}
			$this->ret_json = $this->_highlight($this->ret, "json");
		}
		$this->display();
	}
	
	/** collection properties **/
	public function doCollectionProps() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		$ret = $this->_mongo->selectDB($this->db)->command(array( "collStats" => $this->collection ));
		
		if (!$ret["ok"]) {
			exit("There is something wrong:<font color=\"red\">{$ret['errmsg']}</font>, please refresh the page to try again.");
		}
		$this->isCapped = 0;
		$this->size = 0;
		$this->max = 0;
		$options = $ret;
		if (isset($options["capped"])) {
			$this->isCapped = $options["capped"];
		}
		if (isset($options["size"])) {
			$this->size = $options["size"];
		}
		if (isset($options["max"])) {
			$this->max = $options["max"];
		}
		if ($this->isPost()) {
			$this->isCapped = xi("is_capped");
			$this->size = xi("size");
			$this->max = xi("max");
			
			//rename current collection
			$bkCollection = $this->collection . "_rockmongo_bk_" . uniqid();
			$this->ret = $this->_mongo->selectDB($this->db)->execute('function (coll, newname, dropExists) { db.getCollection(coll).renameCollection(newname, dropExists);}', array( $this->collection, $bkCollection, true ));
			if (!$this->ret["ok"]) {
				$this->error = "There is something wrong:<font color=\"red\">{$this->ret['errmsg']}</font>, please refresh the page to try again.";
				$this->display();
				return;
			}
			
			//create new collection
			$db = $this->_mongo->selectDB($this->db);
			$db->createCollection($this->collection, $this->isCapped, $this->size, $this->max);
			
			//copy data to new collection
			if (!$this->_copyCollection($db, $bkCollection, $this->collection, true)) {
				//try to recover
				$this->ret = $db->execute('function (coll, newname, dropExists) { db.getCollection(coll).renameCollection(newname, dropExists);}', array( $bkCollection, $this->collection, true ));
				
				$this->error = "There is something wrong:<font color=\"red\">{$ret['errmsg']}</font>, please refresh the page to try again.";
				$this->display();
				return;
			}
			
			//drop current collection
			$db->dropCollection($bkCollection);
		}
		
		$this->display();
	}
	
	/** duplicate collection **/
	public function doCollectionDuplicate() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		if (!$this->isPost()) {
			x("target", $this->collection . "_copy");
			x("remove_target", 1);
			x("copy_indexes", 1);
		}
		if ($this->isPost()) {
			$target = trim(xn("target"));
			$removeTarget = x("remove_target");
			$copyIndexes = x("copy_indexes");
			if ($target === "") {
				$this->error = "Please enter a valid target.";
				$this->display();
				return;
			}
			$db = $this->_mongo->selectDB($this->db);
			if ($removeTarget) {
				$db->selectCollection($target)->drop();
			}
			$this->_copyCollection($db, $this->collection, $target, $copyIndexes);
			$this->message = "Collection duplicated successfully.";
 		}
		$this->display();
	}
	
	/** transfer a collection **/
	public function doCollectionTransfer() {
		$this->redirect("db.dbTransfer", array(
			"db" => xn("db"),
			"collection" => xn("collection")
		));
	}
	
	/** export a collection **/
	public function doCollectionExport() {
		$this->redirect("db.dbExport", array( "db" => xn("db"), "collection" => xn("collection"), "can_download" => xn("can_download") ));
	}
	
	/** import a collection **/
	public function doCollectionImport() {
		$this->redirect("db.dbImport", array( "db" => xn("db") ));
	}	
}

?>