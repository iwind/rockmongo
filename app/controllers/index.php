<?php

import("classes.BaseController");

class IndexController extends BaseController {
	/** home **/
	public function doIndex() {
		$this->redirect("admin.index");
	}
}


?>