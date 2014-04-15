<?php

import("classes.BaseController");

class IndexController extends BaseController {
	public function doAdd() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		$this->name = trim(xn("name"));
		$this->body = trim(xn("body"));
		
		if ($this->isPost()) {
			if (!$this->name) {
				$this->error = "Please enter your function name";
				$this->display();
				return;
			}
			
			if (!$this->body) {
				$this->error = "Please enter you function body";
				$this->display();
				return;
			}
			
			$ret = $this->_mongo->selectDB($this->db)
				->execute("function () { return (function (){ {$this->body}; }); }");
			if (!$ret["ok"]) {
				$this->error = $ret["errmsg"];
				$this->display();
				return;
			}	
				
			$ret = $this->_mongo->selectDB($this->db)
					->selectCollection($this->collection)
					->insert(array( "_id" => $this->name, "value" => new MongoCode($this->body) ));
					
			if ($ret) {
				$this->message = "Function created successfully";
			}
			else {
				$this->error = "Failed to create function";
			}
		}
		
		$this->display();
	}
	
	public function doModify() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		$docId = rock_real_id(xn("docId"));
		$this->func = $this->_mongo->selectCollection($this->db, $this->collection)
			->findOne(array( "_id" => $docId ));
		$this->body = $this->func["value"]->__toString();
			
		if ($this->isPost()) {
			$this->body = trim(xn("body"));	
			if (!$this->body) {
				$this->error = "Please enter you function body";
				$this->display();
				return;
			}
				
			$ret = $this->_mongo->selectDB($this->db)
					->selectCollection($this->collection)
					->update(array( "_id" => $docId ), array( "value" => new MongoCode($this->body) ));
					
			if ($ret) {
				$this->message = "Function modified successfully";
			}
			else {
				$this->error = "Failed to create function";
			} 
		}

		$this->display();
	}
	
	public function doTest() {
		$this->db = xn("db");
		$this->collection = xn("collection");
		
		$docId = rock_real_id(xn("docId"));
		
		$this->func = $this->_mongo->selectCollection($this->db, $this->collection)
			->findOne(array( "_id" => $docId ));
			
		//parse args
		$this->args = xi("args");
		if ($this->args == 0) {
			$body = $this->func["value"]->__toString();
			if (preg_match("/function \\((.*)\\)/", $body, $match)) {
				$this->args = count(explode(",", $match[1]));
			}
			else {
				$this->args = 1;
			}
		}
			
		if ($this->isPost()) {
			$this->params = xn("param");
			$this->types = xn("type");
			
			$mongodb = $this->_mongo->selectDB($this->db);
			
			$params = array();
			foreach ($this->types as $index => $type) {
				$param = $this->params[$index];
				$params[] = rock_real_value($mongodb, $type, "json", $this->params[$index]);
			}
			
			$ret = $mongodb->execute($this->func["value"], $params);
			$this->ret = $this->_highlight($ret, "json");
		}
			
		$this->display();
	}
}

?>