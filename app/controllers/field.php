<?php

import("classes.BaseController");

class FieldController extends BaseController {
	public $db;
	public $collection;
	/**
	 * Enter description here...
	 *
	 * @var MongoDB
	 */
	protected $_mongodb;
	
	function onBefore() {
		parent::onBefore();
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->_mongodb = $this->_mongo->selectDB($this->db);
	}
	
	/**
	 * remove a field
	 */
	function doRemove() {
		$db = $this->_mongo->selectDb($this->db);
		
		$field = xn("field");
		$id = xn("id");
		if ($id) {
			$db->execute('function (collection, id, field) {
				var col = db.getCollection(collection);
				var obj = {
					"$unset": {}
				};
				obj["$unset"][field] = 1;
				col.update({"_id":id}, obj, false, false);
			}', array( $this->collection, rock_real_id($id), $field ));
		}
		else {
			$db->execute('function (collection, field) {
				var col = db.getCollection(collection);
				var obj = {
					"$unset": {}
				};
				obj["$unset"][field] = 1;
				col.update({}, obj, false, true);
			}', array( $this->collection, $field ));
		}
		exit();
	}
	
	/**
	 * set field value to NULL
	 */
	function doClear() {
		$db = $this->_mongo->selectDb($this->db);
		
		$field = xn("field");
		$id = xn("id");
		if ($id) {
			$db->execute('function (collection, id, field) {
				var col = db.getCollection(collection);
				var obj = {
					"$set": {}
				};
				obj["$set"][field] = null;
				col.update({"_id":id}, obj, false, false);
			}', array( $this->collection, rock_real_id($id), $field ));
		}
		else {
			$db->execute('function (collection, field) {
				var col = db.getCollection(collection);
				var obj = {
					"$set": {}
				};
				obj["$set"][field] = null;
				col.update({}, obj, false, true);
			}', array( $this->collection, $field ));
		}
		exit();
	}	
	
	/**
	 * rename a field
	 */
	function doRename() {
		$db = $this->_mongo->selectDB($this->db);
		
		$field = xn("field");
		$id = xn("id");
		$newname = trim(xn("newname"));
		$keep = xn("keep");
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		$ret = $db->execute('function (coll, field, newname, id, keep){
				var cursor;
				if (id) {
					cursor = db.getCollection(coll).find({_id:id});
				}
				else {
					cursor = db.getCollection(coll).find();
				}
				while(cursor.hasNext()) {
					var row = cursor.next();
					var newobj = { $rename: {} };
					if (typeof(row[newname]) == "undefined" || !keep) {
						newobj["$rename"][field] = newname;
					}
					if (typeof(row["_id"]) != "undefined") {
						db.getCollection(coll).update({ _id:row["_id"] }, newobj);
					}
					else {
						db.getCollection(coll).update(row, newobj);
					}
				}
			}', array($this->collection, $field, $newname, rock_real_id($id), $keep ? true:false));
		$this->_outputJson(array( "code" => 200 ));
	}
	
