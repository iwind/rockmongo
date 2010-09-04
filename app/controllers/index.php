<?php
ob_start();
session_start();

set_time_limit(0);

import("lib.ext.RExtController");
import("models.MDb");
import("models.MCollection");
class IndexController extends RExtController {
	private $_servers = array();
	private $_server;
	/**
	 * Enter description here...
	 *
	 * @var Mongo
	 */
	private $_mongo;
	private $_admin;//administrator's name
	private $_password;//administrator's encrypted password
	private $_serverIndex = 0;//current server index at all servers
	private $_serverUrl;
	
	/** called before any actions **/
	function onBefore() {
		global $MONGO;

		if ($this->action() != "login" && $this->action() != "logout") {
			//if user is loged in?
			if (!isset($_SESSION["login"]) || !isset($_SESSION["login"]["password"]) || !isset($_SESSION["login"]["index"])) {
				$this->redirect("login");
			}
			
			$this->_admin = $_SESSION["login"]["username"];
			$this->_password = $_SESSION["login"]["password"];
			$this->_serverIndex = $_SESSION["login"]["index"];
			
			//all allowed servers
			foreach ($MONGO["servers"] as $server) {
				if (isset($server["admins"][$this->_admin]) && $this->_encrypt($server["admins"][$this->_admin]) == $this->_password) {
					$this->_servers[] = $server;
				}
			}
			if (empty($this->_servers)) {
				exit("No servers you can access.");
			}
			
			//connect to current server
			if (!isset($this->_servers[$this->_serverIndex])) {
				$this->_serverIndex = 0;
			}
			$server = $this->_servers[$this->_serverIndex];
			$this->_server = $server;
			$link = "mongodb://";
			if ($server["username"]) {
				$link .= $server["username"] . ":" . $server["password"] . "@";
			}
			$link .= $server["host"] . ":" . $server["port"];
			$this->_serverUrl = $link;
			try {
				$this->_mongo = new Mongo($link);
			} catch (MongoConnectionException $e) {
				echo rock_lang("can_not_connect", $e->getMessage());
				exit();
			}
		}
		
		if ($this->action() != "admin" && !$this->isAjax()) {
			$this->display("header");
		}
	}
	
	/** called after action call **/
	function onAfter() {
		if ($this->action() != "admin" && $this->isAjax()) {
			$this->display("footer");
		}
	}
	
	/** just a test **/
	function doTest() {
		
	}
	
	/** home **/
	function doIndex() {
		$this->redirect("admin");
	}
	
	/** login page and post **/
	function doLogin() {
		global $MONGO;
		
		$this->username = trim(xn("username"));
		$this->languages = array(
			"en_us" => "English",
			"zh_cn" => "简体中文",
			"ja_jp" => "日本語"
		);
		
		if ($this->isPost()) {
			$password = trim(xn("password"));
			$serverIndexes = array();
			foreach ($MONGO["servers"] as $index => $server) {
				if (isset($server["admins"][$this->username]) && $server["admins"][$this->username] == $password) {
					$serverIndexes[] = $index;
				}
			}
			
			if (empty($serverIndexes)) {
				$this->message = "Wrong username or password";
				$this->display();
				return;
			}
			
			//remember user
			$_SESSION["login"] = array(
				"username" => $this->username,
				"password" => $this->_encrypt($password),
				"index" => 0
			);
			
			//remember lang
			setcookie("ROCK_LANG", x("lang"), time() + 7 * 86400);
			
			//jump to admin page
			$this->redirect("admin");
		}
		else {
			$this->display();
		}
	}
	
	/** encrypt password **/
	private function _encrypt($password) {
		return md5($password);
	}
	
	/** log out from system **/
	function doLogout() {
		session_destroy();
		$this->redirect("login");
	}
	
	/** admin page **/
	function doAdmin() {
		$this->topUrl = $this->path("top");
		$this->leftUrl = $this->path("dbs");
		$this->rightUrl = $this->path("server");
		
		$this->display();
	}
	
	/** top frame **/
	function doTop() {
		$this->logoutUrl = $this->path("logout");
		$this->admin = $this->_admin; 
		
		$this->servers = $this->_servers;
		$this->serverIndex = $this->_serverIndex;
		
		$isMasterRet =  null;
		try {
			$isMasterRet = $this->_mongo->selectDB($this->_admin)->command(array( "isMaster" => 1 ));
			if ($isMasterRet["ok"]) {
				$this->isMaster = $isMasterRet["ismaster"];
			}
			else {
				$this->isMaster = true;
			}
		} catch (MongoCursorException $e) {
			$this->isMaster = null;
		}
		
		$this->display();
	}
	
	/** change current host **/
	function doChangeHost() {
		$index = xi("index");
		$_SESSION["login"]["index"] = $index;
		$this->redirect("admin");
	}
	
	/** about project and us **/
	function doAbout() {
		$this->display();
	}
	
	/** show dbs in left frame **/
	function doDbs() {
		$dbs = $this->_listdbs();
		$this->_checkException($dbs);
		$this->dbs = array_values(rock_array_sort($dbs["databases"], "name"));
		$this->baseUrl = $this->path("dbs");
		$this->tableUrl = $this->path("collection");
		
		//add collection count
		foreach ($this->dbs as $index => $db) {
			$collectionCount = count(MDb::listCollections($this->_mongo->selectDB($db["name"])));
			$db["collectionCount"] = $collectionCount;
			if (isset($db["sizeOnDisk"])) {
				$db["size"] = round($db["sizeOnDisk"]/1024/1024, 2);//M
			}
			$this->dbs[$index] = $db;
		}

		//current db		
		$db = x("db");
		
		$this->tables = array();
		if ($db) {
			$mongodb = $this->_mongo->selectDB($db);
			$tables = MDb::listCollections($mongodb);
			foreach ($tables as $table) {
				$this->tables[$table->getName()] = $table->count();
			}
			ksort($this->tables);
		}
		
		$this->display();
	}
	
