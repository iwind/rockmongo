<?php
ob_start();
session_write_close();
session_start();

set_time_limit(0);

import("lib.ext.RExtController");
import("funcs.functions", false);
import("funcs.render", false);
import("funcs.rock", false);

import("models.MMongo");
import("models.MServer");
import("models.MUser");
import("models.MDb");
import("models.MCollection");

import("classes.VarExportor");
import("classes.VarEval");

//filter $MONGO
if (class_exists("RFilter")) {
	global $MONGO;
	RFilter::apply("CONFIG_FILTER", $MONGO);
}

class BaseController extends RExtController {
	/**
	 * Enter description here...
	 *
	 * @var MServer
	 */
	protected $_server;

	/**
	 * Enter description here ...
	 *
	 * @var RMongo
	 */
	protected $_mongo;

	/**
	 * current session user
	 *
	 * @var MUser
	 */
	protected $_admin;
	protected $_logQuery = false;

	/** called before any actions **/
	public function onBefore() {
		global $MONGO;

		//exception handler
		set_exception_handler(array($this, "exceptionHandler"));

		$this->_admin = MUser::userInSession();
		if (!$this->_admin) {
			//if user is loged in?
			$server = MServer::serverWithIndex(xi("host"));

			//filter server plugins
			if (class_exists("RFilter")) {
				RFilter::apply("SERVER_FILTER", $server);
			}

			//if auth is disabled
			if ($server && !$server->mongoAuth() && !$server->controlAuth()) {
				MUser::login("rockmongo_memo", "rockmongo_memo", xi("host"), "admin", 10800);
				$this->_admin = MUser::userInSession();
			}
			else {
				$this->redirect("login.index", array( "host" =>  xi("host")));
			}
		}
		if (!$this->_admin->validate()) {
			$this->redirect("login.index", array( "host" => $this->_admin->hostIndex() ));
		}
		$this->_server = MServer::serverWithIndex($this->_admin->hostIndex());
		$this->_mongo = $this->_server->mongo();

		//log query
		if (isset($MONGO["features"]["log_query"]) && $MONGO["features"]["log_query"] == "on") {
			$this->_logQuery = true;
		}

		//render header
		if (!$this->isAjax()) {
			render_view("header");
		}
	}

	/** called after action call **/
	public function onAfter() {
		if (!$this->isAjax()) {
			render_view("footer");
		}
	}

	/**
	 * handle exception in runtime
	 *
	 * @param Exception $exception exception to handle
	 */
	public function exceptionHandler($exception) {
		$message = $exception->getMessage();
		render_view("exception", array( "message" => $message ));
		render_view("footer");
		exit();
	}

	/**
	 * convert variable from string values
	 *
	 * @param MongoDB $mongodb MongoDB instance
	 * @param string $dataType data type
	 * @param string $format string format
	 * @param string $value string value
	 * @param integer $integerValue integer value
	 * @param long $longValue long value
	 * @param string $doubleValue float value
	 * @param string $boolValue boolea value
	 * @param string $mixedValue mixed value (array or object)
	 * @return mixed
	 * @throws Exception
	 */
	protected function _convertValue($mongodb, $dataType, $format, $value, $integerValue, $longValue, $doubleValue, $boolValue, $mixedValue) {
		$realValue = null;
		switch ($dataType) {
			case "integer":
				if (class_exists("MongoInt32")) {
					$realValue = new MongoInt32($integerValue);
				}
				else {
					$realValue = intval($realValue);
				}
				break;
			case "long":
				if (class_exists("MongoInt64")) {
					$realValue = new MongoInt64($longValue);
				}
				else {
					$realValue = $longValue;
				}
				break;
			case "float":
			case "double":
				$realValue = doubleval($doubleValue);
				break;
			case "string":
				$realValue = $value;
				break;
			case "boolean":
				$realValue = ($boolValue == "true");
				break;
			case "null":
				$realValue = NULL;
				break;
			case "mixed":
				$eval = new VarEval($mixedValue, $format, $mongodb);
				$realValue = $eval->execute();
				if ($realValue === false) {
					throw new Exception("Unable to parse mixed value, just check syntax!");
				}
				break;
		}
		return $realValue;
	}

