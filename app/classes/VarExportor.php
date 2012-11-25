<?php

define("MONGO_EXPORT_PHP", "array");
define("MONGO_EXPORT_JSON", "json");

class VarExportor {
	private $_db;
	private $_var;
	private $_phpParams = array();
	private $_jsonParams = array();
	private $_paramIndex = 0;

	/**
	 * construct exportor
	 *
	 * @param MongoDB $db current db you are operating
	 * @param mixed $var variable
	 */
	function __construct(MongoDB $db, $var) {
		$this->_db = $db;
		$this->_var = $var;
	}
	
	/**
	 * Export the variable to a string
	 *
	 * @param string $type variable type (array or json)
	 * @param boolean $fieldLabel if add label to fields
	 * @return string
	 */
	function export($type = MONGO_EXPORT_PHP, $fieldLabel = false) {
		if ($fieldLabel) {
			$this->_var = $this->_addLabelToArray($this->_var);
		}
		if ($type == MONGO_EXPORT_PHP) {
			return $this->_exportPHP();
		}
		return $this->_exportJSON();
	}
	
	private function _exportPHP() {
		$var = $this->_formatVar($this->_var);
		$string = var_export($var, true);
		$params = array();
		foreach ($this->_phpParams as $index => $value) {
			$params["'" . $this->_param($index) . "'"] = $value;
		}

		return strtr($string, $params);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $str
	 * @param unknown_type $from
	 * @param unknown_type $len
	 * @return unknown
	 * @author sajjad at sajjad dot biz (copied from PHP manual)
	 */
	private function _utf8_substr($str,$from,$len) {
		return function_exists('mb_substr') ?
            mb_substr($str, $from, $len, 'UTF-8') :
		    preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $from .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $len .'}).*#s','$1', $str);
	}
	
