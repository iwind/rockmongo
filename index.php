<?php

###########CONFIGRATION BEGIN######################

//configure servers
$MONGO = array();
$MONGO["servers"] = array(
	array(
		"host" => "192.168.1.251",//MongoDB host ip or domain name
		"port" => "27017",//MongoDB port
		"username" => null,//MongoDB username
		"password" => null,//MongoDB password
		"admins" => array( 
			"admin" => "admin", //Administrator's USERNAME => PASSWORD
			//"iwind" => "123456",
		)
	),

	/**array(
		"host" => "192.168.1.5",
		"port" => "27017",
		"username" => null,
		"password" => null,
		"admins" => array( 
			"admin" => "admin"
		)
	),**/
);

###########CONFIGRATION END######################

//default settings, you need not change them in current version
define("__LANG__", "en_us");
define("ROCK_MONGO_VERSION", "1.0.1");
error_reporting(E_ALL | E_STRICT);

//detect environment
if (!version_compare(PHP_VERSION, "5.0")) {
	exit("To make things right, you must install PHP5");
}
if (!class_exists("Mongo")) {
	exit("To make things right, you must install php_mongo module");
}

//rock roll
require "rock.php";
Rock::start();

?>