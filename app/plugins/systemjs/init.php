<?php

if (x("action") != "collection.index") {
	return;
}
$collection = xn("collection");
if ($collection != "system.js") {
	return;
}

function systemjs_collection_menu_filter($items, $dbName, $collectionName) {
	$items[] = array(
		"action" => "@systemjs.index.add",
		"params" => array( "db" => $dbName, "collection" => $collectionName ),
		"name" => "New Function"
	);
	$items[] = array(
		"url" => "http://www.mongodb.org/display/DOCS/Server-side+Code+Execution#Server-sideCodeExecution-Storingfunctionsserverside",
		"name" => "Help"
	);
}

function systemjs_doc_menu_filter($items, $dbName, $collectionName, $docId, $docIndex) {
	$items[] = array(
		"action" => "@systemjs.index.modify",
		"params" => array( "db" => $dbName, "collection" => $collectionName, "docId" => $docId ),
		"name" => "Modify Function"
	);
	$items[] = array(
		"action" => "@systemjs.index.test",
		"params" => array( "db" => $dbName, "collection" => $collectionName, "docId" => $docId ),
		"name" => "Test Function"
	);
}

RFilter::add("COLLECTION_MENU_FILTER", "systemjs_collection_menu_filter");
RFilter::add("DOC_MENU_FILTER", "systemjs_doc_menu_filter");

?>