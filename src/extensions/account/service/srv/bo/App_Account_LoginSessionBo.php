<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('EXT:account.service.dm.App_Account_LoginSessionDm');

/**
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id
 * @package account
 */
class App_Account_LoginSessionBo {
	
	
	private $sessionInfo = array();
	private static $_bo = array();

	/** 
	 * 构造函数信息
	 *
	 */
	private function __construct($sessionId) {
		$this->sessionInfo = $this->_getLoginSessionService()->getLoginSession($sessionId);
		$this->updateExpire($sessionId);
	}
	
	/**
	 * 更新过期时间
	 * @param unknown_type $sessionId
	 * @return boolean
	 */
	public function updateExpire($sessionId){
		if(rand(1,2) == 1){
			$expire = $this->_getLoginSessionService()->getCooieExpire() + Pw::getTime();
			$dm = new App_Account_LoginSessionDm($sessionId);
			$dm->setExpire($expire);
			$this->_getLoginSessionDs()->update($dm);
			$this->sessionInfo['expire'] = intval($expire);	
		}
		return true;
	}
	
	/**
	 * 获取一个对象实例，并缓存
	 */
	public static function getInstance($sessionId) {
		if(! $sessionId) return null;
		if(!isset(self::$_bo[$sessionId])) self::$_bo[$sessionId] = new self($sessionId);
		return self::$_bo[$sessionId];
	}
	
	/**
	 * unset一个bo
	 * @param unknown_type $sessionId
	 * @return boolean
	 */
	public static function unsetInstance($sessionId = null){
		if($sessionId == null) self::$_bo = null;
		if(!isset(self::$_bo[$sessionId])) return false;
		self::$_bo[$sessionId] = null;
		return true;
	}	
	
	public function getSession(){
		return $this->sessionInfo;
	}

	private function _getLoginSessionDs(){
		return Wekit::load('EXT:account.service.App_Account_LoginSession');
	}
	
	private function _getLoginSessionService(){
		return Wekit::load('EXT:account.service.srv.App_Account_LoginSessionService');
	}
}