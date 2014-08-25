<?php
/**
 * eval source code in PHP or JSON format
 *
 */
class VarEval {
	/**
	 * Source to run
	 *
	 * @var string
	 */
	private $_source;

	/**
	 * Source Format
	 *
	 * @var string
	 */
	private $_format;

	/**
	 * current MongoDB
	 *
	 * @var MongoDB
	 */
	private $_db;

	function __construct($source, $format = "array", MongoDB $db = null) {
		$this->_source = $source;

		$this->_format = $format;
		if (!$this->_format) {
			$this->_format = "array";
		}

		$this->_db = $db;
	}

	/**
	 * execute the code
	 *
	 * @return mixed
	 */
	function execute() {
		if ($this->_format == "array") {
			return $this->_runPHP();
		}
		else if ($this->_format == "json") {
			return $this->_runJson();
		}
	}

	private function _runPHP() {
		$this->_source = "return " . $this->_source . ";";
		if (function_exists("token_get_all")) {//tokenizer extension may be disabled
			$php = "<?php\n" . $this->_source . "\n?>";
			$tokens = token_get_all($php);
			foreach ($tokens as $token) {
				$type = $token[0];
				if (is_long($type)) {
					if (in_array($type, array(
							T_OPEN_TAG,
							T_RETURN,
							T_WHITESPACE,
							T_ARRAY,
							T_LNUMBER,
							T_DNUMBER,
							T_CONSTANT_ENCAPSED_STRING,
							T_DOUBLE_ARROW,
							T_CLOSE_TAG,
							T_NEW,
							T_DOUBLE_COLON
							))) {
						continue;
					}

					if ($type == T_STRING) {
						$func = strtolower($token[1]);
						if (in_array($func, array(
								//keywords allowed
								"mongoid",
								"mongocode",
								"mongodate",
								"mongoregex",
								"mongobindata",
								"mongoint32",
								"mongoint64",
								"mongodbref",
								"mongominkey",
								"mongomaxkey",
								"mongotimestamp",
								"true",
								"false",
								"null",
								"__set_state",
								"stdclass"
							))) {
							continue;
						}
					}
					exit("For your security, we stoped data parsing at '(" . token_name($type) . ") " . $token[1] . "'.");
				}
			}
		}
		return eval($this->_source);
	}

	private function _runJson() {
		$timezone = @date_default_timezone_get();
		date_default_timezone_set("UTC");
		$ret = $this->_db->execute('function () {
			if (typeof(ISODate) == "undefined") {
				function ISODate (isoDateStr) {
				    if (!isoDateStr) {
				        return new Date;
				    }
				    var isoDateRegex = /(\d{4})-?(\d{2})-?(\d{2})([T ](\d{2})(:?(\d{2})(:?(\d{2}(\.\d+)?))?)?(Z|([+-])(\d{2}):?(\d{2})?)?)?/;
				    var res = isoDateRegex.exec(isoDateStr);
				    if (!res) {
				        throw "invalid ISO date";
				    }
				    var year = parseInt(res[1], 10) || 1970;
				    var month = (parseInt(res[2], 10) || 1) - 1;
				    var date = parseInt(res[3], 10) || 0;
				    var hour = parseInt(res[5], 10) || 0;
				    var min = parseInt(res[7], 10) || 0;
				    var sec = parseFloat(res[9]) || 0;
				    var ms = Math.round(sec % 1 * 1000);
				    sec -= ms / 1000;
				    var time = Date.UTC(year, month, date, hour, min, sec, ms);
				    if (res[11] && res[11] != "Z") {
				        var ofs = 0;
				        ofs += (parseInt(res[13], 10) || 0) * 60 * 60 * 1000;
				        ofs += (parseInt(res[14], 10) || 0) * 60 * 1000;
				        if (res[12] == "+") {
				            ofs *= -1;
				        }
				        time += ofs;
				    }
				    return new Date(time);
				};
			};

			function r_util_convert_empty_object_to_string(obj) {
				if (r_util_is_empty(obj)) {
					return "__EMPTYOBJECT__";
				}
				if (typeof(obj) == "object") {
					for (var k in obj) {
						obj[k] = r_util_convert_empty_object_to_string(obj[k]);
					}
				}
				return obj;
			};

			function r_util_is_empty(obj) {
				if (obj == null || typeof(obj) != "object" || (obj.constructor != Object)) {
					return false;
				}
			    for(var k in obj) {
			        if(obj.hasOwnProperty(k)) {
			            return false;
					}
			    }

			    return true;
			};
			var o = ' . $this->_source . '; return r_util_convert_empty_object_to_string(o); }'
		);

		$this->_fixEmptyObject($ret);
		date_default_timezone_set($timezone);
		if ($ret["ok"]) {
			return $ret["retval"];
		}
		return json_decode($this->_source, true);
	}

	private function _fixEmptyObject(&$object) {
		if (is_array($object)) {
			foreach ($object as &$v) {
				$this->_fixEmptyObject($v);
			}
		}
		else if (is_string($object) && $object === "__EMPTYOBJECT__") {
			$object = new stdClass();
		}
	}
}

?>