	/**
	 * create new field
	 */
	function doNew() {
		$db = $this->_mongo->selectDB($this->db);
		
		$id = xn("id");
		$newname = trim(xn("newname"));
		$keep = xn("keep");
		$dataType = xn("data_type");
		$value = xn("value");
		$boolValue = xn("bool_value");
		$integerValue = xn("integer_value");
		$longValue = xn("long_value");
		$doubleValue = xn("double_value");
		$mixedValue = xn("mixed_value");
		$format = x("format");
		
		$this->_rememberFormat($format);
		
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		
		$realValue = null;
		try {
			$realValue = $this->_convertValue($this->_mongodb, $dataType, $format, $value, $integerValue, $longValue, $doubleValue, $boolValue, $mixedValue);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 400, "message" => $e->getMessage()));
		}
		
		$fieldType = "";
		if ($dataType == "integer") {
			$fieldType = "integer";
		} else if ($dataType == "long") {
			$fieldType = "long";
		}
		
		if (!$keep) {
			if ($id) {
				$db->selectCollection($this->collection)->update(array(
					"_id" => rock_real_id($id)
				), array( '$set' => array( $newname => $realValue ) ));
			}
			else {
				$db->selectCollection($this->collection)->update(array(), array( '$set' => array( $newname => $realValue ) ), array( "multiple" => 1 ));
			}
			$this->_outputJson(array( "code" => 200 ));
		}
		$ret = $db->execute('function (coll, newname, fieldType, value, id, keep){
				if (typeof(value) != "object") {
					if (fieldType == "integer") {
						if (typeof(NumberInt) != "undefined") {
							value = NumberInt(value);
						}
					} else if (fieldType == "long") {
						value = NumberLong(value);
					}
				}
				
				var cursor;
				if (id) {
					cursor = db.getCollection(coll).find({_id:id});
				}
				else {
					cursor = db.getCollection(coll).find();
				}
				while(cursor.hasNext()) {
					var row = cursor.next();
					var newobj = { $set:{} };
					if (typeof(row[newname]) == "undefined" || !keep) {
						newobj["$set"][newname] = value;
					}
					if (typeof(row["_id"]) != "undefined") {
						db.getCollection(coll).update({ _id:row["_id"] }, newobj);
					}
					else {
						db.getCollection(coll).update(row, newobj);
					}
				}
			}', array($this->collection, $newname, $fieldType, $realValue, rock_real_id($id), $keep ? true:false));
		if ($ret["ok"]) {
			$this->_outputJson(array( "code" => 200 ));
		}
		else {
			$this->_outputJson(array( "code" => 500, "message" => $ret["errmsg"] ));
		}
	}	
	
	/**
	 * load field data
	 *
	 */
	function doLoad() {
		$collection = $this->_mongodb->selectCollection($this->collection);
		$id = xn("id");
		$field = xn("field");
		$type = "integer";
		$data = null;
		if ($id) {
			$one = $collection->findOne(array( "_id" => rock_real_id($id) ));//not select field, because there is a bug in list, such as "list.0"
			$data = rock_array_get($one, $field);
			switch (gettype($data)) {
				case "boolean":
					$type = "boolean";
					break;
				case "integer":
					$type = "integer";
					break;
				case "long":
					$type = "long";
					break;
				case "float":
				case "double":
					$type = "double";
					break;
				case "string":
					$type = "string";
					break;
				case "array":
					$type = "mixed";
					break;
				case "object":
					// int64 is returned as object (Kyryl Bilokurov <kyryl.bilokurov@gmail.com>)
					if (get_class($data) == "MongoInt64") {
						$type = "long";
					} else {
						$type = "mixed";
					}
					break;
				case "resource":
					$type = "mixed";
					break;
				case "NULL":
					$type = "null";
					break;
			}
		}
		$exporter = new VarExportor($this->_mongodb, $data);
		$format = rock_cookie("rock_format", "json");
		$represent = $exporter->export($format);
		if ($format == "json") {
			$represent = json_unicode_to_utf8($represent);
		}
		$this->_outputJson(array(
			"code" => 200,
			"type" => $type,
			 // long requires special handling (Kyryl Bilokurov <kyryl.bilokurov@gmail.com>)
			"value" => ($type=="long") ? $data->__toString() : $data,
			"represent" => $represent,
			"format" => $format
		));
	}
	
	/**
	 * update value for a field
	 */
	function doUpdate() {
		$db = $this->_mongo->selectDB($this->db);
		
		$id = xn("id");
		$newname = trim(xn("newname"));
		$dataType = xn("data_type");
		$value = xn("value");
		$boolValue = xn("bool_value");
		$integerValue = xn("integer_value");
		$longValue = xn("long_value");
		$doubleValue = xn("double_value");
		$mixedValue = xn("mixed_value");
		$format = xn("format");
		
		$this->_rememberFormat($format);
		
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		
		$realValue = null;
		try {
			$realValue = $this->_convertValue($this->_mongodb, $dataType, $format, $value, $integerValue, $longValue, $doubleValue, $boolValue, $mixedValue);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 400, "message" => $e->getMessage()));
		}
		
		$fieldType = "";
		if ($dataType=="integer") {
			$fieldType = "integer";
		} else if ($dataType == "long") {
			$fieldType = "long";
		}
		$ret = array();
		if ($id) {
			$ret = $db->execute('function (collection, id, field, fieldType, value) {
				var col = db.getCollection(collection);
				var obj = {
					"$set": {}
				};
				if (typeof(value) != "object") {
					if (fieldType == "integer") {
						if (typeof(NumberInt) != "undefined") {
							value = NumberInt(value);
						}
					} else if (fieldType=="long") {
						value = NumberLong(value);
					}
				}
				obj["$set"][field] = value;
				col.update({ "_id": id }, obj, false, false);
			}', array($this->collection, rock_real_id($id), $newname, $fieldType, $realValue));
		}
		else {
			$ret = $db->execute('function (collection, field, fieldType, value) {
				var col = db.getCollection(collection);
				var obj = {
					"$set": {}
				};
				if (typeof(value) != "object") {
					if (fieldType=="integer") {
						if (typeof(NumberInt) != "undefined") {
							value = NumberInt(value);
						}
					} else if (fieldType=="long") {
						value = NumberLong(value);
					}
				}
				obj["$set"][field] = value;
				col.update({}, obj, false, true);
			}', array($this->collection, $newname, $fieldType, $realValue));
		}
		if ($ret["ok"]) {
			$this->_outputJson(array( "code" => 200 ));
		}
		else {
			$this->_outputJson(array( "code" => 500, "message" => $ret["errmsg"] ));
		}
	}
	
	function doIndexes() {
		$field = xn("field");
		$indexes = $this->_mongodb->selectCollection($this->collection)->getIndexInfo();
		$ret = array();
		foreach ($indexes as $index) {
			if (isset($index["key"][$field])) {
				$ret[] = array( "name" => $index["name"], "key" => $this->_highlight($index["key"], MONGO_EXPORT_JSON));
			}
		}
		$this->_outputJson(array( "code" => 200, "indexes" => $ret ));
	}
	
	function doCreateIndex() {
		$fields = xn("field");
		if (!is_array($fields)) {
			$this->_outputJson(array( "code" => 300, "message" =>  "Index contains one field at least."));
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
			$this->_outputJson(array( "code" => 300, "message" =>  "Index contains one field at least."));
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
		
		//check name 
		$collection = $this->_mongodb->selectCollection($this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == $name) {
				$this->_outputJson(array( "code" => 300, "message" => "The name \"{$name}\" is token by other index."));
				break;
			}
			if ($attrs === $index["key"]) {
				$this->_outputJson(array( "code" => 300, "message" => "The key on same fields already exists."));
				break;
			}
 		}
 		
 		$ret = null;
		try {
			$ret = $collection->ensureIndex($attrs, $options);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 300, "message" => $e->getMessage()));
		}
		if ($ret["ok"]) {
			$this->_outputJson(array( "code" => 200));
		}
		else {
			$this->_outputJson(array( "code" => 300, "message" => $ret["err"]));
		}
	}
}

?>