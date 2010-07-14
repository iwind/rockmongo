<?php

class RDb {
	private $_uid;
	/**
	 * Enter description here...
	 *
	 * @var Mongo
	 */
	private $_mongo;
	private $_dbname;
	private static $_mongoes = array();
	private static $_pub;
	private static $_users = array();
	private $_cacheTable = "rock_cache";
	private $_versionTable = "rock_version";
	private $_config = array();
	private $_versions = array();
	
	/**
	 * 构造RDb
	 *
	 * @param integer|string $uid 如果小于或等于0，表示取公用的用户
	 */
	function __construct($uid) {
		$this->_uid = $uid;
		list($this->_mongo, $this->_dbname) = $this->_mongo();
	}
	
	/**
	 * 设置操作当前数据库的UID
	 *
	 * @param integer $uid 用户UID
	 */
	function setUid($uid) {
		$this->_uid = $uid;
		list($this->_mongo, $this->_dbname) = $this->_mongo();
	}
	
	/**
	 * 取得当前操作数据库的UID
	 *
	 * @return integer|string
	 */
	function uid() {
		return $this->_uid;
	}	
	
	/**
	 * 选择集合并构造查询
	 *
	 * @param string $collection 集合名
	 * @return RQuery
	 */
	function select($collection) {
		import("@.RQuery");
		$query = new RQuery($this->_mongo, $this->_dbname, $collection);
		return $query;
	}
	
	/**
	 * 当前数据库
	 *
	 * @return MongoDB
	 */
	function db() {
		return $this->_mongo->selectDB($this->_dbname);
	}
	
	/**
	 * 选择集合
	 *
	 * @param string $collection 集合名
	 * @return MongoCollection
	 */
	function collect($collection) {
		return $this->_mongo->selectCollection($this->_dbname, $collection);
	}
	
	private function _mongo() {
		//取uid的第一位
		$prefix = -1;
		if (is_numeric($this->_uid)) {
			if ($this->_uid > 0) {
				$this->_uid = intval($this->_uid);
				$prefix = substr($this->_uid, 0, 1);
			}
		}
		else {
			$prefix = hexdec($this->_uid{0});
		}
		if (isset(self::$_mongoes[$prefix])) {
			return self::$_mongoes[$prefix];
		}
		
		$servers = o("@.config.servers");
		foreach ($servers as $server) {
			if ($server["users"]["min"] <= $prefix && $server["users"]["max"] >= $prefix) {
				$this->_config = $server;
				$host = "mongodb://";
				if ($server["username"] && $server["password"]) {
					$host .= $server["username"] . ":" . $server["password"] . "@";
				}
				$host .= $server["host"] . ":" . $server["port"];
				
				$options = array();
				if ($server["persist"]) {
					$options["persist"] = $server["persist"];
				}
				
				$mongo = new Mongo($host, $options);
				self::$_mongoes[$prefix] = array( $mongo, $server["dbname"] );
				return self::$_mongoes[$prefix];
			}
		}
	}
	
	/**
	 * 取得系统用户
	 *
	 * @return RDb
	 */	
	static function rock() {
		if (self::$_pub) {
			return self::$_pub;
		}
		self::$_pub = new RDb(-1);
		return self::$_pub;
	}
	
	/**
	 * 根据Class构造数据库
	 *
	 * @param integer|string $uid 用户UID
	 * @param string $class 类名称，该类必须继承自RDb，但在程序里没有强制判断
	 * @return RDb
	 */
	static function dbWithUid($uid, $class = __CLASS__) {
		if (isset(self::$_users[$uid])) {
			return self::$_users[$uid];
		}
		$user = new $class($uid);
		self::$_users[$uid] = $user;
		return $user;
	}

	/**
	 * 读取缓存
	 * 
	 * rock_cache:
	 *   * key
	 *   * value
	 *   * maxAge
	 * 或者
	 *   * key
	 *   * IF_TAG
	 *   * tags
	 *   * tagIds
	 *   * value
	 *   * maxAge
	 *
	 * @param string $type 数据类型
	 * @param string $key 缓存键
	 * @param mixed $default 如果没有任何缓存数据，则返回此默认值
	 * @param boolean $apc 是否使用APC
	 * @return mixed
	 */
	function read($type, $key, $default = null, $apc = true) {
		$key = $this->_keyWithVersion($key, $type);
		
		if (!$apc) {
			if (rand(0, 999) == 1) {//1/1000的机会清理1000个过期缓存
				$this->select($this->_cacheTable)
					->lt("maxAge", time())
					->limit(1000)
					->delete();
			}
		}
		
		//APC中是否有值
		$row = null;
		
		//从$GLOBALS中读取
		if (isset($GLOBALS["ROCK_CACHES"][$key])) {
			$row = $GLOBALS["ROCK_CACHES"][$key];
		}
		else if ($apc) {
			$row = apc_fetch($key);
			$GLOBALS["ROCK_CACHES"][$key] = $row;
		}
		else {
			$row = $this->select($this->_cacheTable)->attr("key", $key)->findOne();
			$GLOBALS["ROCK_CACHES"][$key] = $row;
		}
		if (!is_array($row) || empty($row)) {
			return $default;
		}
		
		if (isset($row["IF_TAG"])) {
			$tags = $row["tags"];
			$tagIds = $row["tagIds"];
			if (!empty($tags)) {
				foreach ($tags as $index => $tag) {
					if (!isset($tagIds[$index])) {//已经过期
						$this->delete($type, $key, null, false);
						return $default;
					}
					$tagId = $tagIds[$index];
					$oldTagId = self::rock()->read($type, $tag, null, false);
					if ($oldTagId != $tagId) {//已经过期
						$this->delete($type, $key, null, false);
						return $default;
					}
				}
			}
		}
		
		//是否过期
		if ($row["maxAge"] >= time()) {
			return $row["value"];
		}
		$this->delete($type, $row["key"], null, $apc);
		return $default;
	}

