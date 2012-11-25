<?php

import("classes.BaseController");

class ServerController extends BaseController {
	/** server infomation **/
	public function doIndex() {
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
		$this->webServers["<a href=\"http://www.php.net\" target=\"_blank\">PHP version</a>"] = "PHP " . PHP_VERSION;
		$this->webServers["<a href=\"http://www.php.net/mongo\" target=\"_blank\">PHP extension</a>"] = "<a href=\"http://pecl.php.net/package/mongo\" target=\"_blank\">mongo</a>/" . RMongo::getVersion();
			
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
			"Host" => $this->_server->mongoHost(),
			"Port" => $this->_server->mongoPort(),
			"Username" => "******",
			"Password" => "******"
		);
		
		$this->display();
	}
	
	/** Server Status **/
	public function doStatus() {
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
	public function doDatabases() {
		$ret = $this->_server->listDbs();
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
	public function doCommand() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"]; 
		
		if (!$this->isPost()) {
			x("command", json_format("{listCommands:1}"));
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
				$eval = new VarEval($command);
				$command = $eval->execute();
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
	public function doExecute() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"]; 
		if (!$this->isPost()) {
			if (!x("db")) {
				x("db", "admin");
			}
			x("code", 'function () {
   var plus = 1 + 2;
   return plus;
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
	public function doProcesslist() {
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
	public function doKillOp() {
		$opid = xi("opid");
		$query = $this->_mongo->selectDB("admin")->execute('function (opid){
			return db.killOp(opid);
		}', array( $opid ));
		if ($query["ok"]) {
			$this->redirect("server.processlist");
		}
		$this->ret = $this->_highlight($query, "json");
		$this->display();
	}
	
	/** create databse **/
	public function doCreateDatabase() {
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
	public function doReplication() {
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
}

?>