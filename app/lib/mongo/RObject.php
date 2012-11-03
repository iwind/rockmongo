<?php
/**
 * 定义Mongo中的对象
 * 
 * 除了setAttr()/setAttrs()/setSource()之外其余的动作都不会直接修改当前对象的属性集，
 * 只有调用save()之后才会生效。
 * 
 * 在save()之前多次对同一个方法调用同一个指令，只有最后一个指令才会生效。
 * 目前你不能修改对象的ID。
 *
 */
class RObject extends RModel implements ArrayAccess {
	/**
	 * Enter description here...
	 *
	 * @var MongoCollection
	 */
	private $_collection;
	private $_operations = array();
	private $_attrs = array();
	private $_id;
	
	/**
	 * 构造对象
	 *
	 * @param array $attrs 属性集
	 */
	function __construct(array $attrs = array()) {
		$this->setAttrs($attrs);
	}
	
	/**
	 * 设置属性值
	 *
	 * @param string $name 属性名
	 * @param mixed $value 属性值
	 */
	function setAttr($name, $value) {
		if ($name == "_id") {
			$this->setId($value);
			return;
		}
		$this->_attrs = rock_array_set($this->_attrs, $name, $value);
		$this->_execOperator('$set', $name, $value);
	}
	
	/**
	 * 设置属性集
	 *
	 * @param array $attrs 属性集
	 */
	function setAttrs(array $attrs) {
		foreach ($attrs as $field => $value) {
			$this->setAttr($field, $value);
		}
	}
	
	/**
	 * 设置原始属性集，即刚从数据库中查出来的属性集
	 *
	 * @param array $attrs 原始属性集
	 */
	function setSource(array $attrs) {
		if (isset($attrs["_id"])) {
			$this->setId($attrs["_id"]);
		}
		$this->_attrs = array_merge($this->_attrs, $attrs);
	}
	
	private function _execOperator($operator, $attr, $newValue) {
		if (!isset($this->_operations[$operator])) {
			$this->_operations[$operator] = array();
		}
		$this->_operations[$operator][$attr] = $newValue;
	}
	
	
	/**
	 * 当前对象的ID，可以用来判断对象是否已经存储
	 *
	 * @return MongoId
	 */
	function id() {
		return $this->_id;
	}
	
	/**
	 * 取得当前对象的ID值
	 *
	 * @return string
	 */
	function idValue() {
		return $this->_id ? $this->_id->__toString() : null;
	}
	 
	/**
	 * 设置当前对象的ID
	 *
	 * @param string|MongoId $id 新的Id，如果非长度为24的16禁止的字符串，则MongoDB会认为是非法的ID，而忽略之
	 */
	function setId($id) {
		$this->_id = ($id);
	}
	
	/**
	 * 增加一个属性的值
	 *
	 * @param string $attr 属性
	 * @param integer $count 增加的数量
	 */
	function increase($attr, $count = 1) {
		$this->_execOperator('$inc', $attr, $count);
	}
	
	/**
	 * 删除一个属性值。如果是一个集合只会将其值设置为NULL，
	 * 这时候你需要调用$obj->pull($attrParent, null)来删除null值。
	 *
	 * @param string $attr 属性
	 * @param boolean $pullNull 是否自动删除NULL值
	 */
	function remove($attr, $pullNull = true) {
		if ($pullNull) {
			$this->_execOperator('$unset', $attr, 1);
			$this->save();
			if (strstr($attr, ".")) {
				$parent = substr($attr, 0, strrpos($attr, "."));
				$this->pull($parent, null);
			}
		}
		else {
			$this->_execOperator('$unset', $attr, 1);
		}
	}
	
	/**
	 * 在一个集合末尾增加新的元素
	 * 
	 * 注意索引数组和关联数组不能混合
	 *
	 * @param string $attr 属性
	 * @param mixed $value 属性值
	 * @param boolean|string|integer $genId 是否自动生成ID，如果是boolean，则可以指定是否自动生成ID，如果是string和integer，则直接使用此值作为ID
	 */
	function push($attr, $value, $genId = false) {
		if (is_bool($genId) && $genId) {
			$attr .= "." . strtoupper(uniqid("ID_"));
			$this->setAttr($attr, $value);
		}
		elseif (is_string($genId) || is_integer($genId)) {
			$attr .= "." . $genId;
			$this->setAttr($attr, $value);
		}
		else {
			$this->_execOperator('$push', $attr, $value);	
		}
	}
	
