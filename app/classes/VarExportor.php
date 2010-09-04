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
	
	function export($type = MONGO_EXPORT_PHP) {
		if ($type == MONGO_EXPORT_PHP) {
			return $this->_exportPHP();
		}
		return $this->_exportJSON();
	}
	
	private function _exportPHP() {
		$var = $this->_formatVar($this->_var);
		$string = var_export($var, true);
		foreach ($this->_phpParams as $index => $value) {
			$string = str_replace("'" . $this->_param($index) . "'", $value, $string);
		}
		return $string;
	}
	
	private function _exportJSON() {
		$ret = $this->_db->execute('function(v){ return tojson(v);}',array( $this->_var ));
		if ($ret["ok"] == 1) {
			return $ret["retval"];
		}
		
		import("classes.Services_JSON");
		$service = new Services_JSON();
		$var = $this->_formatVarAsJSON($this->_var, $service);

		$string = $service->encode($var);
		foreach ($this->_jsonParams as $index => $value) {
			$string = str_replace("\"" . $this->_param($index) . "\"", $value, $string);
		}
		return ($string);
	}
	
	private function _param($index) {
		return "%{MONGO_PARAM_{$index}}"; 
	}
	
	private function _formatVar($var) {
		if (is_scalar($var) || is_null($var)) {
			return $var;
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
			return $var;
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
				case "MongoDate":
					$this->_jsonParams[$this->_paramIndex] = "\"" . date("r", $var->sec) . "\"";
					return $this->_param($this->_paramIndex);
				case "MongoRegex":
					$this->_jsonParams[$this->_paramIndex] = $var->__toString();
					return $this->_param($this->_paramIndex);
				case "MongoTimestamp":
					$this->_jsonParams[$this->_paramIndex] = $jsonService->encode(array(
						"t" => $var->inc * 1000,
						"i" => $var->sec
					));
					return $this->_param($this->_paramIndex);
				case "MongoMinKey":
					$this->_jsonParams[$this->_paramIndex] = $jsonService->encode(array( '$minKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoMaxKey":
					$this->_jsonParams[$this->_paramIndex] = $jsonService->encode(array( '$maxKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoCode":
					$this->_jsonParams[$this->_paramIndex] = $var->__toString();
					return $this->_param($this->_paramIndex);
				default:
					if (method_exists($var, "__toString")) {
						return $var->__toString();
					}
			}
		}
		
		
	}
}

?>