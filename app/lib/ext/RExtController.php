<?php

define("__BASE__", rtrim(str_replace(DS, "/", dirname($_SERVER["PHP_SELF"])), "/"));//Current App Directory

class RExtController extends RController {
	/**
	 * Redirect to another action
	 * 
	 * @param string $action Action name
	 * @param array $params Parameters that will be passed
	 * @param boolean $js Whether use javascript
	 */
	function redirect($action, array $params = array(), $js = false) {
		$this->redirectUrl($this->path($action, $params), $js);
		exit();
	}
	
	/**
	 * Redirect to another URL
	 * 
	 * @param string $url URL
	 * @param string $js Whether use javascript
	 */
	function redirectUrl($url, $js = false) {
		if ($js) {
			echo '<script language="Javascript">window.location="' . $url . '"</script>';
			exit();
		}
		header("location:{$url}");
		exit();
	}
	
	/**
	 * Contruct a path from action name
	 * 
	 * @param string $action Action name, like "update", "update.go"
	 * @param array $params Parameters
	 * @return string
	 */
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
	 * Determine if it is a POST request
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