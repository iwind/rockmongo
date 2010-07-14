<?php
/**
 * YAHOO搜索风格的JS分页类
 *
 * @since 1.0
 * @package if
 * @subpackage plugin.pager.js
 */
class IYahooJsPager extends IJsPager {
	/**
	 * 转换为字符串
	 *
	 * @return string
	 */
	function __toString() {
		$jsFunc = $this->jsFunc();
		
        $pages = array();
        $pageNum = $this->length();
        $currPageNo = $this->current();
        $query = $this->query();
        $size = $this->size();
        $total = $this->total();
        $pageSetNum = 11;
        $middlePageNum = ceil($pageSetNum/2);
		if ($pageNum > 0) {
			if ($currPageNo <= $middlePageNum) {
				$start = 1;
				$end = min($pageNum, $pageSetNum);
			}
			else if ($currPageNo + $middlePageNum - 1 > $pageNum) {
				$start = max(1, $pageNum - $pageSetNum - 1);
				$end = $pageNum;
			}
			else {
				$start = max(1, $currPageNo - $middlePageNum);
				$end = min($currPageNo + $middlePageNum - 1, $pageNum);
			}
			if ($currPageNo > 1) {
				$pages[] = "<a href=\"#\" onclick=\"{$jsFunc}(" . ($currPageNo - 1)  . ",this);return false;\" title=\"Previous {$size}\" class=\"prev_page\">" . $this->message("pager_prev") . "</a>&nbsp; ";
			}
			for ($i = $start; $i <= $end; $i++) {
				$_start = $size * ($i - 1) + 1;
				$_end = min($size * $i, $total);
				if ($i != $currPageNo) {
					$pages[] = "<a href=\"#\" onclick=\"{$jsFunc}(" . ($i) . ",this);return false;\" title=\"Results {$_start} - {$_end}\">{$i}</a>";
				}
				else {
					$pages[] = "<span><a href=\"#\" onclick=\"{$jsFunc}(" . ($i) . ",this);return false;\" title=\"Results {$_start} - {$_end}\">{$i}</a></span>";
				}
			}
			if ($currPageNo < $pageNum) {
				$pages[] = " &nbsp;<a href=\"#\" onclick=\"{$jsFunc}(" . ($currPageNo + 1) . ",this);return false;\" title=\"Next {$size}\" class=\"next_page\">" . $this->message("pager_next") . "</a>";
			}
		}
		
       	$string = implode(" &nbsp;", $pages);
        
        return $string;
	}
}

?>