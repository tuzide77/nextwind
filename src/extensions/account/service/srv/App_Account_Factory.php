<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 工厂类
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id
 * @package account
 */

class App_Account_Factory{
	
	private static $type = array('taobao','qzone','sinaweibo','alipay');
	
	public static function getInstance($type){
		if(!in_array($type,self::$type)) return null;
		$className = 'App_Account_' . ucfirst(strtolower($type)) . 'Service';
		return Wekit::load('EXT:account.service.srv.' . $className);
	}
	
}