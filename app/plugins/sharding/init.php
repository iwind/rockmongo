<?php

function sharding_server_menu_filter ($items) {
	$items[] = array(
		"action" => "@sharding.index.index",
		"params" => array(),
		"name" => "Sharding"
	);
}

function sharding_db_menu_filter($items, $dbName) {
	if (in_array($dbName, array( "admin", "local", "config"))) {
		return;
	}
	
	//Is enabled?
	$server = MServer::currentServer();
	$one = $server->mongo()->selectDB("config")->selectCollection("databases")->findOne(array( "_id" => $dbName ));
	if (empty($one)) {
		return;
	}
	
	if (!$one["partitioned"]) {
		$items[] = array(
			"action" => "@sharding.index.enableDb",
			"params" => array( "db" => $dbName ),
			"name" => "<font color=\"red\">EnableSharding</font>"
		);
	}
	else {
		$items[] = array(
			"params" => array( "db" => $dbName ),
			"name" => "<font color=\"green\">ShardingEnabled</font>"
		);
	}
}

function sharding_collection_menu_filter($items, $dbName, $collectionName) {
	//sharded?
	$server = MServer::currentServer();
	$namespace = $dbName . "." . $collectionName;
	$one = $server->mongo()->selectDB("config")->selectCollection("collections")->findOne(array( "_id" => $namespace ));

	if (empty($one)) {
		$items[] = array(
			"action" => "@sharding.index.collection",
			"params" => array( "db" => $dbName, "collection" => $collectionName ),
			"name" => "<font color=\"red\">Sharding</font>"
		);
	}
	else {
		$items[] = array(
			"action" => "@sharding.index.collection",
			"params" => array( "db" => $dbName, "collection" => $collectionName ),
			"name" => "<font color=\"green\">Sharding</font>"
		);
		
		//chunks
		$format = rock_cookie("rock_format");
		$criteria = array( "ns" => $namespace );;
		import("classes.VarExportor");
		$exportor = new VarExportor($server->mongo()->selectDB("config"), $criteria);
		if ($format == "php") {
			$criteria = $exportor->export("php");
		}
		else {
			$criteria = $exportor->export("json");
		}
		$items[] = array(
			"action" => "collection.index",
			"params" => array( "db" => "config", "collection" => "chunks", "criteria" => $criteria ),
			"name" => "Chunks"
		);
	}
}

RFilter::add("SERVER_MENU_FILTER", "sharding_server_menu_filter");
RFilter::add("DB_MENU_FILTER", "sharding_db_menu_filter");
RFilter::add("COLLECTION_MENU_FILTER", "sharding_collection_menu_filter");

?>