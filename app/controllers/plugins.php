<?php


import("classes.BaseController");

class PluginsController extends BaseController {
	public function doIndex() {
		
		//All plugins
		$this->plugins = RPlugin::plugins();
		
		$this->display();
	}
}

?>