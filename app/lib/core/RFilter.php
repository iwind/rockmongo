<?php

class RFilter {
	private static $_filters = array();
	private static $_dataTypes = array();
	
	/**
	 * apply filters to data
	 *
	 * @param unknown_type $dataType
	 * @param unknown_type $data
	 * @param array $params
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
	 * add a new filter
	 *
	 * @param unknown_type $dataType
	 * @param unknown_type $filter
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
	 * stop filter chain
	 *
	 * @param unknown_type $dataType
	 */
	public static function stop($dataType) {
		self::$_dataTypes[$dataType]["enabled"] = false;
	}
	
	/**
	 * remove a filter
	 *
	 * @param unknown_type $dataType
	 * @param unknown_type $filter
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