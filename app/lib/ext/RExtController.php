<?php

class RExtController extends RController {
	function redirect($action, array $params = array()) {
		header("location:" . $this->path($action, $params));
		exit();
	}
	
	function redirectUrl($url) {
		header("location:{$url}");
		exit();
	}
	
	function path($action, array $params = array()) {
		if (!strstr($action, ".")) {
			$action = $this->name() . "." . $action;
		}
		$url = $_SERVER["PHP_SELF"] . "?action=" . $action;
		if (!empty($params)) {
			$url .= "&" . http_build_query($params);
		}
		return $url;
	}
	
	/**
	 * 判断当前浏览器请求方法是否是POST
	 *
	 * @return boolean
	 */
	function isPost() {
		return ($_SERVER["REQUEST_METHOD"] == "POST");
	}
}

function h($var) {
	echo $var;
}

function url($action, array $params = array()) {
	unset($params["action"]);
	if (!strstr($action, ".")) {
		$action = __CONTROLLER__ . "." . $action;
	}
	$url = $_SERVER["PHP_SELF"] . "?action=" . $action;
	if (!empty($params)) {
		$url .= "&" . http_build_query($params);
	}
	return $url;
}

?>