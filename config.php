<?php

//configure servers
$MONGO = array();
$MONGO["servers"] = array(
	array(
		"host" => "127.0.0.1",//replace your MongoDB host ip or domain name here
		"port" => "27017",//MongoDB connection port
		"username" => null,//MongoDB connection username
		"password" => null,//MongoDB connection password
		"admins" => array( 
			"admin" => "admin", //Administrator's USERNAME => PASSWORD
			//"iwind" => "123456",
		)
	),

	/**array(
		"host" => "192.168.1.1",
		"port" => "27017",
		"username" => null,
		"password" => null,
		"admins" => array( 
			"admin" => "admin"
		)
	),**/
);

?>