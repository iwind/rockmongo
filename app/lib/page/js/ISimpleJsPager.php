<?php
/**
 * 简单风格的JS分页类
 *
 * @since 1.0
 * @package if
 * @subpackage plugin.pager.js
 */
class ISimpleJsPager extends IJsPager {
	function __toString() {
		$jsFunc = $this->jsFunc();
    	$page = array();
        $page["input"] = $this->message("pager_input_pageno") . " <input type=\"text\" name=\"pageno\" onblur=\"{$jsFunc}(this.value,this)\" size=\"3\" maxlength=\"10\" />  ";
        $page["no"] = sprintf($this->message("pager_current_pageno"), $this->current()) . ", " . sprintf($this->message("pager_total_page"), $this->length()) . " |  ";
        $page["first"] = ($this->length() > 0) ? "<a href=\"\" onclick=\"{$jsFunc}(1,this);return false;\" target=\"_self\">" . $this->message("pager_first") . "</a>  ":$this->message("pager_first") . "  ";
        $page["pre"] = ($this->current() > 1)? "| <a href=\"\" onclick=\"{$jsFunc}(" . ($this->current() - 1) . ",this);return false;\" target=\"_self\">" . $this->message("pager_prev") . "</a> ":"| " . $this->message("pager_prev") . " ";
        $page["next"] = ($this->current() <= $this->length() - 1)?"| <a href=\"\" onclick=\"{$jsFunc}(" . ($this->current() + 1) . ",this);return false;\" target=\"_self\">" . $this->message("pager_next") . "</a> ":"| " . $this->message("pager_next") . " ";
        $page["last"] = ($this->length() > 0)?"| <a href=\"\" onclick=\"{$jsFunc}(" . $this->length() . ",this);return false;\" target=\"_self\">" . $this->message("pager_last") . "</a>":"| " . $this->message("pager_last");

        //构造页码显示
        $string = implode("", $page);
        
        return $string;
	}
}

?>