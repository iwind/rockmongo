<?php
/**
 * Render html tag beginning
 *
 * @param string $name tag name
 * @param array $attrs tag attributes
 */
function render_begin_tag($name, array $attrs = array()) {
	$tag = "<{$name}";
	foreach ($attrs as $key => $value) {
		$tag .= " {$key}=\"{$value}\"";
	}
	$tag .= ">";
	echo $tag;
}

/**
 * Render select element
 *
 * @param string $name select name
 * @param array $options data options
 */
function render_select($name, array $options, $selectedIndex, array $attrs = array()) {
	$attrs["name"] = $name;
	render_begin_tag("select", $attrs);
	$select = "";
	foreach ($options as $key => $value) {
		$select .= "<option value=\"{$key}\"";
		if ($key == $selectedIndex) {
			$select .= " selected=\"selected\"";
		}
		$select .= ">" . $value . "</option>";
	}
	$select .= "</select>";
	echo $select;
}

/**
 * Construct a url from action and it's parameters
 *
 * @param string $action action name
 * @param array $params parameters
 * @return string
 */
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

/**
 * Render navigation
 *
 * @param string $db database name
 * @param string|null $collection collection name
 * @param boolean $extend if extend the parameters
 */
function render_navigation($db, $collection = null, $extend = true) {
	$dbpath = url("db.index", array("db" => $db));
	$navigation = '<a href="' . url("server.databases") . '"><img src="' . rock_theme_path() . '/images/world.png" width="14" align="absmiddle"/> ' . rock_lang("databases") . '</a> &raquo; <a href="' .$dbpath . '"><img src="' . rock_theme_path() . '/images/database.png" width="14" align="absmiddle"/> ' . $db . "</a>";
	if(!is_null($collection)) {
		$navigation .= " &raquo; <a href=\"" . url("collection.index", $extend ? xn() : array( "db" => $db, "collection" => $collection )) . "\">";
		$navigation .= '<img src="' . rock_theme_path() . '/images/' . r_get_collection_icon($collection) . '.png" width="14" align="absmiddle"/> ';
		$navigation .= $collection . "</a>";
	}
	echo $navigation;
}

/**
 * Render quick links on top-bar
 *
 */
function render_manual_items() {
	$items = array(
		'<a href="http://docs.mongodb.org/manual/reference/operators/" target="_blank">' . rock_lang("querying") . '</a>',
		'<a href="http://docs.mongodb.org/manual/applications/update/" target="_blank">' . rock_lang("updating") . '</a>',
		'<a href="http://docs.mongodb.org/manual/reference/command/" target="_blank">' . rock_lang("commands") . '</a>',
		'<a href="http://api.mongodb.org/js/" target="_blank">' . rock_lang("jsapi") . '</a>',
		'<a href="http://www.php.net/manual/en/book.mongo.php" target="_blank">' . rock_lang("phpmongo") . '</a>'
	);

	//plugins
	if (class_exists("RFilter")) {
		RFilter::apply("MANUAL_MENU_FILTER", $items);
	}

	foreach ($items as $item) {
		echo $item . "<br/>";
	}
}

/**
 * Render server operations
 *
 * @param string|null $currentAction current operation action
 * @since 1.1.0
 */
function render_server_menu($currentAction = null) {
	$menuItems = array(
		array( "action" => "server.index", "name" => rock_lang("server")),
		array( "action" => "server.status", "name" => rock_lang("status")),
		array( "action" => "server.databases", "name" => rock_lang("databases")),
		array( "action" => "server.processlist", "name" => rock_lang("processlist")),
		array( "action" => "server.command", "params" => array("db"=>xn("db")), "name" => rock_lang("command")),
		array( "action" => "server.execute", "params" => array("db"=>xn("db")), "name" => rock_lang("execute")),
		array( "action" => "server.replication", "name" => rock_lang("master_slave")),
	);

	//plugin
	if (class_exists("RFilter")) {
		RFilter::apply("SERVER_MENU_FILTER", $menuItems);
	}

	$string = "";
	$count = count($menuItems);
	foreach ($menuItems as $index => $op) {
		$string .= '<a href="' . url($op["action"], isset($op["params"]) ? $op["params"] : array()) . '"';
		if (__CONTROLLER__ . "." . __ACTION__ == $op["action"] || $currentAction == $op["action"]) {
			$string .= ' class="current"';
		}
		foreach ($op as $attrName => $attrValue) {
			if (preg_match("/^attr\\.(\\w+)/", $attrName, $match)) {
				$string .= " " . $match[1] . "=\"" . $attrValue . "\"";
			}
		}
		$string .= ">" . $op["name"] . "</a>";
		if ($index < $count - 1) {
			$string .= " | ";
		}
	}
	echo $string;
}

