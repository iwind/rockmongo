<?php

###########CONFIGRATION BEGIN###########################
//Notice: configurations have been moved to [config.php], 
//        so that we can upgrade RockMongo more easily.
###########CONFIGRATION END#############################

//default settings, you need not change them in current version
if (isset($_COOKIE["ROCK_LANG"])) {
	define("__LANG__", $_COOKIE["ROCK_LANG"]);
}
else {
	define("__LANG__", "en_us");
}
define("ROCK_MONGO_VERSION", "1.0.7");
error_reporting(E_ALL);

//detect environment
if (!version_compare(PHP_VERSION, "5.0")) {
	exit("To make things right, you must install PHP5");
}
if (!class_exists("Mongo")) {
	exit("To make things right, you must install php_mongo module. <a href=\"http://www.php.net/manual/en/mongo.installation.php\" target=\"_blank\">Here for installation documents on PHP.net.</a>");
}

//rock roll
require "config.php";
require "rock.php";
Rock::start();

?>