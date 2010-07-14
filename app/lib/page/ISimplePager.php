<?php
/**
 * 简单分页类
 *
 * @author iwind <iwind.iwind@gmail.com>
 * @link http://ifphp.cn
 * @version $Id$
 * @package if
 * @subpackage plugin.pager
 */

/**
 * 简单分页类
 * 
 * 下面是一个简单的例子
 * <code>
 * $pager = new ISimplePager();
 * $pager->total(100);
 * $pager->setSize(21);
 * $pager->setKeyword("pager");
 * $pager->setPath("pager.php");
 * $pager->setQuery("a=b&pager=%{PAGE_NO}&d=1");
 * echo $pager;
 * echo "offset:" . $pager->offset();
 * </code>
 * 
 * @version $Id$
 * @package if
 * @subpackage plugin.pager
 */
class ISimplePager extends IPageStyle {
	function __toString() {
    	$page = array();
        $page["input"] = $this->message("pager_input_pageno") . " <input type=\"text\" name=\"pageno\" onblur=\"window.location='" . $this->url(IPageStyle::PAGER_VARIABLE_STRING) .  "'.replace('" . IPageStyle::PAGER_VARIABLE_STRING . "',this.value);\" size=\"3\" maxlength=\"10\" />  ";
        $page["no"] = sprintf($this->message("pager_current_pageno"), $this->current()) . ", " . sprintf($this->message("pager_total_page"), $this->length()) . " |  ";
        $page["first"] = ($this->length() > 0) ? "<a href=\"" . $this->url(1) . "\" target=\"_self\">" . $this->message("pager_first") . "</a>  ":$this->message("pager_first") . "  ";
        $page["pre"] = ($this->current() > 1)? "| <a href=\"" . $this->url($this->current() - 1) . "\" target=\"_self\">" . $this->message("pager_prev") . "</a> ":"| " . $this->message("pager_prev") . " ";
        $page["next"] = ($this->current() <= $this->length() - 1)?"| <a href=\"" . $this->url($this->current() + 1) . "\" target=\"_self\">" . $this->message("pager_next") . "</a> ":"| " . $this->message("pager_next") . " ";
        $page["last"] = ($this->length() > 0)?"| <a href=\"" . $this->url($this->length()) . "\" target=\"_self\">" . $this->message("pager_last") . "</a>":"| " . $this->message("pager_last");

        //构造页码显示
        $string = implode("", $page);
        
        return $string;
	}
}

?>