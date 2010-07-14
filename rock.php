<?php

define("DS", DIRECTORY_SEPARATOR);
define("__ROOT__", dirname(__FILE__) . DS . "app");
define("__VERSION__", "0.0.1");
define("nil", "nil_" . uniqid(microtime(true)));
if (!defined("__ENV__")) {
	define("__ENV__", "dev");
}
if (!defined("__LANG__")) {
	define("__LANG__", "zh_cn");
}
if (!defined("__PLATFORM__")) {
	define("__PLATFORM__", "def");
}

//合并$_POST和$_GET
$GLOBALS["ROCK_VARS"] = array_merge($_GET, $_POST);

/**
 * 应用主类
 *
 */
class Rock {
	/**
	 * 启动应用
	 *
	 */
	static function start() {
		$path = x("action");
		if (!$path) {
			$path = "index.index";
		}
		if (!strstr($path, ".")) {
			$path .= ".index";
		}
		if (!preg_match("/(^.*(?:^|\.))(\w+)\.(\w+)$/", $path, $match)) {
			trigger_error("you called an invalid action");
		}
		$name = $match[1] . $match[2];
		$controller = $match[2];
		$action = $match[3];
		$dir = str_replace(".", DS, $name);
		$file = __ROOT__ . DS . "controllers" . DS . $dir . ".php";
		if (!is_file($file)) {
			trigger_error("file '{$file}' of controller '{$controller}' is not found", E_USER_ERROR);
		}
		require($file);
		$class = ucfirst(rock_name_to_java($controller)) . "Controller";
		if (!class_exists($class, false)) {
			$file = realpath($file);
			trigger_error("class '{$class}' is not found in controller file '{$file}'", E_USER_ERROR);
		}
		$obj = new $class;
		if (!($obj instanceof RController)) {
			trigger_error("controller class '{$class}' must be a subclass of RController", E_USER_ERROR);
		}
		define("__CONTROLLER__", $name);
		define("__ACTION__", $action);
		$obj->setAction($action);
		$obj->setPath($file);
		$obj->setName($name);
		$obj->exec();
	}
}

/**
 * 控制器父类
 *
 */
class RController {
	private $_action;
	private $_path;
	private $_name;
	
	/**
	 * 设置当前控制器的动作
	 *
	 * @param string $action 动作名
	 */
	function setAction($action) {
		$this->_action = $action;
	}
	
	/**
	 * 取得当前动作的名称
	 *
	 * @return string
	 */
	function action() {
		return $this->_action;
	}
	
	/**
	 * 设置当前控制器所在的文件路径
	 *
	 * @param string $path 文件路径
	 */
	function setPath($path) {
		$this->_path = $path;
	}
	
	/**
	 * 设置当前控制器的名字
	 *
	 * @param string $name 控制器名
	 */
	function setName($name) {
		$this->_name = $name;
	}
	
	/**
	 * 取得当前控制器名称
	 *
	 * @return string
	 */
	function name() {
		return $this->_name;
	}
	
	/**
	 * 设置在控制器动作执行前执行的方法
	 *
	 */
	function onBefore() {
		
	}
	
	/**
	 * 设置在控制器动作执行后执行的方法
	 *
	 */
	function onAfter() {
		
	}
	
	/**
	 * 执行当前动作
	 *
	 */
	function exec() {
		$this->onBefore();
		
		$method = "do" . $this->_action;
		if (!method_exists($this, $method)) {
			trigger_error("can not find action '{$this->_action}' in class '" . get_class($this) . "'", E_USER_ERROR);
		}
		$ret = $this->$method();
		if (is_object($ret) && ($ret instanceof RView)) {
			$ret->display();
		}
		
		$this->onAfter();
	}
	
	/**
	 * 显示动作对应的视图
	 *
	 * @param string $action 动作名，如果不为NULL，则使用此动作名查找视图
	 */
	function display($action = null) {
		if (is_null($action)) {
			$action = $this->_action;
		}
		$view = __ROOT__ . "/views/" . str_replace(".", "/", $this->_name) . "/{$action}.php";
		if (is_file($view)) {
			extract(get_object_vars($this), EXTR_OVERWRITE);
			require($view);
		}
	}
}

/**
 * 模型父类
 *
 */
class RModel {
	
}

/**
 * 视图父类
 *
 */
class RView {
	/**
	 * 显示视图
	 *
	 */
	function display() {
		
	}
}

/**
 * 打印数据的内容
 * 
 * 函数名为Print的缩写
 * 
 * <code>
 * p($obj);
 * p($obj1, $obj2, ...)
 * </code>
 * 
 * 从1.0.2开始，在命令行下不再显示<xmp>和</xmp>
 * 
 * @param mixed $data1 要被打印的数据
 */
function p($data1 = null) {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (is_null($arg)) {
			$arg = "NULL";
		}
		else if (is_bool($arg)) {
			$arg = $arg ? "TRUE" : "FALSE";
		}
		echo "<xmp>\n" . print_r($arg, true) . "\n</xmp>\n";
	}
}

