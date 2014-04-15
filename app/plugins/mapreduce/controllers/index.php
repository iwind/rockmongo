<?php

import("classes.BaseController");

class IndexController extends BaseController {
	public function doIndex() {
		$this->db_name = xn("db");
		$this->collection_name = xn("collection");
		
		$this->map_function = xn("map_function");
		$this->reduce_function = xn("reduce_function");
		$this->query_filter = xn("query_filter");
		$this->out_options = xn("out_options");
		$this->keeptemp = xn("keeptemp");
		$this->jsmode = xn("jsmode");
		$this->verbose = xn("verbose");
		$this->sort = xn("sort");
		$this->limit = xi("limit");
		$this->finalize_function = xn("finalize_function");
		$this->scope_vars = xn("scope_vars");
		
		if (!$this->map_function) {
			$this->map_function = 'function () {}';
		}
 		if (!$this->reduce_function) {
 			$this->reduce_function = 'function (key, values) {}';
 		}
		if (!$this->out_options) {
			$this->out_options = '{ inline:1 }';
		}
		
		if ($this->isPost()) {
			$db = $this->_mongo->selectDB($this->db_name);
			$command = '
				 
					 { mapreduce : "' . $this->collection_name . '",' .
					   'map : ' . $this->map_function . ',
					   reduce : ' . $this->reduce_function;
			if ($this->query_filter) {
				$command .= ', query : ' . $this->query_filter;	
			}
			if ($this->sort) {
				$command .= ',sort : ' . $this->sort;		
			}
			if ($this->limit > 0) {
				$command .= ', limit: ' . $this->limit;	
			}

			if ($this->out_options) {
				$command .= ', out : ' . $this->out_options;
			}
			if ($this->keeptemp) {
				$command .= ', keeptemp:' . $this->keeptemp;
			}
			if ($this->finalize_function) {
				$command .= ', finalize: ' . $this->finalize_function;
			}
			if ($this->scope_vars) {
				$command .= ', scope:' . $this->scope_vars;
			}
			if ($this->jsmode) {
				$command .= ', jsMode : ' . $this->jsmode;
			}
			if ($this->verbose) {
				$command .= ', verbose : ' . $this->verbose;
			}
			$command .= ' }';
			
			$this->command = json_format_html($command);
			$ret = $db->execute('function() {} { return db.runCommand(' . $command . ');}');
			
			if ($ret["ok"]) {
				$this->message = $this->_highlight($ret, "json");
			}
			else {
				$this->error = $this->_highlight($ret, "json");
			}
		}
		
		$this->display();
	}
}