<?php

/**
 * pick values from an array
 *
 * @param array $array input array
 * @param string|integer $key key
 * @param boolean $keepIndex if keep index
 * @return array
 * @since 1.0
 */
function rock_array_pick($array, $key, $keepIndex = false) {
	if (!is_array($array)) {
		return array();
	}
	$ret = array();
	foreach ($array as $index => $row) {
		if (is_array($row)) {
			$value = rock_array_get($row, $key);
			if ($keepIndex) {
				$ret[$index] = $value;
			}
			else {
				$ret[] = $value;
			}
		}
	}
	return $ret;
}

/**
 * sort multiple-array by key
 *
 * @param array $array array to sort
 * @param mixed $key string|array
 * @param boolean $asc if asc
 * @return array
 */
function rock_array_sort(array $array, $key = null, $asc = true) {
	if (empty($array)) {
		return $array;
	}
	if (empty($key)) {
		$asc ? asort($array) : arsort($array);
	}
	else {
		$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil] = $key;
		uasort($array, 
			$asc ? create_function('$p1,$p2', '$key=$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1>$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
			:
			create_function('$p1,$p2', '$key=$GLOBALS["rock_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1<$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
		);
		unset($GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil]);
	}	
	return $array;
}

/**
 * read cookie
 *
 * @param string $name Cookie Name
 * @param mixed $default default value
 * @return mixed
 */
function rock_cookie($name, $default = null) {
	return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}

/**
 * Construct a real ID from a mixed ID
 *
 * @param mixed $id id in mixed type
 */
function rock_real_id($id) {
	if (is_object($id)) {
		return $id;
	}
	if (preg_match("/^rid_(\\w+):(.+)$/", $id, $match)) {
		$type = $match[1];
		$value = $match[2];
		switch ($type) {
			case "string":
				return $value;
			case "float":
				return floatval($value);
			case "double":
				return doubleval($value);
			case "boolean":
				return (bool)$value;
			case "integer":
				return intval($value);
			case "long":
				return doubleval($value);
			case "object":
				return new MongoId($value);
			case "MongoInt32":
				return new MongoInt32($value);
			case "MongoInt64":
				return new MongoInt64($value);
			case "mixed":
				$eval = new VarEval(base64_decode($value));
				$realId = $eval->execute();
				return $realId;
		}
		return;
	}
	
	if (is_numeric($id)) {
		return floatval($id);
	}
	if (preg_match("/^[0-9a-z]{24}$/i", $id)) {
		return new MongoId($id);
	}
	return $id;
}

/**
 * Format ID to string
 *
 * @param mixed $id object ID
 */
function rock_id_string($id) {
	if (is_object($id) && $id instanceof MongoId) {
		return "rid_object:" . $id->__toString();
	}
	if (is_object($id)) {
		return "rid_" . get_class($id) . ":" . $id->__toString();
	}
	if (is_scalar($id)) {
		return "rid_" . gettype($id) . ":" . $id;
	}
	return "rid_mixed:" . base64_encode(var_export($id, true));
}

/**
 * Output a variable
 *
 * @param mixed $var a variable
 */
function h($var) {
	if (is_array($var)) {
		echo json_encode($var);
		return;
	}
	if (is_null($var)) {
		echo "NULL";
		return;
	}
	if (is_bool($var)) {
		echo $var ? "TRUE":"FALSE";
		return;
	}
	echo $var;
}
/**
 * Output a variable escaped
 *
 * @param mixed $var a variable
 */
function h_escape($var) {
	if (is_array($var)) {
		echo htmlspecialchars(json_encode($var));
		return;
	}
	if (is_null($var)) {
		echo "";
		return;
	}
	if (is_bool($var)) {
		echo $var;
		return;
	}
	echo htmlspecialchars($var);
}

/**
 * Output a I18N message
 *
 * @param string $var message key
 */
function hm($var) {
	echo rock_lang($var);
}

/**
 * Load all lanugages
 *
 * @return array
 */
function rock_load_languages() {
	$dir = __ROOT__ . DS . "langs";
	$handler = opendir($dir);
	$languages = array();
	while(($file = readdir($handler)) !== false) {
		$langDir = $dir . DS . $file;
		if (is_dir($langDir) && preg_match("/^\\w+_\\w+$/", $file)) {
			require $langDir . DS . "message.php";
			$languages[$file] = array( "code" => $file,  "name" => $message["TRANSLATION_NAME"], "id" => $message["TRANSLATION_ID"]);
		}
	}
	closedir($handler);
	$languages = rock_array_sort($languages, "id");
	return rock_array_combine($languages, "code", "name");
}

/**
 * Get current path of theme 
 *
 * @return string
 * @since 1.1.0
 */
function rock_theme_path() {
	global $MONGO;
	if (isset($MONGO["features"]["theme"])) {
		return "themes/" . $MONGO["features"]["theme"];
	}
	else {
		return "themes/default";
	}
}

/**
 * Get real value from one string
 *
 * @param MongoDB $mongodb current mongodb
 * @param integer $dataType data type
 * @param string $format data format
 * @param string $value value in string format
 * @return mixed
 * @throws Exception
 * @since 1.1.0
 */
function rock_real_value($mongodb, $dataType, $format, $value) {
	$realValue = null;
	switch ($dataType) {
		case "integer":
		case "float":
		case "double":
			$realValue = doubleval($value);
			break;
		case "string":
			$realValue = $value;
			break;
		case "boolean":
			$realValue = ($value == "true");
			break;
		case "null":
			$realValue = NULL;
			break;
		case "mixed":
			$eval = new VarEval($value, $format, $mongodb);
			$realValue = $eval->execute();
			if ($realValue === false) {
				throw new Exception("Unable to parse mixed value, just check syntax!");
			}
			break;
	}
	return $realValue;
}

?>