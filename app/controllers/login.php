<?php

import("classes.BaseController");

class LoginController extends BaseController {
	public function onBefore() {
		//render header
		if (!$this->isAjax()) {
			render_view("header");
		}
	}
	
	/**
	 * login page and post 
	 */
	public function doIndex() {
		global $MONGO;
		
		$password = trim(xn("password"));
		$this->username = trim(xn("username"));
		$this->db = trim(xn("db"));
		$this->hostIndex = xi("host");
		$this->languages = rock_load_languages();
		$this->expires = array(
			3 => "3 " . rock_lang("hours"),
			720 => "1 " . rock_lang("month"),
		);
		$this->moreOptions = xi("more");
		
		if ($this->isPost()) {
			//server exists?
			if (!isset($MONGO["servers"][$this->hostIndex])) {
				$this->message = "Server does not exist";
				return;
			}

			//authenticate
			$server = MServer::serverWithIndex($this->hostIndex);
			if (!$server->auth($this->username, $password, $this->db)) {
				$this->message = rock_lang("can_not_auth");
				$this->display();
				return;
			}
			
			//remember user
			import("models.MUser");
			MUser::login($this->username, $password, $this->hostIndex, $this->db, xi("expire") * 3600);
			
			//remember lang
			setcookie("ROCK_LANG", x("lang"), time() + 365 * 86400);
			
			//jump to admin page
			$this->redirect("admin.index", array( "host" => $this->hostIndex ));
		}
		else {
			$this->display();
		}
	}	
}

?>