	/** server infomation **/
	function doServer() {
		$db = $this->_mongo->selectDB("admin");
		
		//command line
		$query = $db->command(array("getCmdLineOpts" => 1));
		if (isset($query["argv"])) {
			$this->commandLine = implode(" ", $query["argv"]);
		}
		else {
			$this->commandLine = "";
		}
		
		//web server
		$this->webServers = array();
		if (isset($_SERVER["SERVER_SOFTWARE"])) {
			list($webServer) = explode(" ", $_SERVER["SERVER_SOFTWARE"]);
			$this->webServers["Web server"] = $webServer;
		}
		$this->webServers["PHP version"] = "PHP " . PHP_VERSION;
		$this->webServers["PHP extension"] = "mongo/" . Mongo::VERSION;
			
		$this->directives = ini_get_all("mongo");
		
		//build info
		$ret = $db->command(array("buildinfo" => 1));
		$this->buildInfos = array();
		if ($ret["ok"]) {
			unset($ret["ok"]);
			$this->buildInfos = $ret;
		}
		
		//connection
		$this->connections = array(
			"Host" => $this->_server["host"],
			"Port" => $this->_server["port"],
			"Username" => "******",
			"Password" => "******"
		);
		
		$this->display();
	}
	
	/** Server Status **/
	function doStatus() {
		//status
		$db = $this->_mongo->selectDB("admin");
		$ret = $db->command(array("serverStatus" => 1));
		$this->status = array();
		if ($ret["ok"]) {
			unset($ret["ok"]);
			$this->status = $ret;
			foreach ($this->status as $index => $_status) {
				$json = $this->_highlight($_status, "json");
				if ($index == "uptime") {//we convert it to days
					if ($_status >= 86400) {
						$json .= "s (" . ceil($_status/86400) . "days)";
					}
				}
				$this->status[$index] =  $json;
			}
		}
		
		$this->display();
	}
	
	/** show databases **/
	function doDatabases() {
		$ret = $this->_listdbs();
		$this->dbs = $ret["databases"];
		foreach ($this->dbs as $index => $db) {
			$mongodb = $this->_mongo->selectDB($db["name"]);
			$ret = $mongodb->command(array("dbstats" => 1));
			$ret["collections"] = count(MDb::listCollections($mongodb));
			if (isset($db["sizeOnDisk"])) {
				$ret["diskSize"] = $this->_formatBytes($db["sizeOnDisk"]);
				$ret["dataSize"] = $this->_formatBytes($ret["dataSize"]);
			}
			else {
				$ret["diskSize"] = "-";
				$ret["dataSize"] = "-";
			}
			$ret["storageSize"] = $this->_formatBytes($ret["storageSize"]);
			$ret["indexSize"] = $this->_formatBytes($ret["indexSize"]);
			$this->dbs[$index] = array_merge($this->dbs[$index], $ret);
			
		}
		$this->dbs = rock_array_sort($this->dbs, "name");
		$this->display();
	}
	
	/** execute command **/
	function doCommand() {
		$ret = $this->_listdbs();
		$this->dbs = $ret["databases"]; 
		
		if (!$this->isPost()) {
			x("command", json_format("{assertinfo:1}"));
			if (!x("db")) {
				x("db", "admin");
			}
		}
		
		if ($this->isPost()) {
			$command = xn("command");
			$format = x("format");
			if ($format == "json") {
				$command = 	$this->_decodeJson($command);
			}
			else {
				eval("\$command=" . $command . ";");
			}
			if (!is_array($command)) {
				$this->message = "You should send a valid command";
				$this->display();
				return;
			}
			$this->ret = $this->_highlight($this->_mongo->selectDB(xn("db"))->command($command), $format);
		}
		$this->display();
	}
	
	/** execute code **/
	function doExecute() {
		$ret = $this->_listdbs();
		$this->dbs = $ret["databases"]; 
		if (!$this->isPost()) {
			if (!x("db")) {
				x("db", "admin");
			}
			x("code", 'function () {
   return "Hello,World";
}');
		}
		if ($this->isPost()) {
			$code = trim(xn("code"));
			$arguments = xn("argument");
			if (!is_array($arguments)) {
				$arguments = array();
			}
			else {
				$this->arguments = $arguments;
				foreach ($arguments as $index => $argument) {
					$argument = trim($argument);
					$array = $this->_decodeJson($argument);
					$arguments[$index] = $array;
				}
			}
			$ret = $this->_mongo->selectDB(xn("db"))->execute($code, $arguments);
			$this->ret = $this->_highlight($ret, "json");
		}
 		$this->display();
	}
	
	/** processlist **/
	function doProcesslist() {
		$query = $this->_mongo->selectDB("admin")->execute('function (){ 
			return db.$cmd.sys.inprog.find({ $all:1 }).next();
		}');

		$this->progs = array();
		if ($query["ok"]) {
			$this->progs = $query["retval"]["inprog"];
		}
		foreach ($this->progs as $index => $prog) {
			foreach ($prog as $key=>$value) {
				if (is_array($value)) {
					$this->progs[$index][$key] = $this->_highlight($value, "json");
				}
			}
		}
		$this->display();
	}
	
