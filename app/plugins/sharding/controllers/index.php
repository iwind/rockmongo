<?php

import("classes.BaseController");

class IndexController extends BaseController {
	public function onBefore() {
		parent::onBefore();
		
		//Am i on mongos?
		try {
			$ret = $this->_mongo->selectDB("admin")->command(array( "listshards" => 1 ));
			if (isset($ret["bad cmd"]) && $ret["bad cmd"]) {
				exit("Sharding plugin only works on mongos.");
			}
		} catch (Exception $e) {
			exit("Sharding plugin only works on mongos.");
		}
	}
	
	/**
	 * List all shards
	 *
	 */
	public function doIndex() {
		$ret = $this->_mongo->selectDB("admin")->command(array( "listshards" => 1 ));
		$this->shards = $ret["shards"];
		$this->display();
	}
	
	/**
	 * Add a sharding server
	 *
	 */
	public function doAdd() {
		$this->server = "127.0.0.1";
		$this->port = 27017;
		$this->name = "";
		$this->max_size = "";
		$this->replica_name = "";
		
		if ($this->isPost()) {
			$this->server = x("server");
			$this->port = xi("port");
			$this->name = x("name");
			$this->max_size = doubleval(x("max_size"));
			$this->replica_name = trim(xn("replica_name"));
			
			$server = $this->server;
			if ($this->port > 0) {
				$server .= ":" . $this->port;
			}
			if ($this->replica_name != "") {
				$server = $this->replica_name .  "/" . $server;
			}
			$command = array(
				"addshard" => $server
			);
			if ($this->name) {
				$command["name"] = $this->name;
			}
			if ($this->max_size > 0) {
				$command["maxSize"] = $this->max_size;
			}

			//run command
			$this->ret = $this->_highlight($this->_mongo->selectDB("admin")
				->command($command), "json");
		}
		
		$this->display();
	}
	
	/**
	 * Remove a sharding server
	 *
	 */
	public function doRemove() {
		$host = xn("host");
		
		$ret = $this->_mongo->selectDB("admin")->command(array(
			"removeshard" => $host
		));
		
		$this->ret = $this->_highlight($ret, "json");
		
		$this->display();
	}
	
	/**
	 * Enable sharding on a DB
	 *
	 */
	public function doEnableDb() {
		$this->db = xn("db");
		
		$ret = $this->_mongo->selectDB("admin")->command(array(
			"enablesharding" => $this->db
		));
		
		$this->ret = $this->_highlight($ret, "json");
		
		$this->display();
	}
	
	/**
	 * Sharding collection
	 */
	public function doCollection() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		//already sharded?
		$one = $this->_mongo
			->selectDB("config")
			->selectCollection("collections")
			->findOne(array( "_id" => $this->db . "." . $this->collection ));
		if (!empty($one)) {
			$this->sharded = true;
			$this->ret = $this->_highlight($one, "json");
			$this->display();
			return;
		}
		
		$this->sharded = false;
		$this->key = trim(xn("key"));
		$this->is_unique = xi("is_unique");
		
		if ($this->isPost()) {
			$cmd = array( "shardcollection" => xn("namespace") );
			if ($this->is_unique) {
				$cmd["unique"] = true;
			}
			
			//key
			$keys = preg_split("/\s*,\s*/", $this->key);
			$cmd["key"] = array();
			foreach ($keys as $key) {
				$cmd["key"][$key] = 1;
			}
			
			$this->ret = $this->_highlight($this->_mongo->selectDB("admin")->command($cmd), "json");
		}
		
		$this->display();
	}
}

?>