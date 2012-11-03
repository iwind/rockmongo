<?php

class MMongo {
	/**
	 * throw operation exception 
	 * 
	 * @param mixed $ret the result to be checked
	 */
	public static function checkException($ret) {
		if (!is_array($ret) || !isset($ret["ok"])) {
			return;
		}
		if ($ret["ok"]) {
			return;
		}
		if (isset($ret["assertion"])) {
			exit($ret["assertion"]);
		}
		if (isset($ret["errmsg"])) {
			exit($ret["errmsg"]);
		}
		p($ret);
		exit();
	}
	
	public static function readException($ret) {
		if (!empty($ret["assertion"])) {
			return $ret["assertion"];
		}
		if (!empty($ret["errmsg"])) {
			return $ret["errmsg"];
		}
		return "unknown error";
	}
}