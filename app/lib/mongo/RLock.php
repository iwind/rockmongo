<?php
import("@.RDb");

/**
 * 轻量级的锁实现
 *
 */
class RLock {
	private $_id;
	private $_key;
	private $_maxLife;
	
	/**
	 * 构造器
	 *
	 * @param string $id 锁ID
	 * @param integer $maxLife 生命周期
	 */
	function __construct($id, $maxLife = 30) {
		$this->_id = $id;
		$this->_maxLife = $maxLife;
		$this->_key = "LOCK$$$" . md5($this->_id);
	}
	
	/**
	 * 取得当前锁的最大生命周期
	 *
	 * @return integer
	 */
	function maxLife() {
		return $this->_maxLife;	
	}
	
	/**
	 * 开始锁
	 *
	 * @return boolean
	 */
	function start() {
		if (!RDb::rock()->write($this->_key, 1, null, $this->_maxLife)) {
			import("@.RMongoException");
			throw new RMongoException("Can not start lock with id '{$this->_id}'");
		}
		return true;
	}
	
	/**
	 * 释放锁
	 *
	 * @return boolean
	 */
	function release() {
		return RDb::rock()->delete($this->_key);
	}
	
	/**
	 * 是否有效
	 *
	 * @return boolean
	 */
	function isValid() {
		return (bool)RDb::rock()->read($this->_key);
	}
	
	/**
	 * 根据锁ID查找锁
	 *
	 * @param string $lockId 锁ID
	 * @param integer $maxLife 生命周期
	 * @return RLock
	 */
	static function lockWithId($lockId, $maxLife = 30) {
		return new RLock($lockId, $maxLife);
	}
	
	/**
	 * 等待某个锁的释放
	 *
	 * @param RLock $lock 被等待的锁
	 * @return boolean 是否成功
	 */
	static function waitLock(RLock $lock) {
		$i = 0;
		while($lock->isValid()) {
			$i ++;
			if ($i > $lock->maxLife() * 1000) {//超过锁的最大生命周期就失效
				return false;
			}
			usleep(2000);
		}
		
		return true;
	}
}

?>