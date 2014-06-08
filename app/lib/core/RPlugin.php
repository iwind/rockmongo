<?php
/**
 * Plugin object
 *
 * See details here: http://rockmongo.com/wiki/pluginDevelope
 *
 * @author Liu <q@yun4s.cn>
 */
class RPlugin {
	private static $_plugins = array();
	private static $_loaded = false;

	public function onBefore() {

	}

	public function onAfter() {

	}

	/**
	 * Read plugin help
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
	 * Register a plugin
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
	 * Call onBefore() method in plugin
	 *
	 */
	public static function callBefore() {
		$plugins = rock_array_sort(self::$_plugins, "priority");
		foreach ($plugins as $plugin) {
			$plugin["obj"]->onBefore();
		}
	}

	/**
	 * Call onAfter() method in plugin
	 *
	 */
	public static function callAfter() {
		$plugins = rock_array_sort(self::$_plugins, "priority", false);
		foreach ($plugins as $plugin) {
			$plugin["obj"]->onAfter();
		}
	}

	/**
	 * Load all of plugins
	 *
	 * You should put all plugins to app/plugins:
	 * $ROCK-MONGO
	 *   apps/
	 *   	plugins/
	 *   		mapreduce/
	 *   		ace/
	 *			systemjs/
	 *			other plugins ...
	 *
	 * But we also support another deploy way:
	 * $ROCK-MONGO
	 * 	 apps/
	 * plugins/
	 *	 csv/
	 *   sharding/
	 *   other plugins ...
	 */
	public static function load() {
		if (self::$_loaded) {
			return;
		}
		$plugins = array();
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

	/**
	 * Get all plugins
	 *
	 * @return array
	 * @since 1.1.6
	 */
	public static function plugins() {
		$configPlugins = array();
		$plugins = array();
		require(__ROOT__ . DS . "configs" . DS . "rplugin.php");
		if (empty($plugins) || !is_array($plugins)) {
			return $configPlugins;
		}
		foreach ($plugins as $name => $plugin) {
			$dir = __ROOT__ . DS . "plugins" . DS . $name;
			if (!is_dir($dir)) {
				$dir = dirname(dirname(__ROOT__)) . DS . "plugins" . DS . $name;
			}
			$pluginConfig = array(
				"name" => null,
				"dir" => $name,
				"code" => null,
				"author" => null,
				"description" => null,
				"version" => null,
				"url" => null,
				"enabled" => isset($plugin["enabled"]) ? $plugin["enabled"] : false
			);

			$descFile = $dir . "/desc.php";
			if (is_file($descFile)) {
				$config = require($descFile);
				if (isset($config["name"])) {
					$pluginConfig["name"] = $config["name"];
				}
				if (isset($config["code"])) {
					$pluginConfig["code"] = $config["code"];
				}
				if (isset($config["author"])) {
					$pluginConfig["author"] = $config["author"];
				}
				if (isset($config["description"])) {
					$pluginConfig["description"] = $config["description"];
				}
				if (isset($config["version"])) {
					$pluginConfig["version"] = $config["version"];
				}
				if (isset($config["url"])) {
					$pluginConfig["url"] = $config["url"];
				}
			}

			$configPlugins[] = $pluginConfig;
		}

		return $configPlugins;
	}
}

?>