/**
 * 获取或设置参数对应的值
 *
 * @param string|array $name 一个或一组参数名
 * @param mixed $value 如果不为null，则将此值赋给参数
 * @return mixed
 */
function x($name, $value = nil) {
	if ($value != nil) {
		$GLOBALS["ROCK_VARS"][$name] = $value;
		return $value;
	}
	if (isset($GLOBALS["ROCK_VARS"][$name])) {
		return rock_filter_param($GLOBALS["ROCK_VARS"][$name]);
	}
	return null;
}

/**
 * 过滤参数
 *
 * @param string $param 参数
 * @return mixed
 */
function rock_filter_param($param) {
	if (!is_array($param) && !is_object($param)) {
		return htmlspecialchars(trim($param));
	}
	foreach ($param as $key => $value) {
		$param[$key] = rock_filter_param($value);
	}
	return $param;
}

/**
 * 获取参数的值
 * 
 * 与x($name)不同的是，该函数获取的参数不会被自动过滤（通过trim和htmlspecialchars）
 *
 * @param string $name 参数名
 * @return mixed
 * @see x
 */
function xn($name = nil) {
	if ($name == nil) {
		return $GLOBALS["ROCK_VARS"];
	}
	if (isset($GLOBALS["ROCK_VARS"][$name])) {
		return $GLOBALS["ROCK_VARS"][$name];
	}
	return null;
}

/**
 * 获取参数的值，并转化为整数
 *
 * @param string $name 参数名
 * @return integer
 * @see x
 */
function xi($name) {
	return intval(x($name));
}

/**
 * 导入文件
 * 
 * 比如：
 * import("classes.MyClass");
 * import("models.TLog");
 * import("@.RHttpRequest");//引入当前目录下的RHttpRequest类
 * import("@.@.RHttpRequest");//引入上一级目录下的RHttpRequest类
 *
 * @param string $class 类文件名（包含完整的地址）
 */
function import($class) {
	$className = substr($class, strrpos($class, ".") + 1);
	if (class_exists($className, false)) {
		return;
	}
	
	$file = null;
	if (strstr($class, "@")) {
		$trace = debug_backtrace();
		$calledFile = $trace[0]["file"];
		$count = substr_count($class, "@");
		$dir = $calledFile;
		for ($i = 0; $i < $count; $i ++) {
			$dir = dirname($dir);
		}
		$file = $dir . "/" . str_replace(".", "/", str_replace("@.", "", $class)) . ".php";
	}
	else {
		$file = __ROOT__ . "/" . str_replace(".", "/", $class) . ".php";
	}
	if (empty($GLOBALS["ROCK_LOADED"]) || !in_array($file, $GLOBALS["ROCK_LOADED"])) {
		require($file);
		$GLOBALS["ROCK_LOADED"][] = $file;
	}
}

/**
 * 查找配置
 * 
 * 配置都有环境和平台限制
 * 
 * o("@.config") - 查找当前目录下的config.dev@def.php配置
 * o("@.config.servers") - 查找config配置，并取出其中的servers键对应的值
 *
 * @param string $config 配置名
 * @return mixed
 */
function o($config) {
	if (isset($GLOBALS["ROCK_CONFIGS"][$config])) {
		return $GLOBALS["ROCK_CONFIGS"][$config];
	}
	
	$file = null;
	$option = null;
	$pieces = explode(".", $config);
	if (strstr($config, "@")) {
		$trace = debug_backtrace();
		$calledFile = $trace[0]["file"];
		$count = substr_count($config, "@");
		$dir = $calledFile;
		for ($i = 0; $i < $count; $i ++) {
			unset($pieces[$i]);
			$dir = dirname($dir);
		}
		$filename = array_shift($pieces);
		$file = $dir . "/" . $filename . "." . __ENV__ . "@" . __PLATFORM__ . ".php";
	}
	else {
		$filename = array_shift($pieces);
		$file = __ROOT__ . "/" . $filename . "." . __ENV__ . "@" . __PLATFORM__ . ".php";
	}
	
	$options = $pieces;
	$ret = require($file);

	//有没有子选项
	if (empty($options)) {
		$GLOBALS["ROCK_CONFIGS"][$config] = $ret;
		return $ret;
	}
	if (!is_array($ret)) {
		$GLOBALS["ROCK_CONFIGS"][$config] = $ret;
		return null;
	}
	$ret = rock_array_get($ret, $options);
	
	$GLOBALS["ROCK_CONFIGS"][$config] = $ret;
	return $ret;
}

/**
 * 将名称转换为Java的编程风格
 *
 * 首字母小写，其余单词的首字母大写<br/>
 * load_xml_config --> loadXmlConfig
 * 
 * @param string $name 名称
 * @return string
 */
function rock_name_to_java($name) {
	$name = preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name);
	return $name;
}