	/** kill one operation in processlist **/
	function doKillOp() {
		$opid = xi("opid");
		$query = $this->_mongo->selectDB("admin")->execute('function (opid){
			return db.killOp(opid);
		}', array( $opid ));
		if ($query["ok"]) {
			$this->redirect("processlist");
		}
		$this->ret = $this->_highlight($query, "json");
		$this->display();
	}
	
	/** create databse **/
	function doCreateDatabase() {
		if ($this->isPost()) {
			$name = trim(xn("name"));
			if (empty($name)) {
				$this->error = "Please input a valid database name.";
				$this->display();
				return;
			}
			$this->message = "New database created.";
			$this->_mongo->selectDb($name)->execute("function(){}");
		}
		$this->display();
	}
	
	/** replication status **/
	function doReplication() {
		$ret = $this->_mongo->selectDB("local")->execute('function () { return db.getReplicationInfo(); }');
		$this->status = array();
		$status = isset($ret["retval"]) ? $ret["retval"] : array();
		if (isset($ret["retval"]["errmsg"])) {
			$this->status["errmsg"] = $ret["retval"]["errmsg"];
		}
		else {
			foreach ($status as $param => $value) {
				if ($param == "logSizeMB") {
					$this->status["Configured oplog size"] = $value . "m";
				}
				else if ($param == "timeDiff") {
					$this->status["Log length start to end"] = $value . "secs (" . $status["timeDiffHours"] . "hrs)";
				}
				else if ($param == "tFirst") {
					$this->status["Oplog first event time"] = $value;
				}
				else if ($param == "tLast") {
					$this->status["Oplog last event time"] = $value;
				}
				else if ($param == "now") {
					$this->status["Now"] = $value;
				}
			}
		}
		
		//slaves
		$this->slaves = array();
		$query = $this->_mongo->selectDB("local")->selectCollection("slaves")->find();
		foreach ($query as $one) {
			foreach ($one as $param=>$value) {
				if ($param == "syncedTo") {
					$one[$param] = date("Y-m-d H:i:s", $value->inc) . "." . $value->sec;
				}
			}
			$this->slaves[] = $one;
		}
		
		//masters
		$this->masters = array();
		$query = $this->_mongo->selectDB("local")->selectCollection("sources")->find();
		foreach ($query as $one) {
			foreach ($one as $param=>$value) {
				if ($param == "syncedTo" || $param == "localLogTs") {
					if ($value->inc > 0) {
						$one[$param] = date("Y-m-d H:i:s", $value->inc) . "." . $value->sec;
					}
				}
			}
			$this->masters[] = $one;
		}
		
		//me
		$this->me = $this->_mongo->selectDB("local")->selectCollection("me")->findOne();
		
		$this->display();
	}
	
	/** database **/
	function doDb() {
		$this->db = trim(xn("db"));
		
		$dbs = $this->_listdbs();
		$ret = array();
		foreach ($dbs["databases"] as $db) {
			if ($db["name"] == $this->db) {
				$ret = $db;
			}
		}
		
		//collections
		$db = $this->_mongo->selectDB($this->db);
		$collections = MDb::listCollections($db);
		
		$ret = array_merge($ret, $db->command(array("dbstats" => 1)));
		$ret["diskSize"] = "-";
		if (isset($ret["sizeOnDisk"])) {
			$ret["diskSize"] = $this->_formatBytes($ret["sizeOnDisk"]);
		}
		$ret["dataSize"] = $this->_formatBytes($ret["dataSize"]);
		$ret["storageSize"] = $this->_formatBytes($ret["storageSize"]);
		$ret["indexSize"] = $this->_formatBytes($ret["indexSize"]);
		
		$this->stats = array();
		$this->stats["Size"] = $ret["diskSize"];
		$this->stats["Is Empty?"] = $ret["empty"] ? "Yes" : "No";
		if (empty($collections)) {
			$this->stats["Collections"] = count($collections) . " collections:";
			$this->stats["Collections"] .= "<br/>No collections yet";
		}
		else {
			$key = "Collections<br/>[<a href=\"" . $this->path("dropDbCollections", array( "db" => $this->db )) . "\" onclick=\"return window.confirm('Are you sure to drop all collections in the db?')\"><u>Drop All</u></a>]<br/>[<a href=\"" . $this->path("clearDbCollections", array( "db" => $this->db )) . "\" onclick=\"return window.confirm('Are you sure to clear all records in all collections?')\"><u>Clear All</u></a>]";
			$this->stats[$key] = count($collections) . " collections:";
			foreach ($collections as $collection) {
				$this->stats[$key] .= "<br/><a href=\"" 
					. $this->path("collection", array( "db" => $this->db, "collection" => $collection->getName())) . "\">" . $collection->getName() . "</a>";
			}
		}
		$this->stats["Objects"] = $ret["objects"];
		$this->stats["Data Size"] = $ret["dataSize"];
		$this->stats["Storage Size"] = $ret["storageSize"];
		$this->stats["Extents"] = $ret["numExtents"];
		$this->stats["Indexes"] = $ret["indexes"];
		$this->stats["Index Size"] = $ret["indexSize"];
		
		
		
		
		$this->display();
	}
	
	/** drop database **/
	function doDropDatabase() {
		$this->db = xn("db");
		
		if (!x("confirm")) {
			$this->display();
			return;
		}
		
		$ret = $this->_mongo->dropDB($this->db);
		$this->ret = $this->_highlight($ret, "json");
		$this->display("dropDatabaseResult");
	}
	
	/** drop all collections in a db **/
	function doDropDbCollections() {
		$this->db = xn("db");
		$db = $this->_mongo->selectDB($this->db);
		foreach ($db->listCollections() as $collection) {
			$collection->drop();
		}
		echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
		$this->redirect("db", array( "db" => $this->db ), true);
	}
	
