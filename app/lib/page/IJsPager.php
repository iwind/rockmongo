<?php
/**
 * JS分页接口
 *
 * @since 1.0
 * @package if
 * @subpackage plugin.pager
 */
abstract class IJsPager extends IPageStyle {
	private $jsFunc;
	
	/**
	 * 设置JS回调函数
	 *
	 * @param string $jsFunc JS回调函数名
	 * @return IJsPager
	 * @since 1.0
	 */
	function setJsFunc($jsFunc) {
		$this->jsFunc = $jsFunc;
		return $this;
	}
	
	protected function jsFunc() {
		return $this->jsFunc;
	}
}

?>