	/**
	 * 写入数据
	 *
	 * @param string $type 逻辑数据类型
	 * @param string $key 键
	 * @param mixed $value 值
	 * @param boolean $apc 是否使用APC缓存
	 * @param integer $ttl 刷新时间
	 * @param array|string $tags 标签
	 * @return boolean
	 */
	function write($type, $key, $value, $apc = true, $ttl = 1209600, $tags = null) {
		$key = $this->_keyWithVersion($key, $type);
		
		$row = array();
		if (is_null($tags) || trim(strval($tags)) === "") {
			$row = array(
				"key" => $key,
				"value" => $value,
				"maxAge" => time() + $ttl
			);
		}
		else {
			if (!is_array($tags)) {
				$tags = preg_split("/[\s,;]+/", $tags);
			}
			$tagIds = array();
			foreach ($tags as $index => $tag) {
				$tagkey = "TAG$$$" . $tag;
				$tags[$index] = $tagkey;
				$tagId = self::rock()->read($type, $tagkey, null, false);
				if (!$tagId) {
					$tagId = strval(microtime(true));
					$bool = self::rock()->write($type, $tagkey, $tagId, false, 30 * 86400);
					if (!$bool) {
						return false;
					}
				}
				$tagIds[] = $tagId;
			}
		
			$row = array(
				"key" => $key,
				"IF_TAG" => 1,
				"tags" => $tags,
				"tagIds" => $tagIds,
				"value" => $value,
				"maxAge" => time() + $ttl
			);
		}
		
		//写入$GLOBALS
		$GLOBALS["ROCK_CACHES"][$key] = $row;
		
		//存入apc
		if ($apc) {
			$newRow = $row;
			unset($newRow["key"]);
			return apc_store($key, $newRow, $ttl);
		}
		
		//查询
		$cache = $this->select($this->_cacheTable)->attr("key", $key)->findId();
		if ($cache) {
			$cache->setAttrs($row);
			return $cache->save();
		}
		else {
			return $this->select($this->_cacheTable)->insert($row);
		}
	}

	/**
	 * 删除缓存. $key和$tags只能选其一.
	 *
	 * @param string $type 数据类型
	 * @param string $key 缓存键
	 * @param string|array $tags 数据标签
	 * @param boolean $apc 是否从APC中读取
	 * @return boolean
	 */
	function delete($type, $key = null, $tags = null, $apc = true) {
		if (is_null($tags) || trim(strval($tags)) === "") {
			if ($key) {
				$key = $this->_keyWithVersion($key, $type);
				unset($GLOBALS["ROCK_CACHES"][$key]);
				if ($apc) {
					return apc_delete($key);
				}
				else {
					return $this->select($this->_cacheTable)->attr("key", $key)->delete();
				}
			}
			return true;
		}
		if (!is_array($tags)) {
			$tags = preg_split("/[\s,;]+/", $tags);
		}
		foreach ($tags as $index => $tag) {
			$tags[$index] = self::rock()->_keyWithVersion($type, "TAG$$$" . $tag);
			unset($GLOBALS["ROCK_CACHES"][$tags[$index]]);
		}
		return self::rock()->select($this->_cacheTable)->attr("key", $tags)->delete();
	}
	
	/**
	 * 取得当前用户对应的MongoDB配置
	 *
	 * @return array
	 */
	function config() {
		return $this->_config;
	}
	
	/**
	 * 更新逻辑数据类型版本
	 *
	 * @param integer $type 数据类型，比如log, message
	 */
	function updateVersion($type) {
		$version = $this->select($this->_versionTable)
			->attr("uid", $this->_uid)
			->result($type)
			->find();
		if (!$version) {
			import("@.RObject");
			$version = new RObject(array(
				"uid" => $this->_uid,
				$type => 1
			));
			$version->setCollection($this->collect($this->_versionTable));
		}
		else {
			$version->increase($type);
		}
		$version->save();
		if (isset($this->_versions[$type])) {
			$this->_versions[$type] = $version->attr($type);
		}
	}
	
	private function _version($type) {
		if (empty($this->_versions)) {
			$this->_versions = $this->select($this->_versionTable)
				->attr("uid", $this->_uid)
				->findOne();
		}
		
		return isset($this->_versions[$type]) ? $this->_versions[$type] : 0;
	}
	
	
	private function _keyWithVersion($key, $type = null) {
		if (is_null($type)) {
			return 0;
		}
	   $version = $this->_version($type);
	   $key = __PLATFORM__ . "://" . $key  . ":" . $version;
	   return $key;
	}
}

?>