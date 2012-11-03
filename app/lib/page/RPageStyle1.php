<?php
/**
 * 仿雅虎搜索的分页
 *
 * @author iwind <iwind.iwind@gmail.com>
 * @link http://ifphp.cn
 * @version $Id$
 * @package if
 * @subpackage plugin.pager
 */

/**
 * 仿Yahoo搜索的分页
 *
 * @version $Id$
 * @package if
 * @subpackage plugin.pager
 * @since 1.0
 */
import("@.RPage");
class RPageStyle1 extends RPage {
	function __toString() {
        $pages = array();
        $pageNum = $this->length();
        $currPageNo = $this->current();
        $query = $this->query();
        $size = $this->size();
        $total = $this->total();
        $pageSetNum = $this->pageSetNum();
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
			if ($pageNum > 1) {
				$pages[] = "<a href=\"" . $this->url(1)  . "\" title=\"First Page\">&laquo;</a>&nbsp; ";
			}
			if ($currPageNo > 1) {
				$pages[] = "<a href=\"" . $this->url($currPageNo - 1)  . "\" title=\"Previous {$size}\">" . $this->message("pager_prev") . "</a>&nbsp; ";
			}
			for ($i = $start; $i <= $end; $i++) {
				$_start = $size * ($i - 1) + 1;
				$_end = min($size * $i, $total);
				if ($i != $currPageNo) {
					$pages[] = "<a href=\"" . $this->url($i) . "\" title=\"Results {$_start} - {$_end}\">{$i}</a>";
				}
				else {
					$pages[] = "<span><a href=\"" . $this->url($i) . "\" title=\"Results {$_start} - {$_end}\">{$i}</a></span>";
				}
			}
			if ($currPageNo < $pageNum) {
				$pages[] = " &nbsp;<a href=\"" . $this->url($currPageNo + 1) . "\" title=\"Next {$size}\">" . $this->message("pager_next") . "</a>";
			}
			if ($pageNum > 1) {
				$pages[] = "<a href=\"" . $this->url($pageNum)  . "\" title=\"Last Page\">&raquo;</a>&nbsp; ";
			}
		}
		
       	$string = implode(" &nbsp;", $pages);
        
        return $string;
	}
}