<?php

class REvent {
	private static $_events = array();
	private static $_listeners = array();
	
	/**
	 * dispatch event
	 * 
	 * @param unknown_type $event
	 * @param array $params
	 */
	public static function dispatch($event, array $params = array()) {
		if (empty(self::$_listeners[$event])) {
			return;
		}
		if (isset(self::$_events[$event]["enabled"]) && !self::$_events[$event]["enabled"]) {
			return;
		}
		foreach (rock_array_sort(self::$_listeners[$event], "priority") as $index => $listener) {
			call_user_func_array($listener["callback"], array($params));
			if (isset(self::$_events[$event]["enabled"]) && !self::$_events[$event]["enabled"]) {
				return;
			}
		}
	}
	
	/**
	 * add a event listener
	 *
	 * @param unknown_type $event
	 * @param unknown_type $callback
	 * @param unknown_type $priority
	 */
	public static function listen($event, $callback, $priority = -1) {
		if ($priority == -1) {
			if (isset(self::$_listeners[$event])) {
				$priority = count(self::$_listeners[$event]);
			}
		}
		self::$_listeners[$event][] = array(
			"callback" => $callback,
			"priority" => $priority
		);
	}
	
	/**
	 * stop event propagation
	 *
	 * @param unknown_type $event
	 */
	public static function stop($event) {
		self::$_events[$event]["enabled"] = false;
	}
	
	/**
	 * remove a event listener
	 *
	 * @param unknown_type $event
	 * @param unknown_type $callback
	 */
	public static function remove($event, $callback) {
		if (empty(self::$_listeners[$event])) {
			return;
		}
		$indexes = array();
		foreach (self::$_listeners[$event] as $index => $listener) {
			if ($listener["callback"] == $callback) {
				$indexes[] = $index;
			}
		}
		if (!empty($indexes)) {
			foreach (array_reverse($indexes) as $index) {
				unset(self::$_listeners[$event][$index]);
			}
		}
	}
}

?>