/**
 * Render database operations
 *
 * @param string $dbName database name
 * @since 1.1.0
 */
function render_db_menu($dbName) {
	$menuItems = array(
		array( "action" => "db.index", "params" => array("db"=>$dbName), "name" => rock_lang("statistics") ),
		array( "action" => "db.newCollection", "params" => array("db"=>$dbName), "name" => rock_lang("create_collection") ),
		array( "action" => "server.command", "params" => array("db"=>$dbName), "name" => rock_lang("command") ),
		array( "action" => "server.execute", "params" => array("db"=>$dbName), "name" => rock_lang("execute") ),
		array( "action" => "db.dbTransfer", "params" => array("db"=>$dbName), "name" => rock_lang("transfer") ),
		array( "action" => "db.dbExport", "params" => array("db"=>$dbName, "can_download"=>1), "name" => rock_lang("export"), "can_download" => 1 ),
		array( "action" => "db.dbImport", "params" => array("db"=>$dbName), "name" => rock_lang("import") ),
		array( "action" => "db.profile", "params" => array("db"=>$dbName), "name" => rock_lang("profile")),
		array( "action" => "db.repairDatabase", "params" => array("db"=>$dbName), "name" => rock_lang("repair"), "attr.onclick" => "return window.confirm('" . rock_lang("repairdbmsg") . " {$dbName}?');" ),
		array( "action" => "db.auth", "params" => array("db"=>$dbName), "name" => rock_lang("authentication") ),
		array( "action" => "db.dropDatabase", "params" => array("db"=>$dbName), "name" => rock_lang("drop"), "attr.onclick" => "return window.confirm('" . rock_lang("dropwarning") . " " . $dbName . "? " . rock_lang("dropwarning2") . "');")
	);

	//plugin
	if (class_exists("RFilter")) {
		RFilter::apply("DB_MENU_FILTER", $menuItems, array( "dbName" => $dbName )  );
	}

	$displayCount = 7;
	$hasMore = false;

	$string = "";
	$count = count($menuItems);
	foreach ($menuItems as $index => $op) {
		if ($index >= $displayCount && !$hasMore) {
			$hasMore = true;
			$string .= "<a href=\"#\" onclick=\"showMoreMenus(this);return false;\">" . rock_lang("more") . " &raquo;</a>";
			$string .= "<div class=\"menu\">";
		}

		if (is_string($op)) {
			if ($op == "-") {
				$string .= "-----------";
			}
			else {
				$string .= $op;
			}
		}
		else if (!empty($op["action"])) {
			$string .= '<a href="' . url($op["action"], isset($op["params"]) ? $op["params"] : array()) . '"';
			if (__CONTROLLER__ . "." . __ACTION__ == $op["action"]) {
				$string .= ' class="current"';
			}
			foreach ($op as $attrName => $attrValue) {
				if (preg_match("/^attr\\.(\\w+)/", $attrName, $match)) {
					$string .= " " . $match[1] . "=\"" . $attrValue . "\"";
				}
			}
			$string .= ">" . $op["name"] . "</a>";
		}
		else {
			if (!empty($op["url"])) {
				$string .= "<a href=\"" . $op["url"] . "\" target=\"_blank\">";
			}
			$string .= $op["name"];
			if (!empty($op["url"])) {
				$string .= "</a>";
			}
		}
		if ($hasMore) {
			$string .= "<br/>";
		}
		else {
			if ($index < $count - 1) {
				$string .= " | ";
			}
		}
	}
	if ($hasMore) {
		$string .= "</div>";
	}
	echo $string;
}


/**
 * Render collection operations
 *
 * Menu definition:
 * - array ( "action" => "ACTION", "params" => array( ... ), "name" => "NAME" )
 * - array ( "url" => "http://....", "name" => "NAME" )
 * - - //separator line
 *
 * @param string $dbName database name
 * @param string $collectionName collection name
 * @since 1.1.0
 */
