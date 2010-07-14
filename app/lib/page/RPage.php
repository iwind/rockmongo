<?php
/**
 * 分页抽象类
 *
 * <code>
 * $pager = new RPage();
 * $pager->total(100);
 * $pager->setSize(21);
 * $pager->setKeyword("page");
 * $pager->setPath("pager.php");
 * $pager->setQuery("a=b&pager=%{PAGE_NO}&d=1");
 * echo $pager;
 * echo "offset:" . $pager->offset();
 * </code>
 * 
 * @author iwind <iwind.iwind@gmail.com>
 * @link http://ifphp.cn
 * @version $Id$
 * @package rock
 * @subpackage plugin.pager
 */

/**
 * 分页抽象类
 * 
 * 实现了RPage接口的一些方法
 *
 * @version $Id$
 * @package if
 * @subpackage plugin.pager
 */
abstract class RPage {
	private $keyword;
	private $total;
	private $path;
	private $size;
	private $properties;
	private $query;
	private $length;
	private $pageSetSize = 11;
	private $rows = array();
	
	/**
	 * 是否加载了本地化对象
	 *
	 * @var boolean
	 * @since 1.0
	 */
	private $localeLoaded = false;
	
	/**
	 * 语言配置
	 *
	 * @var array
	 * @since 1.0
	 */
	private $messages = array();
	
	/**
	 * 分页中代码当前页码的常量
	 *
	 */
	const PAGER_VARIABLE_STRING = "%{PAGE_NO}";		
	
	/**
	 * 构造器
	 *
	 * @since 2.0
	 */
	function __construct() {
		$this->path = $_SERVER["PHP_SELF"];
	}
	
	/**
	 * 取得当前页码，第一页为1
	 * 
	 * @return integer
	 */	
	function current() {
		$keyword = $this->keyword();
		$pageNo = intval(x($keyword));
		if ($pageNo <= 0) {
			$pageNo = 1;
		}
		return min($pageNo, $this->length());
	}
	
	/**
	 * 取得下一页页码
	 *
	 * @return integer
	 */
	function next() {
		$length = $this->length();
		$current = $this->current();
		return $current < $length ? ($current + 1) : $length;
	}
	
	/**
	 * 取得上一页页码
	 *
	 * @return integer
	 */
	function prev() {
		$length = $this->length();
		$current = $this->current();
		return $current > 1 ? ($current - 1) : 1;
	}
	
	/**
	 * 取得记录开始的偏移量
	 *
	 * @return integer
	 */	
	function offset() {
		$offset = $this->size() * ($this->current() - 1);
		if ($offset < 0) {
			$offset = 0;
		}
		if($offset >= $this->total()){
            $offset = max($this->size () * ($this->length () - 1), 0);
        }
		return $offset;
	}
	
	/**
	 * 设置内容总数
	 *
	 * @param integer $total 内容总数
	 * @return RPage
	 */	
	function setTotal($total) {
		$this->total =  intval($total);
		if ($this->total < 0) {
			throw new IException("content total '{$total}' can't be small than 0", 0x100027, "条目总数 '{$total}' 不能小于 0");
		}
		return $this;
	}
	
	/**
	 * 数据总数
	 *
	 * @return integer
	 * @since 1.0
	 */
	function total() {
		return $this->total;
	}
	
	/**
	 * 设置分页链接中的关键字
	 *
	 * @param string $keyword 关键字
	 * @return RPage
	 */	
	function setKeyword($keyword) {
		$this->keyword = $keyword;
		return $this;
	}
	
	/**
	 * 取得分页用的关键字
	 *
	 * 从1.0开始，如果没有关键字，则默认为page
	 * 
	 * @return string
	 */
	function keyword() {
		if (!$this->keyword) {
			$this->keyword = "page";
		}
		return $this->keyword;
	}
	
	/**
	 * 设置每页记录数
	 *
	 * @param integer $size 大于0的数字
	 * @return RPage
	 */	
	function setSize($size) {
		$this->size = intval($size);
		if ($this->size < 1) {
			throw new IException("page size '{$size}' can't be small than 1", 0x100028, "分页尺寸 '{$size}' 不能小于 1");
		}
		return $this;
	}
	
	/**
	 * 取得每页记录数
	 *
	 * @return integer
	 */	
	function size() {
		if ($this->size < 1) {
			$this->size = 10;
		}
		return $this->size;
	}
	
	/**
	 * 设置链接的路径
	 *
	 * @param string $path 路径
	 * @return RPage
	 */	
	function setPath($path) {
		$this->path = $path;
		return $this;
	}
	
	/**
	 * 取得程序路径
	 *
	 * @return string
	 * @since 1.0
	 */
	function path() {
		return $this->path;
	}
	
	/**
	 * 设置属性
	 *
	 * @param array $properties 属性列表
	 * @return RPage
	 */	
	function setProperties(array $properties) {
		$this->properties = $properties;
		return $this;
	}
	
	/**
	 * 取得设置的属性
	 *
	 * @return array
	 * @since 1.0
	 */
	function properties() {
		return $this->properties;
	}
	
