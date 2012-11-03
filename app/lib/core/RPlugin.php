<?php

/**
 * Plugin definiation
 */
class RPlugin {
	private static $_plugins = array();
	private static $_loaded = false;
	
	public function onBefore() {
		
	}
	
	public function onAfter() {
		
	}
	
	/**
	 * read plugin help
	 *
	 */
	public function help() {
		return array(
			"name" => "Default Plugin",
			"author" => "rock",
			"version" => "1.0"
		);
	}
	
	/**
	 * register a plugin
	 * 
	 * @param string $pluginClass plugin class name
	 * @param integer $priority priority
	 * @throws Exception
	 */
	public static function register($pluginClass, $priority = -1) {
		if ($priority == -1) {
			$priority = count(self::$_plugins);
		}
		if (!is_subclass_of($pluginClass, "RPlugin")) {
			throw new Exception("plugin class '{$pluginClass}' must be inherited from RPlugin");
		}
		self::$_plugins[] = array( "obj" => new $pluginClass, "priority" => $priority );
	}
	
	/**
	 * call onBefore() method in plugin
	 *
	 */
	public static function callBefore() {
		$plugins = rock_array_sort(self::$_plugins, "priority"); 
		foreach ($plugins as $plugin) {
			$plugin["obj"]->onBefore();
		}
	}
	
	/**
	 * call onAfter() method in plugin
	 *
	 */
	public static function callAfter() {
		$plugins = rock_array_sort(self::$_plugins, "priority", false); 
		foreach ($plugins as $plugin) {
			$plugin["obj"]->onAfter();
		}
	}
	
	/**
	 * load all of plugins
	 *
	 */
	public static function load() {
		if (self::$_loaded) {
			return;
		}
		require(__ROOT__ . DS . "configs" . DS . "rplugin.php");
		if (empty($plugins) || !is_array($plugins)) {
			return;
		}
		foreach ($plugins as $name => $plugin) {
			if ($plugin["enabled"]) {
				$dir = __ROOT__ . DS . "plugins" . DS . $name;
				if (!is_dir($dir)) {
					$dir = dirname(dirname(__ROOT__)) . DS . "plugins" . DS . $name;
				}
				$initFile = $dir . DS . "init.php";
				if (is_file($initFile)) {
					require $dir . DS . "init.php";
				}
				else {
					trigger_error("could not find initialize file '{$initFile}' for plugin '{$name}', you can disable it in app/configs/rplugin.php");
				}
			}
		}
		
		self::$_loaded = true;
	}
}

?>