	/** clear all records in all collections **/
	function doClearDbCollections() {
		$this->db = xn("db");
		$db = $this->_mongo->selectDB($this->db);
		foreach ($db->listCollections() as $collection) {
			$collection->remove();
		}
		echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
		$this->redirect("db", array( "db" => $this->db ), true);
	}
	
	/** repair dataase **/
	function doRepairDatabase() {
		$this->db = xn("db");
		
		$db = $this->_mongo->selectDB($this->db);
		$ret = $db->command(array( "repairDatabase" => 1 ));
		//$ret = $db->execute('function (){ return db.repairDatabase(); }'); //occure error in current version, we did not know why?
		$this->ret = $this->_highlight($ret, "json");
		$this->display();
	}
	
	/** db profiling **/
	function doProfile() {
		$this->db = xn("db");
	
		import("lib.mongo.RQuery");
		import("lib.page.RPageStyle1");
		$query = new RQuery($this->_mongo, $this->db, "system.profile");
		$page = new RPageStyle1();
		$page->setTotal($query->count());
		$page->setSize(10);
		$page->setAutoQuery();
		$this->page = $page;
		
		$this->rows = $query
			->offset($page->offset())
			->limit($page->size())
			->desc("ts")
			->findAll();
		foreach ($this->rows as $index => $row) {
			$this->rows[$index]["text"] = $this->_highlight($row, "json");
		}
			
		$this->display();
	}
	
	/** change db profiling level **/
	function doProfileLevel() {
		$this->db = xn("db");

		$db = $this->_mongo->selectDB($this->db);
		$query1 = $db->execute("function (){ return db.getProfilingLevel(); }");
		$this->level = $query1["retval"];
		if (x("go") == "save_level") {
			$level = xi("level");
			$slowms = xi("slowms");
			$db->execute("function(level,slowms) { db.setProfilingLevel(level,slowms); }", array($level, $slowms));
			$this->level = $level;
		}
		else {
			x("slowms", 50);
		}
		$this->display();
	}
	
	/** clear profiling data **/
	function doClearProfile() {
		$this->db = xn("db");
		$db = $this->_mongo->selectDB($this->db);
		
		$query1 = $db->execute("function (){ return db.getProfilingLevel(); }");
		$oldLevel = $query1["retval"];
		$db->execute("function(level) { db.setProfilingLevel(level); }", array(0));
		$ret = $db->selectCollection("system.profile")->drop();
		$db->execute("function(level) { db.setProfilingLevel(level); }", array($oldLevel));

		$this->redirect("profile", array( 
			"db" => $this->db
		));
	}	
	
	/** authentication **/
	function doAuth() {
		$this->db = xn("db");
		$db = $this->_mongo->selectDB($this->db);
		
		//users
		$collection = $db->selectCollection("system.users");
		$cursor = $collection->find();
		$this->users= array();
		while($cursor->hasNext()) {
			$this->users[] = $cursor->getNext();
		}
		
		$this->display();
	}
	
	/** delete user **/
	function doDeleteUser() {
		$this->db = xn("db");
		$db = $this->_mongo->selectDB($this->db);
		
		$db->execute("function (username){ db.removeUser(username); }", array(xn("user")));
		$this->redirect("auth", array(
			"db" => $this->db
		));
	}
	
	/** add user **/
	function doAddUser() {
		$this->db = xn("db");
		
		if (!$this->isPost()) {
			$this->display();
			return;
		}
		
		$username = trim(xn("username"));
		$password = trim(xn("password"));
		$password2 = trim(xn("password2"));
		if ($username == "") {
			$this->error = "You must supply a username for user.";
			$this->display();
			return;
		}
		if ($password == "") {
			$this->error = "You must supply a password for user.";
			$this->display();
			return;
		}
		if ($password != $password2) {
			$this->error = "Passwords you typed twice is not same.";
			$this->display();
			return;
		}
		$db = $this->_mongo->selectDB($this->db);
		$db->execute("function (username, pass, readonly){ db.addUser(username, pass, readonly); }", array(
			$username,
			$password,
			x("readonly") ? true : false
		));
		
		$this->redirect("auth", array(
			"db" => $this->db
		));
	}
	
	/** transfer db collections from one server to another **/
	function doDbTransfer() {
		$this->db = xn("db");
		
		$db = $this->_mongo->selectDB($this->db);
		$this->collections = $db->listCollections();
		$this->servers = $this->_servers;
		$this->serverIndex = $this->_serverIndex;
		
		$this->selectedCollections = array();
		if (!$this->isPost()) {
			$this->selectedCollections[] = xn("collection");
			x("copy_indexes", 1);
		}
		else {
			$checkeds = xn("checked");
			if (is_array($checkeds)) {
				$this->selectedCollections = array_keys($checkeds);
			}
			if (empty($checkeds)) {
				$this->error = "Please select collections which you want to transfer.";
				$this->display();
				return;
			}
			$target = trim(xn("target"));
			$hostIndex = intval(xn("server"));
			$copyIndexes = xi("copy_indexes");
			/**if ($target === "") {
				$this->error = "Please enter a valid database name.";
				$this->display();
				return;
			}**/
			
			$host = $this->_servers[$hostIndex];
			$server = "";
			if ($host["username"]) {
				$server .= $host["username"] . ":" . $host["password"] . "@";
			}
			$server .= $host["host"] . ":" . $host["port"];
			
			//start to transfer
			$targetConnection = new Mongo("mongodb://" . $server);
			$targetDb = $targetConnection->selectDB($this->db);
			$errors = array();
			foreach ($this->selectedCollections as $collectionName) {
				$ret = $targetDb->command(array(
					"cloneCollection" => $this->db . "." . $collectionName,
					"from" =>  str_replace("mongodb://", "", $this->_serverUrl),
					"copyIndexes" => (bool)$copyIndexes
				));
				if (!$ret["ok"]) {
					$errors[] = $ret["errmsg"];
				}
				else {
					
				}
			}
			if (!empty($errors)) {
				$this->error = implode("<br/>", $errors);
				$this->display();
				return;
			}
			
			$this->message = "All data were transfered to '{$server}' successfully.";
		}		
		
		$this->display();
	}
	