	private function _addLabelToArray($array, $prev = "") {
		$ret = array();
		$cutLength = 150;
		foreach ($array as $key => $value) {
			if (is_string($key) || is_int($key) || is_float($key)) {
				$newKey = $prev . ($prev === ""?"":".") . "rockfield." . $key;
				if (is_string($value) && strlen($value) > $cutLength) {
					$value = $this->_utf8_substr($value, 0, $cutLength);
					$value = $value . " __rockmore.{$newKey}.rockmore__";
				}
				$ret[$newKey . ".rockfield"] = $value;
				if (is_array($value)) {
					$ret[$newKey . ".rockfield"] = $this->_addLabelToArray($value, $newKey);
				}
			}
			else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	private function _exportJSON() {
		if (function_exists('json_encode')) {
		    $service = 'json_encode';
		} else {
		    import("classes.Services_JSON");
		    $json = new Services_JSON();
		    $service = array(&$json, 'encode');
		}
		$var = $this->_formatVarAsJSON($this->_var, $service);
		$string = call_user_func($service, $var);
		$params = array();
		foreach ($this->_jsonParams as $index => $value) {
			$params['"' . $this->_param($index) . '"'] = $value;
		}
		return json_unicode_to_utf8(json_format(strtr($string, $params)));
	}
	
	private function _param($index) {
		return "%{MONGO_PARAM_{$index}}"; 
	}
	
	private function _formatVar($var) {
		if (is_scalar($var) || is_null($var)) {
			switch (gettype($var)) {
				case "integer":
					if (class_exists("MongoInt32")) {
		                // producing MongoInt32 to keep type unchanged (Kyryl Bilokurov <kyryl.bilokurov@gmail.com>)
		 				$this->_paramIndex ++;
		 				$this->_phpParams[$this->_paramIndex] = 'new MongoInt32(' . $var . ')';
		            	return $this->_param($this->_paramIndex);      
					}                          
				default:
					return $var;
			}
		}
		if (is_array($var)) {
			foreach ($var as $index => $value) {
				$var[$index] = $this->_formatVar($value);
			}
			return $var;
		}
		if (is_object($var)) {
			$this->_paramIndex ++;
			switch (get_class($var)) {
				case "MongoId":
					$this->_phpParams[$this->_paramIndex] = 'new MongoId("' . $var->__toString() . '")';
					return $this->_param($this->_paramIndex);
				case "MongoInt32":
					$this->_phpParams[$this->_paramIndex] = 'new MongoInt32(' . $var->__toString() . ')';
					return $this->_param($this->_paramIndex);
				case "MongoInt64":
					$this->_phpParams[$this->_paramIndex] = 'new MongoInt64(' . $var->__toString() . ')';
					return $this->_param($this->_paramIndex);
				case "MongoDate":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDate(' . $var->sec . ', ' . $var->usec . ')';
					return $this->_param($this->_paramIndex);
				case "MongoRegex":
					$this->_phpParams[$this->_paramIndex] = 'new MongoRegex(\'/' . $var->regex . '/' . $var->flags . '\')';
					return $this->_param($this->_paramIndex);
				case "MongoTimestamp":
					$this->_phpParams[$this->_paramIndex] = 'new MongoTimestamp(' . $var->sec . ', ' . $var->inc . ')';
					return $this->_param($this->_paramIndex);
				case "MongoMinKey":
					$this->_phpParams[$this->_paramIndex] = 'new MongoMinKey()';
					return $this->_param($this->_paramIndex);
				case "MongoMaxKey":
					$this->_phpParams[$this->_paramIndex] = 'new MongoMaxKey()';
					return $this->_param($this->_paramIndex);
				case "MongoCode":
					$this->_phpParams[$this->_paramIndex] = 'new MongoCode("' . addcslashes($var->code, '"') . '", ' . var_export($var->scope, true) . ')';
					return $this->_param($this->_paramIndex);
				default:
					if (method_exists($var, "__toString")) {
						return $var->__toString();
					}
			}
		}
		return $var;
	}	
	
	private function _formatVarAsJSON($var, $jsonService) {
		if (is_scalar($var) || is_null($var)) {
			switch (gettype($var)) {
				case "integer":
					$this->_paramIndex ++;
					$this->_jsonParams[$this->_paramIndex] = 'NumberInt(' . $var . ')';
					return $this->_param($this->_paramIndex);
				default:
					return $var;
			}
		}
		if (is_array($var)) {
			foreach ($var as $index => $value) {
				$var[$index] = $this->_formatVarAsJSON($value, $jsonService);
			}
			return $var;
		}
		if (is_object($var)) {
			$this->_paramIndex ++;
			switch (get_class($var)) {
				case "MongoId":
					$this->_jsonParams[$this->_paramIndex] = 'ObjectId("' . $var->__toString() . '")';
					return $this->_param($this->_paramIndex);
				case "MongoInt32":
						$this->_jsonParams[$this->_paramIndex] = 'NumberInt(' . $var->__toString() . ')';
						return $this->_param($this->_paramIndex);
				case "MongoInt64":
					$this->_jsonParams[$this->_paramIndex] = 'NumberLong(' . $var->__toString() . ')';
					return $this->_param($this->_paramIndex);
				case "MongoDate":
					$timezone = @date_default_timezone_get();
					date_default_timezone_set("UTC");
					$this->_jsonParams[$this->_paramIndex] = "ISODate(\"" . date("Y-m-d", $var->sec) . "T" . date("H:i:s.", $var->sec) . ($var->usec/1000) . "Z\")";
					date_default_timezone_set($timezone);
					return $this->_param($this->_paramIndex);
				case "MongoTimestamp":
					$this->_jsonParams[$this->_paramIndex] = call_user_func($jsonService, array(
						"t" => $var->inc * 1000,
						"i" => $var->sec
					));
					return $this->_param($this->_paramIndex);
				case "MongoMinKey":
					$this->_jsonParams[$this->_paramIndex] = call_user_func($jsonService, array( '$minKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoMaxKey":
					$this->_jsonParams[$this->_paramIndex] = call_user_func($jsonService, array( '$minKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoCode":
					$this->_jsonParams[$this->_paramIndex] = $var->__toString();
					return $this->_param($this->_paramIndex);
				default:
        			if (method_exists($var, "__toString")) {
        				return $var->__toString();
        			}
                    return '<unknown type>';
			}
		}
	}
}

?>