	/**
	 * 设置查询
	 *
	 * @param mixed $query string|array
	 * @return RPage
	 */	
	function setQuery($query) {
		if (is_array($query)) {
			$_query = array();
			foreach ($query as $key => $value) {
				if ($key == $this->keyword()) {
					continue;
				}
				if (is_array($value)) {
					foreach ($value as $key1=>$value1) {
						$_query[] = "{$key}[]=" . urlencode($value1);
					}
				}
				else {
					$_query[] = "{$key}=" . urlencode($value);
				}
			}
			$query = implode("&", $_query);
		}
		$this->query = $query;
		return $this;
	}
	
	/**
	 * 添加查询条件
	 * 
	 * <code>
	 * $page->addQuery(array(
	 *		 "e" => 5,
	 *		 "f" => 6
	 *	));
	 *	$page->addQuery("g=7");
	 * </code>
	 *
	 * @param mixed $query string|array
	 * @return RPage
	 * @since 1.0.3
	 */		
	function addQuery($query) {
		if (is_array($query)) {
			$_query = array();
			foreach ($query as $key => $value) {
				if ($key == $this->keyword()) {
					continue;
				}
				if (is_array($value)) {
					foreach ($value as $key1=>$value1) {
						$_query[] = "{$key}[]=" . urlencode($value1);
					}
				}
				else {
					$_query[] = "{$key}=" . urlencode($value);
				}
			}
			$query = implode("&", $_query);
		}
		$this->query .= ($this->query ? "&" : "") . $query;
		return $this;
	}
	
	/**
	 * 开启自动构造查询条件功能
	 *
	 * @param boolean $bool 是否开启该功能
	 * @param string|array $except 要去除的参数名
	 * @param string|array $only 限制的参数名
	 * @return RPage
	 * @since 1.0.3
	 */
	function setAutoQuery($bool = true, $except = "", $only = "") {
		if ($bool) {
			$x = xn();
			foreach ($x as $name => $value) {
				if ($except && if_in_array($name, $except)) {
					unset($x[$name]);
				}
				if ($only && !if_in_array($name, $only)) {
					unset($x[$name]);
				}
			}
			$this->setQuery($x);
		}
		return $this;
	}
	
	/**
	 * 取得查询
	 *
	 * @return array
	 * @since 1.0
	 */
	function query() {
		return $this->query;
	}
	
	/**
	 * 取得一个分页好号对应的URL
	 *
	 * @param integer $pageNo 分页号
	 * @return string
	 * @since 1.0
	 */
	function url($pageNo) {
		$query = $this->query();
		if (strstr($query, self::PAGER_VARIABLE_STRING)) {
			$query = str_replace(self::PAGER_VARIABLE_STRING, $pageNo, $query);
		}
		else {
			if ($query == "") {
				$query = $this->keyword() . "=" . $pageNo;
			}
			else {
				$query .= "&" . $this->keyword() . "=" . $pageNo;
			}
		}
		return $this->path() . "?" . $query;
	}
	
	/**
	 * 取得总分页数
	 *
	 * @return integer
	 * @since 1.0
	 */
	function length() {
		if ($this->size() == 0) {
			return 0;
		}
		return ceil($this->total()/$this->size());
	}
	
	/**
	 * 添加记录
	 *
	 * @param mixed $row 记录
	 * @return RPage
	 */
	function addRow($row) {
		$this->rows[] = $row;
		return $this;
	}
	
	/**
	 * 添加记录集
	 *
	 * @param array $rows 记录集
	 * @return RPage
	 */	
	function addRows(array $rows) {
		foreach ($rows as $row) {
			$this->rows[] = $row;
		}
		return $this;
	}
	
	/**
	 * 取得记录集
	 *
	 * @return array
	 */	
	function rows() {
		return $this->rows;
	}
	
	/**
	 * 设置记录集
	 *
	 * @param array|iterable $rows 记录集
	 * @return RPage
	 */	
	function setRows($rows) {
		$this->rows = $rows;
		return $this;
	}
	
	/**
	 * 取得键值对应的消息文本
	 *
	 * @param string $key 键值
	 * @return string
	 * @since 1.0
	 */
	protected function message($key) {
		if (!$this->localeLoaded) {
			$locale = __LANG__;
			if (!$locale) {
				$locale = "default";
			}
			
			$message = x("~" . $key);
			if ($message) {
				return $message;
			}
			
			//简写
			$dirname = dirname(__FILE__) . "/lang";
			$langFile = $dirname . "/" . $locale . ".php";
			if (is_file($langFile)) {
				require($langFile);		
				$this->messages = $message;	
			}
			$this->localeLoaded = true;
		}
		if (is_array($this->messages) && array_key_exists($key, $this->messages)) {
			return $this->messages[$key];
		}
		return null;
	}
	
	/**
	 * 转换成字符串
	 *
	 * @return string
	 */	
	public abstract function __toString();
	
	/**
	 * 设置分页集尺寸
	 *
	 * @param integer $num 大于1
	 * @return RPage
	 * @since 1.0
	 */
	function setPageSetNum($num){
		$this->pageSetSize = $num;
		return $this;
	}
	
	/**
	 * 取得分页集尺寸
	 *
	 * @return integer
	 * @since 1.0
	 */
	function pageSetNum(){
		return $this->pageSetSize;
	}
	
	static function pageWithStyle($style, array $params = null) {
		exit(__METHOD__ . " need to be implemented.");
	}
}


?>