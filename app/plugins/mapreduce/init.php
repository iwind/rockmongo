<?php


function mapreduce_collection_menu_filter($items, $dbName, $collectionName) {
	$items[] = array(
		"action" => "@mapreduce.index.index",
		"name" => "MapReduce",
		"params" => array( "db" => $dbName, "collection" => $collectionName ),		
	);
}

function mapreduce_manual_menu_filter($items) {
	$items[] = '<a href="http://www.mongodb.org/display/DOCS/MapReduce" target="_blank">MapReduce</a>';
}

RFilter::add("COLLECTION_MENU_FILTER", "mapreduce_collection_menu_filter");
RFilter::add("MANUAL_MENU_FILTER", "mapreduce_manual_menu_filter");