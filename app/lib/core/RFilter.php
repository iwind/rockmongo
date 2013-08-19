<?php
/**
 * Data filter object
 * 
 * See data types that can be filtered here: http://rockmongo.com/wiki/pluginDevelope?lang=en_us#%23%23%23+Supported+Filters%0D
 *
 * @author Liu <q@yun4s.cn>
 */
class RFilter {
	private static $_filters = array();
	private static $_dataTypes = array();
	
	/**
	 * Apply filters to data
	 *
	 * @param string $dataType Data Type
	 * @param mixed $data Data to be filtered
	 * @param array $params parameters will be passed to "filter callback function"
	 */
	public static function apply($dataType, &$data, array $params = array()) {
		if (empty(self::$_filters[$dataType])) {
			return;
		}
		if (isset(self::$_dataTypes[$dataType]["enabled"]) && !self::$_dataTypes[$dataType]["enabled"]) {
			return;
		}
		$newParams = array( &$data );
		foreach ($params as $param) {
			$newParams[] = $param;
		}
	
		foreach (rock_array_sort(self::$_filters[$dataType], "priority") as $index => $filter) {
			call_user_func_array($filter["callback"], $newParams);
			if (isset(self::$_dataTypes[$dataType]["enabled"]) && !self::$_dataTypes[$dataType]["enabled"]) {
				return;
			}
		}
	}
	
	/**
	 * Add a new filter
	 *
	 * @param string $dataType Data type
	 * @param callback|string $filter Filter function
	 * @param unknown_type $priority
	 */
	public static function add($dataType, $filter, $priority = -1) {
		if ($priority == -1) {
			if (isset(self::$_filters[$dataType])) {
				$priority = count(self::$_filters[$dataType]);
			}
		}
		self::$_filters[$dataType][] = array(
			"callback" => $filter,
			"priority" => $priority
		);
	}
	
	/**
	 * Stop filter chain
	 *
	 * @param string $dataType Data type
	 */
	public static function stop($dataType) {
		self::$_dataTypes[$dataType]["enabled"] = false;
	}
	
	/**
	 * Remove a filter
	 *
	 * @param string $dataType Data type
	 * @param callback|string $filter Filter function
	 */
	public static function remove($dataType, $filter) {
		if (empty(self::$_filters[$dataType])) {
			return;
		}
		$indexes = array();
		foreach (self::$_filters[$dataType] as $index => $_filter) {
			if ($_filter["callback"] == $filter) {
				$indexes[] = $index;
			}
		}
		if (!empty($indexes)) {
			foreach (array_reverse($indexes) as $index) {
				unset(self::$_filters[$dataType][$index]);
			}
		}
	}
}

?>