	/** export db **/
	function doDbExport() {
		$this->db = xn("db");
		
		$db = $this->_mongo->selectDB($this->db);
		$this->collections = $db->listCollections();
			
		$this->selectedCollections = array();
		if (!$this->isPost()) {
			$this->selectedCollections[] = xn("collection");
		}
		else {
			$checkeds = xn("checked");
			$canDownload = xn("can_download");
			if (is_array($checkeds)) {
				$this->selectedCollections = array_keys($checkeds);
			}
			
			sort($this->selectedCollections);
			
			import("classes.VarExportor");
			$this->contents =  "";
			$this->countRows = 0;
			
			//indexes
			foreach ($this->selectedCollections as $collection) {
				$collObj = $db->selectCollection($collection);
				$infos = $collObj->getIndexInfo();
				foreach ($infos as $info) {
					$options = array();
					if (isset($info["unique"])) {
						$options["unique"] = $info["unique"];
					}
					$exportor = new VarExportor($db, $info["key"]);
					$exportor2 = new VarExportor($db, $options);
					$this->contents .= "\n/** {$collection} indexes **/\ndb[\"" . addslashes($collection) . "\"].ensureIndex(" . $exportor->export(MONGO_EXPORT_JSON) . "," . $exportor2->export(MONGO_EXPORT_JSON) . ");\n";
				}
			}
			
			//data
			foreach ($this->selectedCollections as $collection) {
				$cursor = $db->selectCollection($collection)->find();
				$this->contents .= "\n/** " . $collection  . " records **/\n";
				foreach ($cursor as $one) {
					$this->countRows ++;
					$exportor = new VarExportor($db, $one);
					$this->contents .= "db[\"" . addslashes($collection) . "\"].insert(" . $exportor->export(MONGO_EXPORT_JSON) . ");\n";
				}
			}
			
			if (x("can_download")) {
				ob_end_clean();
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"rockmongo-export-" . urlencode($this->db) . "-" . time() . ".json\")");
				echo $this->contents;
				exit();
			}
		}
		