function render_collection_menu($dbName, $collectionName) {
	$params = xn();
	$exportParams = $params;
	$exportParams["can_download"] = 1;
	$menuItems = array(
		array( "action" => "collection.createRow", "params" => $params, "name" => rock_lang("insert") ),
		array( "action" => "collection.clearRows", "params" => $params, "name" => rock_lang("clear"), "attr.onclick" => "return window.confirm('Are you sure to delete all records in collection \\'" . $collectionName . "\\'?');" ),
		array( "action" => "#", "params" =>  array(), "name" => rock_lang("new_field"), "attr.onclick" => "fieldOpNew();return false;" ),
		array( "action" => "collection.collectionStats", "params" => $params, "name" => rock_lang("statistics") ),
		array( "action" => "collection.collectionExport", "params" => $exportParams, "name" => rock_lang("export") ),
		array( "action" => "collection.collectionImport", "params" => $params, "name" => rock_lang("import") ),
		array( "action" => "collection.collectionProps", "params" => $params, "name" => rock_lang("properties") ),
		array( "action" => "collection.collectionIndexes", "params" => $params, "name" => rock_lang("indexes") ),
		array( "action" => "collection.collectionRename", "params" => $params, "name" => rock_lang("rename") ),
		array( "action" => "collection.collectionDuplicate", "params" => $params, "name" => rock_lang("duplicate") ),
		array( "action" => "collection.collectionTransfer", "params" => $params, "name" => rock_lang("transfer") ),
		array( "action" => "collection.collectionValidate", "params" => $params, "name" => rock_lang("validate") ),
		array( "action" => "collection.removeCollection", "params" => $params, "name" => rock_lang("drop"), "attr.onclick" => "return window.confirm('Are you sure to drop collection \\'" . $collectionName . "\\'?')" ),
	);

	//plugin
	if (class_exists("RFilter")) {
		RFilter::apply("COLLECTION_MENU_FILTER", $menuItems, array( "dbName" => $dbName, "collectionName" => $collectionName ));
	}
	$displayCount = 6;
	$hasMore = false;

	$string = "";
	$count = count($menuItems);
	foreach ($menuItems as $index => $op) {
		if ($index >= $displayCount && !$hasMore) {
			$hasMore = true;
			$string .= "<a href=\"#\" onclick=\"showMoreMenus(this);return false;\">" . rock_lang("more") . " &raquo;</a>";
			$string .= "<div class=\"menu\">";
		}

		if (is_string($op)) {
			if ($op == "-") {
				$string .= "-----------";
			}
			else {
				$string .= $op;
			}
		}
		else if (!empty($op["action"])) {
			$string .= '<a href="' . url($op["action"], isset($op["params"]) ? $op["params"] : array()) . '"';
			if (__CONTROLLER__ . "." . __ACTION__ == $op["action"]) {
				$string .= ' class="current"';
			}
			foreach ($op as $attrName => $attrValue) {
				if (preg_match("/^attr\\.(\\w+)/", $attrName, $match)) {
					$string .= " " . $match[1] . "=\"" . $attrValue . "\"";
				}
			}
			$string .= ">" . $op["name"] . "</a>";
		}
		else {
			if (!empty($op["url"])) {
				$string .= "<a href=\"" . $op["url"] . "\" target=\"_blank\">";
			}
			$string .= $op["name"];
			if (!empty($op["url"])) {
				$string .= "</a>";
			}
		}
		if ($hasMore) {
			$string .= "<br/>";
		}
		else {
			if ($index < $count - 1) {
				$string .= " | ";
			}
		}
	}
	if ($hasMore) {
		$string .= "</div>";
	}
	echo $string;
}
/**
 * Render document operations
 *
 * @param string $dbName database name
 * @param string $collectionName collection name
 * @param mixed $docId document id
 * @param integer $docIndex document index
 * @since 1.1.0
 */