	protected function _encodeJson($var) {
		if (function_exists("json_encode")) {
			return json_encode($var);
		}
		import("classes.Services_JSON");
		$service = new Services_JSON();
		return $service->encode($var);
	}

	/**
	 * Output variable as JSON
	 *
	 * @param mixed $var variable
	 * @param boolean $exit if exit after output
	 */
	protected function _outputJson($var, $exit = true) {
		echo $this->_encodeJson($var);
		if ($exit) {
			exit();
		}
	}

	protected function _decodeJson($var) {
		import("classes.Services_JSON");
		$service = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$ret = array();
		$decode = $service->decode($var);
		return $decode;
	}

	/**
	 * Export var as string then highlight it.
	 *
	 * @param mixed $var variable to be exported
	 * @param string $format data format, array|json
	 * @param boolean $label if add label to field
	 * @return string
	 */
	protected function _highlight($var, $format = "array", $label = false) {
		import("classes.VarExportor");
		$exportor = new VarExportor($this->_mongo->selectDB("admin"), $var);
		$varString = null;
		$highlight = true;
		switch ($this->_server->docsRender()) {
			case "default":
				$varString = $exportor->export($format, $label);
				break;
			case "plain":
				$varString = $exportor->export($format, false);
				$label = false;
				$highlight = false;
				break;
			default:
				$varString = $exportor->export($format, $label);
				break;
		}
		$string = null;
		if ($highlight) {
			if ($format == "array") {
				$string = highlight_string("<?php " . $varString, true);
				$string = preg_replace("/" . preg_quote('<span style="color: #0000BB">&lt;?php&nbsp;</span>', "/") . "/", '', $string, 1);
			}
			else {
				$string =  json_format_html($varString);
			}
		}
		else {
			$string = "<div><xmp style='width:600px;overflow:auto'>" . $varString . "</xmp></div>";
		}
		if ($label) {
			$id = addslashes(isset($var["_id"]) ? rock_id_string($var["_id"]) : "");
			$string = preg_replace_callback("/(['\"])rockfield\\.(.+)\\.rockfield(['\"])/U", create_function('$match', '	$fields = explode(".rockfield.", $match[2]);
					return "<span class=\"field\" field=\"" . implode(".", $fields) . "\">" . $match[1] . array_pop($fields) . $match[3] . "</span>";'), $string);
			$string = preg_replace_callback("/__rockmore\\.(.+)\\.rockmore__/U", create_function('$match', '
			$field = str_replace("rockfield.", "", $match[1]);
			return "<a href=\"#\" onclick=\"fieldOpMore(\'" . $field . "\',\'' . $id . '\');return false;\" title=\"More text\">[...]</a>";'), $string);
		}
		return $string;
	}

	/**
	 * Enter description here...
	 *
	 * @param MongoDB $db
	 * @param unknown_type $from
	 * @param unknown_type $to
	 * @param unknown_type $index
	 */
	protected function _copyCollection($db, $from, $to, $index = true) {
		if ($index) {
			$indexes = $db->selectCollection($from)->getIndexInfo();
			foreach ($indexes as $index) {
				$options = array();
				if (isset($index["unique"])) {
					$options["unique"] = $index["unique"];
				}
				if (isset($index["name"])) {
					$options["name"] = $index["name"];
				}
				if (isset($index["background"])) {
					$options["background"] = $index["background"];
				}
				if (isset($index["dropDups"])) {
					$options["dropDups"] = $index["dropDups"];
				}
				$db->selectCollection($to)->ensureIndex($index["key"], $options);
			}
		}
		$ret = $db->execute('function (coll, coll2) { return db.getCollection(coll).copyTo(coll2);}', array( $from, $to ));
		return $ret["ok"];
	}

	protected function _logFile($db, $collection) {
		$logDir = dirname(__ROOT__) . DS . "logs";
		return $logDir . DS . urlencode($this->_admin->username()) . "-query-" . urlencode($db) . "-" . urlencode($collection) . ".php";
	}

	/**
	 * remember data format choice
	 *
	 * @param string $format data format
	 */
	protected function _rememberFormat($format) {
		setcookie("rock_format", $format, time() + 365 * 86400, "/");
	}
}



?>