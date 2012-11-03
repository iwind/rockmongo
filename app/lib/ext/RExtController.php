<?php

define("__BASE__", rtrim(str_replace(DS, "/", dirname($_SERVER["PHP_SELF"])), "/"));//当前主目录路径

class RExtController extends RController {
	function redirect($action, array $params = array(), $js = false) {
		$this->redirectUrl($this->path($action, $params), $js);
		exit();
	}
	
	function redirectUrl($url, $js = false) {
		if ($js) {
			echo '<script language="Javascript">window.location="' . $url . '"</script>';
			exit();
		}
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
	 * Is POST request?
	 *
	 * @return boolean
	 */
	function isPost() {
		return ($_SERVER["REQUEST_METHOD"] == "POST");
	}
	
	/**
	 * Is from AJAX request?
	 *
	 * @return boolean
	 */
	function isAjax() {
		return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
	}
}

?>