function render_doc_menu($dbName, $collectionName, $docId, $docIndex) {
	$menuItems = array(
		array (  "action" => "collection.none", "name" => rock_lang("text"), "attr.onclick" => "changeText('{$docIndex}');return false;" ),
		array (  "action" => "collection.none", "name" => "Expand", "attr.id" => "expand_{$docIndex}", "attr.onclick" => "expandText('{$docIndex}');return false;" ),
	);

	//plugin
	if (class_exists("RFilter")) {
		RFilter::apply("DOC_MENU_FILTER", $menuItems, array( "dbName" => $dbName, "collectionName" => $collectionName, "docId" => $docId, "docIndex" => $docIndex ));
	}

	$displayCount = 2;
	$hasMore = false;

	$string = "";
	$count = count($menuItems);
	foreach ($menuItems as $index => $op) {
		if ($index >= $displayCount && !$hasMore) {
			$hasMore = true;
			$string .= "<a href=\"#\" onclick=\"showMoreDocMenus(this, {$docIndex});return false;\">" . rock_lang("more") . " &raquo;</a>";
			$string .= "<div class=\"doc_menu doc_menu_{$docIndex}\">";
		}

		if (is_string($op)) {
			if ($op == "-") {
				$string .= "-----------";
			}
			else {
				$string .= $op;
			}
		}
		else if (!empty($op["action"])) {
			$string .= '<a href="' . url($op["action"], isset($op["params"]) ? $op["params"] : array()) . '"';
			if (__CONTROLLER__ . "." . __ACTION__ == $op["action"]) {
				$string .= ' class="current"';
			}
			foreach ($op as $attrName => $attrValue) {
				if (preg_match("/^attr\\.(\\w+)/", $attrName, $match)) {
					$string .= " " . $match[1] . "=\"" . $attrValue . "\"";
				}
			}
			$string .= ">" . $op["name"] . "</a>";
		}
		else {
			if (!empty($op["url"])) {
				$string .= "<a href=\"" . $op["url"] . "\" target=\"_blank\">";
			}
			$string .= $op["name"];
			if (!empty($op["url"])) {
				$string .= "</a>";
			}
		}
		if ($hasMore) {
			$string .= "<br/>";
		}
		else {
			if ($index < $count - 1) {
				$string .= " | ";
			}
		}
	}
	if ($hasMore) {
		$string .= "</div>";
	}
	echo $string;
}

/**
 * Render supported data types
 *
 * @param string $name tag name
 * @param string|null $selected selected type
 * @since 1.1.0
 */
function render_select_data_types($name, $selected = null) {
	$types = array (
		"integer" => "Integer",
		"long" => "Long",
		"double" => "Double",
		"string" => "String",
		"boolean" => "Boolean",
		"null" => "NULL",
		"mixed" => "Mixed"
	);
	render_select($name, $types, $selected);
}

/**
 * Render a server list
 *
 * @param string $name tag name
 * @param array $servers server configs
 * @param integer $selectedIndex selected server index
 * @param array $attrs tag attributes
 * @since 1.1.0
 */
function render_server_list($name, $servers, $selectedIndex = 0, array $attrs = array()) {
	$options = array();
	foreach ($servers as $index => $server) {
		$server = new MServer($server);
		$options[$index] = $server->mongoName();
	}
	render_select($name, $options, $selectedIndex, $attrs);
}

/**
 * Render server hosts
 *
 * @param string $name tag name
 * @param string|null $selected selected host index
 * @since 1.1.0
 */
function render_select_hosts($name = "host", $selected = null) {
	global $MONGO;
	$hosts = array();
	foreach ($MONGO["servers"] as $config) {
		$server = new MServer($config);
		$hosts[] = $server->mongoName();
	}
	render_select($name, $hosts, $selected, array( "class" => "select_hosts" ));
}

/**
 * Render a view file
 *
 * examples:
 * - h_include("header", "title=DocumentTitle")
 * - h_include("footer")
 *
 * @param string $view view file name
 * @param string|array $vars a key-value array or name=iwind&age=18
 * @since 1.1.0
 */
function render_view($view, $vars = null) {
	if (is_string($vars)) {
		parse_str($vars);
	}
	else if (is_array($vars)) {
		extract($vars);
	}
	$view = dirname(__ROOT__) . DS . rock_theme_path() . DS . "views" . DS . str_replace(".", DS, $view) . ".php";
	require $view;
}

function render_theme_path() {
	echo rock_theme_path();
}

/**
 * Render page header
 *
 * @since 1.1.0
 */
function render_page_header() {
	if (class_exists("REvent")) {
		REvent::dispatch("RENDER_PAGE_HEADER_EVENT");
	}
}

/**
 * Render page footer
 *
 * @since 1.1.0
 */
function render_page_footer() {
	if (class_exists("REvent")) {
		REvent::dispatch("RENDER_PAGE_FOOTER_EVENT");
	}
}

/**
 * Render response from server
 *
 * @param string $response response
 * @since 1.1.0
 */
function render_server_response($response) {
	$string = "<div style=\"border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff\">" .
	rock_lang("responseserver") . "
		<div style=\"margin-top:5px\">
			{$response}
		</div>
	</div>";
	echo $string;
}

/**
 * Render url for an action
 *
 * @param string $action action name
 * @param array $params parameters
 * @since 1.1.0
 */
function render_url($action, array $params = array()) {
	echo url($action, $params);
}

?>