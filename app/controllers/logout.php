<?php

import("classes.BaseController");

class LogoutController extends BaseController {
	public function onBefore() {
		
	} 
	
	/** log out from system **/
	function doIndex() {
		session_destroy();
		$this->redirect("login.index");
	}
}
	
?>