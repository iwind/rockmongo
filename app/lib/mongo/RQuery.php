<?php

/**
 * 对MongoDB中集合的查询
 *
 * MongoDB中的数据类型是严格对应的，所以要注意整数和字符串的区别：
 * - attr("state", 0);
 * - attr("state", "0");
 * 两个查询是不一样的
 * 
 */
class RQuery {
	/**
	 * Enter description here...
	 *
	 * @var MongoDB
	 */
	private $_db;
	private $_dbName;
	/**
	 * Enter description here...
	 *
	 * @var MongoCollection
	 */
	private $_collection;
	private $_collectionName;
	private $_attrs = array();//field1 => array(..), field2 => array(...) ...
	private $_results = array();//field => [1|0]
	private $_sort = array();//field => [1|-1]
	private $_offset = -1;
	private $_limit = 0;
	private $_conds = array();//field1 => array( '$lt' => value1, .. )
	private $_noPk = false;
	private $_hints = array();

	/**
	 * 构造查询
	 *
	 * @param Mongo $mongo MongoDB连接
	 * @param string $db 数据库
	 * @param string $collection 集合
	 */
	function __construct(RMongo $mongo, $db, $collection) {
		$this->_dbName = $db;
		$this->_collectionName = $collection;
		$this->_db = $mongo->selectDB($this->_dbName);
		$this->_collection = $mongo->selectCollection($this->_dbName, $this->_collectionName);
	}
	
	/**
	 * 指定属性值
	 *
	 * @param string|array $nameOrAttrs 属性名或一组属性值
	 * @param string $value 属性值
	 * @return RQuery
	 */
	function attr($nameOrAttrs, $value = null) {
		if (!is_array($nameOrAttrs)) {
			$nameOrAttrs = array( $nameOrAttrs => $value );
		}
		foreach ($nameOrAttrs as $attr => $value) {
			if ($attr == "_id" && (!is_object($value) || !($value instanceof MongoId)) && strlen($attr) == 24) {
				$value = new MongoId($value);
			}
			if (!isset($this->_attrs[$attr])) {
				$this->_attrs[$attr] = array();
			}
			if (is_array($value)) {
				$this->_attrs[$attr] = array_merge($this->_attrs[$attr], array($value));
			}
			else {
				$this->_attrs[$attr][] = $value;
			}
		}
		return $this;
	}
	
	/**
	 * 指定返回的结果属性。在当前的mongo版本中（<1.4.x），不能混合使用result()和exclude()
	 * 
	 * @param string $attr1 第一个属性
	 * @param ...
	 * @return RQuery
	 */
	function result($attr1 = null) {
		foreach (func_get_args() as $arg) {
			if ($arg) {
				if (is_array($arg)) {
					foreach ($arg as $v) {
						$this->_results[$v] = 1;
					}
				}
				else if (strstr($arg, ",")) {
					foreach (preg_split("/\s*,\s*/", $arg) as $attr) {
						$this->_results[$attr] = 1;
					}
				}
				else {
					$this->_results[$arg] = 1;
				}
			}
		}
		return $this;
	}
	
	/**
	 * 指定返回的结果中要排除的属性
	 * 
	 * @param string $attr1 第一个属性
	 * @param ...
	 * @return RQuery
	 */
	function exclude($attr1 = null) {
		foreach (func_get_args() as $arg) {
			if ($arg) {
				if (strstr($arg, ",")) {
					foreach (preg_split("/\s*,\s*/", $arg) as $attr) {
						$this->_results[$attr] = 0;
					}
				}
				else {
					$this->_results[$arg] = 0;
				}
			}
		}
		return $this;
	}
	
	/**
	 * 设置正排序条件
	 *
	 * @param string $attr 需要排序的属性
	 * @return RQuery
	 */
	function asc($attr = "_id") {
		$this->_sort[$attr] = 1;
		return $this;
	}
	
	/**
	 * 设置倒排序条件
	 *
	 * @param string $attr 需要排序的属性
	 * @return RQuery
	 */	
	function desc($attr = "_id") {
		$this->_sort[$attr] = -1;
		return $this;
	}
	
	/**
	 * 设置记录开始的位置
	 *
	 * @param integer $offset 开始位置
	 * @return RQuery
	 */
	function offset($offset) {
		$this->_offset = intval($offset);
		return $this;
	}
	
	/**
	 * 设置需要查询的记录行数
	 *
	 * @param integer $size 行数
	 * @return RQuery
	 */
	function limit($size) {
		$this->_limit = intval($size);
		return $this;
	}
	
