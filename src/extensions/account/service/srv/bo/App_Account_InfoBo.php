<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id
 * @package account
 */
class App_Account_InfoBo {
	
	private $info;
	private static $_accountBo = null;

	/** 
	 * 构造函数信息
	 *
	 * @param int $uid 用户ID
	 */
	private function __construct(array $type) {
		$this->info = $this->_getAccountInfoDs()->fetch($type);
	}
	
	/**
	 * 获取一个对象实例，并缓存
	 */
	public static function getInstance(array $type) {
		if(!isset(self::$_accountBo)) self::$_accountBo = new self($type);
		return self::$_accountBo;
	}

	public function getAccountInfo(){
		return $this->info;
	}
	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
}