/**
 * 取得一个数组中中某个键的值
 *
 * @param array $array 数组
 * @param array|string $keys 键，可以是多级的，比如a.b.c
 * @return mixed
 * @see rock_array_set
 */	
function rock_array_get(array $array, $keys) {
	if (is_array($keys) && empty($keys)) {
		return $array;
	}
	if (!is_array($keys)) {
		if (strstr($keys, "`")) {
			$keys = preg_replace("/`(.+)`/Ue", "str_replace('.','\.','\\1')", $keys);
		}
		$keys = preg_split("/(?<!\\\)\./", $keys);
	}
	if (count($keys) == 1) {
		$firstKey = array_pop($keys);
		$firstKey = str_replace("\.", ".", $firstKey);
		return array_key_exists($firstKey, $array)?$array[$firstKey]:null;
	}
	$lastKey = array_pop($keys);
	$lastKey = str_replace("\.", ".", $lastKey);
	$lastArray = $array;
	foreach ($keys as $key) {
		$key = str_replace("\.", ".", $key);
		if (is_array($lastArray) && array_key_exists($key, $lastArray)) {
			$lastArray = $lastArray[$key];
		}
		else {
			return null;
		}
	}
	
	return (is_array($lastArray) && array_key_exists($lastKey, $lastArray))? $lastArray[$lastKey] : null;
}


/**
 * 设置一个数组中某个键的值，并返回设置后的值
 * 
 * 对原有的数组没有影响
 *
 * @param array $array 数组
 * @param array|string $keys 键，可以是多级的，比如a.b.c
 * @param mixed $value 新的键值
 * @return array
 * @see rock_array_get
 */
function rock_array_set(array $array, $keys, $value) {
	if (is_array($keys) && empty($keys)) {
		return $array;
	}
	if (!is_array($keys)) {
		if (strstr($keys, "`")) {
			$keys = preg_replace("/`(.+)`/Ue", "str_replace('.','\.','\\1')", $keys);
		}
		$keys = preg_split("/(?<!\\\)\./", $keys);
	}
	if (count($keys) == 1) {
		$firstKey = array_pop($keys);
		$firstKey = str_replace("\.", ".", $firstKey);
		$array[$firstKey] = $value;
		return $array;
	}
	$lastKey = array_pop($keys);
	$lastKey = str_replace("\.", ".", $lastKey);
	$lastConfig = &$array;
	foreach ($keys as $key) {
		$key = str_replace("\.", ".", $key);
		if (!isset($lastConfig[$key]) || !is_array($lastConfig[$key])) {
			$lastConfig[$key] = array();
		}
		$lastConfig = &$lastConfig[$key];
	}
	$lastConfig[$lastKey] = $value;
	return $array;
}

/**
 * 从一个数组的值中选取key做当前数组的key
 * 
 * <code>
 * $array = array(
 *   array("a" => 11, "b" => 12),
 *   array("a" => 21, "b" => 22)
 *   //...
 * );
 * 
 * $array2 = rock_array_combine($array, "a", "b");
 * </code>
 * 
 * $array2就变成了：
 * <code>
 * array(
 *   11 => 12,
 *   21 => 22
 * );
 * </code>
 * 
 * 如果$valueName没有值，则是把当前元素值当成value:
 * 
 * <code>
 * $array2 = rock_array_combine($array, "a");
 * 
 * array(
 *   11 => array("a" => 11, "b" => 12),
 *   21 => array("a" => 21, "b" => 22)
 * );
 * </code>
 * 
 * 支持以点(.)符号连接的多层次keyName和valueName：
 * - rock_array_combine($array, "a.b", "a.c"); 
 * 即重新构成了一个以$array[n][a][b]为键，以$array[n][a][c]为值的数组，其中n是数组的索引
 *
 * @param array $array 二维数组
 * @param integer|string $keyName 选取的key名称
 * @param integer|string $valueName 选取的值名称
 * @return array
 * @since 1.0
 */
function rock_array_combine($array, $keyName, $valueName = null) {
	$ret = array();
	foreach ($array as $row) {
		if (is_array($row)) {
			$keyValue = rock_array_get($row, $keyName);
			$value = is_null($valueName) ? $row : rock_array_get($row, $valueName);
			if ($keyValue) {
				$ret[$keyValue] = $value;
			}
			else {
				$ret[] = $value;
			}
		}
	}
	return $ret;
}

/**
 * 获取语言消息
 *
 * @param string $code 消息代号
 * @return mixed
 */
function rock_lang($code) {
	if (!isset($GLOBALS["ROCK_LANGS"])) {
		$file = __ROOT__ . "/langs/" . __LANG__ . "/message.php";
		require($file);
		if (isset($message) && is_array($message)) {
			$GLOBALS["ROCK_LANGS"] = $message;
		}
		else {
			$GLOBALS["ROCK_LANGS"] = array();
		}
	}
	return isset($GLOBALS["ROCK_LANGS"][$code]) ? $GLOBALS["ROCK_LANGS"][$code] : null;
}

?>