		$this->display();
	}
	
	/** import db **/
	function doDbImport() {
		$this->db = xn("db");
		
		if ($this->isPost()) {
			if (!empty($_FILES["json"]["tmp_name"])) {
				$tmp = $_FILES["json"]["tmp_name"];
				$body = file_get_contents($tmp);
				
				$ret = $this->_mongo->selectDB($this->db)->execute('function (){ ' . $body . ' }');
				$this->message = "All data import successfully.";
			}
			else {
				$this->error = "Either no file input or file is too large to upload.";
			}
		}
		
		$this->display();
	}
	
	/** show one collection **/
	function doCollection() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		//field and sort
		$fields = xn("field");
		$orders = xn("order");
		if (empty($fields)) {
			$fields = array(
				"_id", "", "", ""
			);
			$orders = array(
				"desc", "asc", "asc", "asc"
			);
			x("field", $fields);
			x("order", $orders);
		}
		
		
		//conditions
		$native = xn("criteria");
		$criteria = $native;
		if (empty($criteria)) {
			$criteria = array();
			$native = "array(\n\n)";
			x("newobj", 'array(
	\'$set\' => array (
		//your attributes
	)
)');
		}
		else {
			$row = null;
			eval("\$row={$criteria};");
			if (!is_array($row)) {
				$this->message = "Criteria must be an array.";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		x("criteria", $native);

		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//sort
		foreach ($fields as $index => $field) {
			if (!empty($field)) {
				if ($orders[$index] == "asc") {
					$query->asc($field);
				}
				else {
					$query->desc($field);
				}
			}
		}
		
		//command
		$command = x("command");
		if (!$command) {
			$command = "findAll";
			x("command", $command);
		}
		$limit = xi("limit");
		if ($limit > 0) {
			$query->limit($limit);
		}
		$count = ($limit > 0 && $command == "findAll") ? $query->count(true) : $query->count();
		
		switch ($command) {
			case "findAll":
				break;
			case "remove":
				$microtime = microtime(true);	
				$query->delete();
				$this->cost = microtime(true) - $microtime;
				break;
			case "modify":
				$microtime = microtime(true);	
				$row = null;
				$newobj = xn("newobj");
				eval("\$row={$newobj};");
				if (is_array($row)) {
					$query->upsert($row);
				}
				$this->cost = microtime(true) - $microtime;
				break;
		}
		
		if ($command != "findAll") {
			$this->count = $count;
			$this->display();
			return;
		}
		
		//pagination
		$pagesize = xi("pagesize");
		if ($pagesize < 1) {
			$pagesize = 10;
		}
		import("lib.page.RPageStyle1");
		$page = new RPageStyle1();
		$page->setTotal($count);
		$page->setSize($pagesize);
		$page->setAutoQuery();

		$this->page = $page;
		$query->offset($page->offset());
		if ($limit > 0) {
			$query->limit(min($limit, $page->size()));
		}
		else {
			$query->limit($page->size());
		}
		
		//format
		$format = x("format");
		if (!$format) {
			$format = "json";
		}
		$params = xn();
		$params["format"] = "array";
		$this->arrayLink = $this->path($this->action(), $params);
		$params["format"] = "json";
		$this->jsonLink = $this->path($this->action(), $params);
		
		$microtime = microtime(true);	
		$this->rows = $query->findAll(true);
		$this->cost = microtime(true) - $microtime;
		import("classes.VarExportor");
		foreach ($this->rows as $index => $row) {
			$native = $row;
			$exportor = new VarExportor($query->db(), $native);
			$row["text"] = $exportor->export($format);
			$row["data"] = $this->_highlight($native, $format);
			$this->rows[$index] = $row;
		}
		
		$this->display();
	}
	
	/** explain query **/
	function doExplainQuery() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		//field and sort
		$fields = xn("field");
		$orders = xn("order");
		if (empty($fields)) {
			$fields = array(
				"_id", "", "", ""
			);
			$orders = array(
				"desc", "asc", "asc", "asc"
			);
			x("field", $fields);
			x("order", $orders);
		}
		
		
		//conditions
		$native = xn("criteria");
		$criteria = $native;
		if (empty($criteria)) {
			$criteria = array();
			$native = "array(\n\n)";
		}
		else {
			$row = null;
			eval("\$row={$criteria};");
			if (!is_array($row)) {
				$this->error = "To explain a query, criteria must be an array.";
				$this->display();
				return;
			}
			$criteria = $row;
		}
		x("criteria", $native);

		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->cond($criteria);
		
		//sort
		foreach ($fields as $index => $field) {
			if (!empty($field)) {
				if ($orders[$index] == "asc") {
					$query->asc($field);
				}
				else {
					$query->desc($field);
				}
			}
		}
		
		//command
		$command = x("command");
		if (!$command) {
			$command = "findAll";
			x("command", $command);
		}
		
		$this->ret = $this->_highlight($query->cursor()->explain(), "json");
		$this->display();
	}
	
	/** delete on row **/
	function doDeleteRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->id(rock_real_id(x("id")))->delete();
		
		echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
		$this->redirectUrl(xn("uri"), true);
	}
	
	/** create row **/
	function doCreateRow() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$id = rock_real_id(x("id"));
		
		import("lib.mongo.RQuery");		
		
		//if is duplicating ...
		if (!$this->isPost() && $id) {
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$row = $query->id($id)->findOne();
			if (!empty($row)) {
				unset($row["_id"]);
				import("classes.VarExportor");
				$export = new VarExportor($query->db(), $row);
				x("data", $export->export(MONGO_EXPORT_PHP));
			}
		}
		if (!$this->isPost() && !x("data")) {
			x("data", "array(\n\n)");
		}
		
		if ($this->isPost()) {
			$data = xn("data") . ";";
			$data = str_replace(array(
				"%{created_at}"
			), time(), $data);
			$row = null;
			if (@eval("\$row=" . $data . ";") === false || !is_array($row)) {
				$this->error = "Only PHP array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$ret = $query->insert($row);
			
			echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
			$this->redirect("collection", array( 
				"db" => $this->db,
				"collection" => $this->collection 
			), true);
		}
		
		$this->display();
	}
	
	/** modify one row **/
	function doModifyRow() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$id = rock_real_id(xn("id"));
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$this->row = $query->id($id)->findOne();
		if (empty($this->row)) {
			$this->message = "Record is not found.";
			$this->display();
			return;
		}
		$this->data = $this->row;
		unset($this->data["_id"]);
		import("classes.VarExportor");
		$export = new VarExportor($query->db(), $this->data);
		$this->data = $export->export(MONGO_EXPORT_PHP);
	
		if ($this->isPost()) {
			$this->data = xn("data");
			$row = null;
			if (@eval("\$row=" . $this->data . ";") === false || !is_array($row)) {
				$this->message = "Only array is accepted.";
				$this->display();
				return;
			}
			
			$query = new RQuery($this->_mongo, $this->db, $this->collection);
			$obj = $query->id($this->row["_id"])->find();
			$oldAttrs = $obj->attrs();
			$obj->setAttrs($row);
			foreach ($oldAttrs as $oldAttr => $oldValue) {
				if ($oldAttr == "_id") {
					continue;
				}
				if (!isset($row[$oldAttr])) {
					$obj->remove($oldAttr);
				}
			}
			$obj->save();
			
			$this->message = "Updated successfully.";
		}
		
		$this->display();
	}
	
	/** clear rows in collection **/
	function doClearRows() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		import("lib.mongo.RQuery");	
		$query = new RQuery($this->_mongo, $this->db, $this->collection);
		$query->delete();
		
		echo '<script language="javascript">
