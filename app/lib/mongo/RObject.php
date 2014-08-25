<?php
/**
 * Object in Mongo
 *
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
	 * Construct object
	 *
	 * @param array $attrs fields
	 */
	function __construct(array $attrs = array()) {
		$this->setAttrs($attrs);
	}

	/**
	 * Set field value
	 *
	 * @param string $name field name
	 * @param mixed $value field value
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
	 * Set fields values
	 *
	 * @param array $attrs fields values
	 */
	function setAttrs(array $attrs) {
		foreach ($attrs as $field => $value) {
			$this->setAttr($field, $value);
		}
	}

	/**
	 * Set original fields values
	 *
	 * @param array $attrs fields
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
	 * Return current object ID, can determine if the object is saved
	 *
	 * @return MongoId
	 */
	function id() {
		return $this->_id;
	}

	/**
	 * Return current object ID string value
	 *
	 * @return string
	 */
	function idValue() {
		return $this->_id ? $this->_id->__toString() : null;
	}

	/**
	 * Set current object id
	 *
	 * @param string|MongoId $id New id, must contains 24 chars
	 */
	function setId($id) {
		$this->_id = ($id);
	}

	/**
	 * Increase numeric field value
	 *
	 * @param string $attr Field
	 * @param integer $count The count to increase
	 */
	function increase($attr, $count = 1) {
		$this->_execOperator('$inc', $attr, $count);
	}

	/**
	 * Remove field
	 *
	 * Later, you need to $obj->pull($attrParent, null) to remove NULL field
	 *
	 * @param string $attr Field
	 * @param boolean $pullNull Should remove NULL field automatically?
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
	 * Push value to collection
	 *
	 * @param string $attr Field
	 * @param mixed $value Value
	 * @param boolean|string|integer $genId Should generate ID?
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
	 * Add values to collection
	 *
	 * @param string $attr Field
	 * @param array $values Values
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
	 * Pull specified value in field
	 *
	 * @param string $attr Field
	 * @param mixed $value Value
	 */
	function pull($attr, $value) {
		$this->_execOperator('$pull', $attr, $value);
	}

	function pullAll($attr, array $values) {
		$this->_execOperator('$pullAll', $attr, $values);
	}

	/**
	 * Save current object to MongoDB
	 *
	 * @param boolean $refresh Should refresh the object fields values?
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
				$bool = $this->_collection->update(array( "_id" => $this->_id ), $this->_operations, array(
					"upsert" => false,
					"multiple" => false
				));
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
	 * Refresh the current object
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
	 * Get all fields values
	 *
	 * @return array
	 */
	function attrs() {
		return $this->_attrs;
	}

	/**
	 * Delete the object
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
	 * Determine offset exists
	 *
	 * For ArrayAccess implementation
	 *
	 * @param integer $index
	 * @return boolean
	 * @since 1.0
	 */
	function offsetExists($index) {
		return !is_null($this->attr($index));
	}

	/**
	 * Get value at specified offset
	 *
	 * For ArrayAccess implementation
	 *
	 * @param integer $index Offset
	 * @return mixed
	 */
	function offsetGet($index) {
		return $this->attr($index);
	}

	/**
	 * Set value at specified offset
	 *
	 * For ArrayAccess implementation
	 *
	 * @param integer $index 偏移量
	 * @param mixed $item 值
	 */
	function offsetSet($index, $item) {
		$this->setAttr($index, $item);
	}

	/**
	 * Unset value at specified offset
	 *
	 * For ArrayAccess implementation
	 *
	 * @param integer $index Offset
	 */
	function offsetUnset($index) {
		$this->setAttr($index, null);
	}
}

?>