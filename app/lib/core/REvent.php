<?php
/**
 * Event object
 * 
 * See events list here: http://rockmongo.com/wiki/pluginDevelope?lang=en_us#%23%23%23+Events%0D
 *
 * @author Liu <q@yun4s.cn>
 */
class REvent {
	private static $_events = array();
	private static $_listeners = array();
	
	/**
	 * Dispatch event
	 * 
	 * @param string $event Event name
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
	 * Add a event listener
	 *
	 * @param string $event Event name
	 * @param callback|string $callback Event listener
	 * @param integer $priority Listener priority
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
	 * Stop event propagation
	 *
	 * @param string $event Event name
	 */
	public static function stop($event) {
		self::$_events[$event]["enabled"] = false;
	}
	
	/**
	 * Remove a event listener
	 *
	 * @param string $event Event name
	 * @param callback|string $callback Event listener
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