	/**
	 * 增加查询条件
	 *
	 * @param array $cond 查询条件
	 * @return RQuery
	 */
	function cond(array $cond) {
		foreach ($cond as $key => $value) {
			$this->_conds[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * 添加操作符
	 *
	 * @param string $attr 属性名
	 * @param string $operator 操作符，比如$gt, $lt ...
	 * @param mixed $value 操作符对应的值
	 * @return RQuery
	 */
	function operator($attr, $operator, $value) {
		if (!isset($this->_conds[$attr])) {
			$this->_conds[$attr] = array();
		}
		$this->_conds[$attr][$operator] = $value;
		return $this;
	}
	
	function gt($attr, $value) {
		return $this->operator($attr, '$gt', $value);
	}
	
	function lt($attr, $value) {
		return $this->operator($attr, '$lt', $value);
	}
	
	function gte($attr, $value) {
		return $this->operator($attr, '$gte', $value);
	}
	
	function lte($attr, $value) {
		return $this->operator($attr, '$lte', $value);
	}
	
	/**
	 * 设置不等于（!=）条件
	 *
	 * @param string $attr 属性名
	 * @param mixed $value 和属性比较的值
	 * @return RQuery
	 */
	function ne($attr, $value) {
		return $this->operator($attr, '$ne', $value);
	}
	
	function in($attr, array $values) {
		return $this->operator($attr, '$in', $values);
	}
	
	function nin($attr, array $values) {
		return $this->operator($attr, '$nin', $values);
	}
	
	function mod($attr, $value) {
		return $this->operator($attr, '$mod', $value);
	} 
	
	function all($attr, $value) {
		return $this->operator($attr, '$all', $value);
	}
	
	function contains($attr, $value) {
		return $this->all($attr, '$all', $value);
	}
	
	/**
	 * 限定集合属性的尺寸
	 *
	 * @param string $attr 属性名
	 * @param integer $size 限制的尺寸
	 * @return RQuery
	 */
	function size($attr, $size) {
		return $this->operator($attr, '$size', intval($size));
	}
	
	function exists($attr, $bool = true) {
		return $this->operator($attr, '$exists', $bool);
	}
	
	function type($attr, $type) {
		return $this->operator($attr, '$type', intval($type));
	}
	
	function match($attr, $regexp) {
		return $this->attr($attr, new MongoRegex($regexp));
	}
	
	/**
	 * 分割一个是集合（相当于PHP中的索引数组）的属性值
	 *
	 * @param string $attr 属性名
	 * @param integer $subOffset 开始位置
	 * @param integer $subLimit 要取出的条数
	 * @return RQuery
	 */
	function slice($attr, $subOffset, $subLimit) {
		$this->_results[$attr]['$slice'] = array( intval($subOffset), intval($subLimit) );
		return $this;
	}
	
	/**
	 * 使用函数。如果数据较多话，将会非常慢。
	 *
	 * @param string $func Javascript函数
	 * @return RQuery
	 */
	function func($func) {
		$this->_conds['$where'] = $func;
		return $this;
	}
	
	/**
	 * 设置否是不返回主键（_id）
	 *
	 * @param unknown_type $returnPk
	 * @return RQuery
	 */
	function noPk($noPk = true) {
		$this->_noPk = $noPk;
		return $this;
	}
	
	/**
	 * 设置查询的主键值
	 *
	 * @param string $pk1 主键1
	 * @param string ...
	 * @return RQuery
	 */
	function id($pk1) {
		foreach (func_get_args() as $arg) {
			$this->attr("_id", $arg);
		}
		return $this;
	}
	
	function copy() {
		exit(__METHOD__ . "() to be implemented");
	}
	
	/**
	 * 现有查询条件的组合
	 *
	 * @return array
	 */
	function criteria() {
		$attrs = $this->_attrs;
		foreach ($this->_attrs as $attr => $values) {
			if (!empty($values)) {
				if (count($values) == 1) {
					$attrs[$attr] = $values[0];
				}
				else {
					$attrs[$attr]['$in'] = $values;
				}
			}
		}
		foreach ($this->_conds as $key => $value) {
			$attrs[$key] = $value;
		}
		return $attrs;
	}
	
	/**
	 * add hints for query
	 *
	 * @param unknown_type $hint
	 * @return RQuery
	 */
	function hint($hint) {
		$this->_hints[] = $hint;
		return $this;
	}
	
	/**
	 * 取得当前查询的游标
	 *
	 * @return MongoCursor
	 */
	function cursor() {
		$cursor = $this->_collection->find($this->criteria(), $this->_results);
		if ($this->_offset >= 0) {
			$cursor->skip($this->_offset);
		}
		if ($this->_limit > 0) {
			$cursor->limit($this->_limit);
		}
		if ($this->_sort) {
			$cursor->sort($this->_sort);
		}
		if (!empty($this->_hints)) {
			foreach ($this->_hints as $hint) {
				$cursor->hint($hint);
			}
		}
		return $cursor;
	}
	
	/**
	 * 查找一行数据，并以一个对象的形式返回
	 *
	 * @param string $id 主键_id值
	 * @return RObject
	 */
	function find($id = null) {
		if (!is_null($id)) {
			$this->id($id);
		}
		$row = $this->findOne();
		if (empty($row)) {
			return null;
		}
		import("@.RObject");
		$obj = new RObject();
		$obj->setSource($row);
		$obj->setCollection($this->_collection);
		$obj->setId($row["_id"]);
		return $obj;
	}
	
	/**
	 * 查找一行数据，以数组的形式返回
	 *
	 * @param string $id 主键_id值
	 * @return array
	 */
	function findOne($id = null) {
		if (!is_null($id)) {
			$this->id($id);
		}
		$this->limit(1);
		$all = $this->findAll();
		return empty($all) ? array() : $all[0];
	}
	
	/**
	 * 查找一行数据，但只返回ID数据
	 *
	 * @param string $id 主键_id值
	 * @return RObjct
	 */	
	function findId($id = null) {
		if (!is_null($id)) {
			$this->id($id);
		}
		$this->_results = array();
		$this->_results["_id"] = 1;
		return $this->find();
	}
	
	/**
	 * 根据请求的参数查询数据
	 *
	 * @param string $requestPkName 值为主键值的请求参数
	 * @return RObject
	 */
	function findx($requestPkName = "id") {
		return $this->find(x($requestPkName));
	}
	
	/**
	 * 取出所有记录
	 *
	 * @param boolean $keepId 是否保留ID的原始状态
	 * @return array
	 */
	function findAll($keepId = true) {
		$rets = array();
		foreach ($this->cursor() as $value) {
			if ($this->_noPk) {
				unset($value["_id"]);
			}
			else {
				if (!$keepId && isset($value["_id"]) && ($value["_id"] instanceof MongoId)) {
					$value["_id"] = $value["_id"]->__toString();
				}
			}
			$rets[] = $value;
		}
		return $rets;
	}
	
	/**
	 * 分页
	 *
	 * @param array $params 分页参数
	 * @param string $style 分页样式
	 * @return RPage
	 */
	function page(array $params = array(), $style = null) {
		import("RPage");
		if (!class_exists("RPage")) {
			exit("You must use the method " . __METHOD__ . "() with page plugin.");
		}
		$page = RPage::pageWithStyle($style, $params);
		$this->offset($page->offset());
		$this->limit($page->size());
		$page->setRows($this->findAll());
		return $page;
	}
	
	/**
	 * 计算符合条件的行数
	 *
	 * @param boolean $withLimit limit()/offset()方法是否有效
	 * @return integer
	 */
	function count($withLimit = false) {
		return $this->cursor()->count($withLimit);
	}
	
	/**
	 * Insert new record
	 *
	 * @param array $attrs attributes of new record
	 * @param boolean $safe check result
	 * @return boolean
	 */
	function insert(array $attrs, $safe = false) {
		$bool = $this->_collection->insert($attrs, array( "safe" => $safe ));
		if ($bool) {
			import("@.RMongo");
			if ($attrs["_id"] instanceof MongoId) {
				RMongo::setLastInsertId($attrs["_id"]->__toString());
			}
			else {
				RMongo::setLastInsertId($attrs["_id"]);
			}
		}
		return $bool;
	}	
	
	/**
	 * 插入新的行，_id是上一行的ID加1
	 *
	 * @param array $attrs 新行的属性集
	 * @return boolean
	 */
	function insertNext(array $attrs) {
		$response = $this->_db->execute('function insertObject(o, myCollection) {
			var x = db.getCollection(myCollection);
			while( 1 ) {
		        // determine next _id value to try
		        var c = x.find({},{_id:1}).sort({_id:-1}).limit(1);
		        var i = c.hasNext() ? c.next()._id + 1 : 1;
		        o._id = i;
		        x.insert(o);
		        var err = db.getLastErrorObj();
		        if( err && err.code ) {
		            if( err.code == 11000 /* dup key */ )
		                continue;
		            else
		                print("unexpected error inserting data: " + tojson(err));
		        }
		        break;
		    }
		    return o._id;
		}', array( $attrs, $this->_collectionName ));		
		
		if ($response["ok"]) {
			import("@.RMongo");
			RMongo::setLastInsertId($response["retval"]);
		}
		
		return $response["ok"];
	}

	/**
	 * 删除符合条件的记录
	 *
	 * @return boolean
	 */
	function delete() {
		return $this->_collection->remove($this->criteria());
	}
	
	/**
	 * 更改或插入新的对象
	 *
	 * 在当前驱动下不能正常工作
	 * 
	 * @param array $obj 新的对象
	 * @return boolean
	 */
	function upsert(array $obj) {
		return $this->_collection->update($this->criteria(), $obj, array( "upsert" => true, "multiple" => true));
	}
	
	/**
	 * 批量插入一组新的数据
	 *
	 * @param array $array 每一个元素包含一个要插入的行
	 * @return boolean
	 */
	function batchInsert(array $array) {
		return $this->_collection->batchInsert($array);
	}
	/**
	 * 当前操作的集合
	 *
	 * @return MongoCollection
	 */
	function collection() {
		return $this->_collection;
	}
	
	/**
	 * 当前操作的数据库
	 *
	 * @return MongoDB
	 */
	function db() {
		return $this->_db;
	}
}

?>