	/**
	 * 在一个集合末尾增加一组新的元素
	 *
	 * @param string $attr 属性
	 * @param array $values 属性值
	 */
	function pushAll($attr, array $values) {
		$this->_execOperator('$pushAll', $attr, $values);
	}
	
	function addToSet($attr, $value) {
		$this->_execOperator('$addToSet', $attr, $value);
	}

	function addAllToSet($attr, array $values) {
		$this->_execOperator('$addToSet', $attr, array( '$each' => $values ));
	}
	
	function pop($attr) {
		$this->_execOperator('$pop', $attr, 1);
	}
	
	function shift($attr) {
		$this->_execOperator('$pop', $attr, -1);
	}
	
	/**
	 * 拽出某一个数组中的值为$value的元素
	 *
	 * @param string $attr 属性
	 * @param mixed $value 元素值
	 */
	function pull($attr, $value) {
		$this->_execOperator('$pull', $attr, $value);
	}
	
	function pullAll($attr, array $values) {
		$this->_execOperator('$pullAll', $attr, $values);
	} 
	
	/**
	 * 保存当前对象
	 *
	 * @param boolean $refresh 是否刷新当前对象
	 * @return boolean
	 */
	function save($refresh = false) {
		if (!$this->_collection) {
			import("@.RMongoException");
			throw new RMongoException("Object is not in any collection, please use setCollection() to method to set a collection.");
		}
		$bool = true;
		if ($this->_id) {//if exists
			if (!empty($this->_operations)) {
				$bool = $this->_collection->update(array( "_id" => $this->_id ), $this->_operations, array(  "upsert" => false, "multiple" => false, "safe" => true ));
				if ($refresh) {
					$bool = $this->refresh();
				}
			}
		}
		else {
			$bool = $this->_collection->insert($this->_attrs, true);
			if ($bool) {
				$this->_id = $this->_attrs["_id"];
				import("@.RMongo");
				RMongo::setLastInsertId($this->_id->__toString());
			}
		}
		$this->_operations = array();
		return $bool;
	}
	
	/**
	 * 刷新当前对象
	 *
	 * @return boolean
	 */
	function refresh() {
		if (!$this->_collection) {
			import("@.RMongoException");
			throw new RMongoException("Object is not in any collection, please use setCollection() to method to set a collection.");
		}
		if (!$this->_id) {
			return true;
		}
		$this->setSource($this->_collection->findOne( array( "_id" => $this->_id )  ));
		return true;
	}
	
	function setCollection(MongoCollection $collection) {
		$this->_collection = $collection;
	}
	
	function attr($name) {
		return rock_array_get($this->_attrs, $name);
	}
	
	/**
	 * 取得当前对象的所有属性值
	 *
	 * @return array
	 */
	function attrs() {
		return $this->_attrs;
	}
	
	/**
	 * 删除当前对象
	 *
	 */
	function delete() {
		if ($this->_collection && $this->_id) {
			$this->_collection->remove(array( "_id" => $this->_id ));
		}
		$this->_id = null;
		$this->_attrs = array();
	}
	
	function __get($name) {
		return $this->attr($name);
	}
	
	/**
	 * 判断一个偏移量是否存在
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 * 
	 * @param integer $index
	 * @return boolean
	 * @since 1.0
	 */
	function offsetExists($index) {
		return !is_null($this->attr($index));
	}
	
	/**
	 * 从一个偏移量中取得数据
	 * 
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index 偏移量
	 * @return mixed
	 */
	function offsetGet($index) {
		return $this->attr($index);
	}
	
	/**
	 * 设置偏移量位置上的值
	 *
	 * 实现了 ArrayAccess 接口对应方法
	 * 
	 * @param integer $index 偏移量
	 * @param mixed $item 值
	 */
	function offsetSet($index, $item) {
		$this->setAttr($index, $item);
	}
	
	/**
	 * 删除偏移量位置对应的元素
	 * 
	 * 实现了 ArrayAccess 接口对应方法
	 *
	 * @param integer $index 偏移量
	 */
	function offsetUnset($index) {
		$this->setAttr($index, null);
	}			
}

?>