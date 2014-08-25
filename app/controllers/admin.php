<?php

import("classes.BaseController");

class AdminController extends BaseController {
	/** admin page **/
	public function doIndex() {
		$this->topUrl = $this->path("admin.top");
		$this->leftUrl = $this->path("admin.dbs");
		$this->rightUrl = $this->path("server.index");

		$this->display();
	}

	/** top frame **/
	public function doTop() {
		$this->logoutUrl = $this->path("logout.index");
		$this->admin = $this->_admin->username();

		$this->servers = $this->_admin->servers();
		$this->serverIndex = $this->_admin->hostIndex();

		$isMasterRet =  null;
		try {
			$isMasterRet = $this->_mongo->selectDB($this->_admin->defaultDb())->command(array( "isMaster" => 1 ));
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

	/** show dbs in left frame **/
	public function doDbs() {
		$dbs = $this->_server->listDbs();
		$this->dbs = array_values(rock_array_sort($dbs["databases"], "name"));
		$this->baseUrl = $this->path("admin.dbs");
		$this->tableUrl = $this->path("collection.index");
		$this->showDbSelector = false;

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
		}
		$this->display();
	}

	/** about project and us **/
	public function doAbout() {
		$this->display();
	}

	/** change current host **/
	public function doChangeHost() {
		$index = xi("index");
		MUser::userInSession()->changeHost($index);
		$this->redirect("admin.index", array( "host" => $index ));
	}

	/**
	 * change language of UI interface
	 *
	 */
	public function doChangeLang() {
		setcookie("ROCK_LANG", x("lang"), time() + 365 * 86400);
		header("location:index.php");
	}
}


?>