window.parent.frames["left"].location.reload();
</script>';
		
		$this->redirect("collection", array(
			"db" => $this->db,
			"collection" => $this->collection
		), true);
	}
	
	/** drop collection **/
	function doRemoveCollection() {
		$this->db = x("db");
		$this->collection = x("collection");
		$db = new MongoDB($this->_mongo, $this->db);
		$db->dropCollection($this->collection);
		$this->display();
	}
	
	/** create new collection **/
	function doNewCollection() {
		$this->db = xn("db");
		$this->name = x("name");
		$this->isCapped = xi("is_capped");
		$this->size = xi("size");
		$this->max = xi("max");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$db->createCollection($this->name, $this->isCapped, $this->size, $this->max);
			$this->message = "New collection is created.";
			
			//add index
			$db->selectCollection($this->name)->ensureIndex(array( "_id" => 1 ));
		}
		
		$this->display();
	}
	
	/** list collection indexes **/
	function doCollectionIndexes() {
		$this->db = x("db");
		$this->collection = x("collection");
		$collection = $this->_mongo->selectCollection(new MongoDB($this->_mongo, $this->db), $this->collection);
		$this->indexes = $collection->getIndexInfo();
		foreach ($this->indexes as $_index => $index) {
			$index["data"] = $this->_highlight($index["key"]);
			$this->indexes[$_index] = $index;
		}
		$this->display();
	}
	
	/** drop a collection index **/
	function doDeleteIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$db = new MongoDB($this->_mongo, $this->db);
		$collection = $this->_mongo->selectCollection($db, $this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == trim(xn("index"))) {
				$ret = $db->command(array("deleteIndexes" => $collection->getName(), "index" => $index["name"]));
				break;
			}
		}
		
		$this->redirect("collectionIndexes", array(
			"db" => $this->db,
			"collection" => $this->collection
		));
	}
	
	/** create a collection index **/
	function doCreateIndex() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		if ($this->isPost()) {
			$db = new MongoDB($this->_mongo, $this->db);
			$collection = $this->_mongo->selectCollection($db, $this->collection);
			
			$fields = xn("field");
			if (!is_array($fields)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			$orders = xn("order");
			$attrs = array();
			foreach ($fields as $index => $field) {
				$field = trim($field);
				if (!empty($field)) {
					$attrs[$field] = ($orders[$index] == "asc") ? 1 : -1;
				}
			}
			if (empty($attrs)) {
				$this->message = "Index contains one field at least.";
				$this->display();
				return;
			}
			
			//if is unique
			$options = array();
			if (x("is_unique")) {
				$options["unique"] = 1;
				if (x("drop_duplicate")) {
					$options["dropDups"] = 1;
				}
			}
			$options["background"] = 1;
			$options["safe"] = 1;
			
			//name
			$name = trim(xn("name"));
			if (!empty($name)) {
				$options["name"] = $name;
			}
			$collection->ensureIndex($attrs, $options);
			
			$this->redirect("collectionIndexes", array(
				"db" => $this->db,
				"collection" => $this->collection
			));
		}
		
		$this->display();
	}
	
	/** collection statistics **/
	function doCollectionStats() {
		$this->db = x("db");
		$this->collection = x("collection");
		$this->stats = array();
		
		$db = new MongoDB($this->_mongo, $this->db);
		$ret = $db->execute("db.{$this->collection}.stats()");
		if ($ret["ok"]) {
			$this->stats = $ret["retval"];
			foreach ($this->stats as $index => $stat) {
				if (is_array($stat)) {
					$this->stats[$index] = $this->_highlight($stat, "json");
				}
			}
		}
		
		//top
		$ret = $this->_mongo->selectDB("admin")->command(array( "top" => 1 ));
		$this->top = array();
		if ($ret["ok"]) {
			$this->top = $ret["totals"][$this->db . "." . $this->collection];
			foreach ($this->top as $index => $value) {
				$this->top[$index] = $value["count"];
			}
		}
		$this->display();
	}
	
	/** validate collection **/
	function doCollectionValidate() {
		$this->db = x("db");
		$this->collection = x("collection");
		
		$db = $this->_mongo->selectDB($this->db);
		$this->ret = $this->_highlight($db->execute('function (collection){ return db[collection].validate(); }', array($this->collection)), "json");
		$this->display();
	}
	
	/** rename collection **/
	function doCollectionRename() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->realName = $this->collection;
		if ($this->isPost()) {
			$oldname = trim(xn("oldname"));
			$newname = trim(xn("newname"));
			$removeExists = trim(xn("remove_exists"));
			if ($newname === "") {
				$this->error = "Please enter a new name.";
				$this->display();
				return;
			}
			if (!$removeExists) {
				//Is there a same name?
				$collections = MDb::listCollections($this->_mongo->selectDB($this->db));
				foreach ($collections as $collection) {
					if ($collection->getName() == $newname) {
						$this->error = "There is already a '{$newname}' collection, you should drop it before renaming.";
						$this->display();
						return;
					}
				}
			}
 			$this->ret = $this->_mongo->selectDB($this->db)->execute('function (coll, newname, dropExists) { db[coll].renameCollection(newname, dropExists);}', array( $oldname, $newname, (bool)$removeExists ));
 			if ($this->ret["ok"]) {
 				$this->realName = $newname;
 				$this->message = "Operation success.";
 			}
 			else {
 				$this->error = "Operation failure";
 			}
			$this->ret = $this->_highlight($this->ret, "json");
		}
		$this->display();
	}
	
	/** collection properties **/
	function doCollectionProps() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		$ret = $this->_mongo->selectDB($this->db)->execute('function (coll){return db[coll].exists();}', array( $this->collection ));
		
		if (!$ret["ok"]) {
			exit("There is something wrong:<font color=\"red\">{$ret['errmsg']}</font>, please refresh the page to try again.");
		}
		if (!isset($ret["retval"]["options"])) {
			$ret["retval"]["options"] = array();
		}
		$this->isCapped = 0;
		$this->size = 0;
		$this->max = 0;
		$options = $ret["retval"]["options"];
		if (isset($options["capped"])) {
			$this->isCapped = $options["capped"];
		}
		if (isset($options["size"])) {
			$this->size = $options["size"];
		}
		if (isset($options["max"])) {
			$this->max = $options["max"];
		}
		
		if ($this->isPost()) {
			$this->isCapped = xi("is_capped");
			$this->size = xi("size");
			$this->max = xi("max");
			
			//rename current collection
			$bkCollection = $this->collection . "_rockmongo_bk_" . uniqid();
			$this->ret = $this->_mongo->selectDB($this->db)->execute('function (coll, newname, dropExists) { db[coll].renameCollection(newname, dropExists);}', array( $this->collection, $bkCollection, true ));
			if (!$this->ret["ok"]) {
				$this->error = "There is something wrong:<font color=\"red\">{$ret['errmsg']}</font>, please refresh the page to try again.";
				$this->display();
				return;
			}
			
			//create new collection
			$db = $this->_mongo->selectDB($this->db);
			$db->createCollection($this->collection, $this->isCapped, $this->size, $this->max);
			
			//copy data to new collection
			if (!$this->_copyCollection($db, $bkCollection, $this->collection, true)) {
				//try to recover
				$this->ret = $db->execute('function (coll, newname, dropExists) { db[coll].renameCollection(newname, dropExists);}', array( $bkCollection, $this->collection, true ));
				
				$this->error = "There is something wrong:<font color=\"red\">{$ret['errmsg']}</font>, please refresh the page to try again.";
				$this->display();
				return;
			}
			
			//drop current collection
			$db->dropCollection($bkCollection);
		}
		
		$this->display();
	}
	
	/** duplicate collection **/
	function doCollectionDuplicate() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		if (!$this->isPost()) {
			x("target", $this->collection . "_copy");
			x("remove_target", 1);
			x("copy_indexes", 1);
		}
		if ($this->isPost()) {
			$target = trim(xn("target"));
			$removeTarget = x("remove_target");
			$copyIndexes = x("copy_indexes");
			if ($target === "") {
				$this->error = "Please enter a valid target.";
				$this->display();
				return;
			}
			$db = $this->_mongo->selectDB($this->db);
			if ($removeTarget) {
				$db->selectCollection($target)->drop();
			}
			$this->_copyCollection($db, $this->collection, $target, $copyIndexes);
			$this->message = "Collection duplicated successfully.";
 		}
		$this->display();
	}
	
	/** transfer a collection **/
	function doCollectionTransfer() {
		$this->redirect("dbTransfer", array(
			"db" => xn("db"),
			"collection" => xn("collection")
		));
	}
	
	/** export a collection **/
	function doCollectionExport() {
		$this->redirect("dbExport", array( "db" => xn("db"), "collection" => xn("collection") ));
	}
	
	/** import a collection **/
	function doCollectionImport() {
		$this->redirect("dbImport", array( "db" => xn("db") ));
	}
	
	private function _encodeJson($var) {
		if (function_exists("json_encode")) {
			return json_encode($var);
		}
		import("classes.Services_JSON");
		$service = new Services_JSON();
		return $service->encode($var);
	}
	
	private function _decodeJson($var) {
		import("classes.Services_JSON");
		$service = new Services_JSON();
		$ret = array();
		$decode = $service->decode($var);
		if (!is_object($decode)) {
			return $decode;
		}
		foreach ($decode as $key => $value) {
			$ret[$key] = $value;
		}
		return $ret;
	}	
	
	/**
	 * Export var as string then highlight it.
	 *
	 * @param mixed $var variable to be exported
	 * @param string $format data format, array|json
	 * @return string
	 */
	private function _highlight($var, $format = "array") {
		import("classes.VarExportor");
		$exportor = new VarExportor($this->_mongo->selectDB("admin"), $var);
		$varString = $exportor->export($format);
		$string = null;
		if ($format == "array") {
			$string = highlight_string("<?php " . $varString, true);
			$string = preg_replace("/" . preg_quote('<span style="color: #0000BB">&lt;?php&nbsp;</span>', "/") . "/", '', $string, 1);
		}
		else {
			$string =  json_format_html($varString);
		}
		return $string;
	}
	
	/** 
	 * format bytes to human size 
	 * 
	 * @param integer $bytes size in byte
	 * @return string size in k, m, g..
	 **/
	private function _formatBytes($bytes) {
		if ($bytes < 1024) {
			return $bytes;
		}
		if ($bytes < 1024 * 1024) {
			return round($bytes/1024, 2) . "k";
		}
		if ($bytes < 1024 * 1024 * 1024) {
			return round($bytes/1024/1024, 2) . "m";
		}
		if ($bytes < 1024 * 1024 * 1024 * 1024) {
			return round($bytes/1024/1024/1024, 2) . "g";
		}
		return $bytes;
	}
	
	/** throw operation exception **/
	private function _checkException($ret) {
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
	
	private function _listdbs() {
		$dbs = $this->_mongo->listDBs();
		$this->_checkException($dbs);
		return $dbs;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param MongoDB $db
	 * @param unknown_type $from
	 * @param unknown_type $to
	 * @param unknown_type $index
	 */
	private function _copyCollection($db, $from, $to, $index = true) {
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
		$ret = $db->execute('function (coll, coll2) { return db[coll].copyTo(coll2);}', array( $from, $to ));
		return $ret["ok"];
	}
}


/**
 * 将一个多维数组按照一个键的值排序
 *
 * @param array $array 数组
 * @param mixed $key string|array
 * @param boolean $asc 是否正排序
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
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Pretty print some JSON
//modified by iwind
function json_format_html($json)
{
    $tab = "&nbsp;&nbsp;";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= $char . "<br/>" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "<br/>" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= ",<br/>" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                    $new_json .= $in_string ? "<font color=\"red\">" . $char: $char . "</font>"; //iwind
                    break;//iwind
                }
            default:
            	if (!$in_string) {
            		$char = "<font color=\"blue\">" . $char . "</font>";
            	}
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}


function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}

?>