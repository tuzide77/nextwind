<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_TypeService - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TypeService.php 23980 2013-01-17 12:10:34Z xiao.fengx $
 * @package account
 */
class App_Account_TypeService {
	
	private $_type = array('taobao','qzone','sinaweibo','alipay');

	private $_typeName = array(
			'taobao' => '淘宝',
			'qzone' => 'QQ',
			'sinaweibo' => '新浪微博',
			'alipay'   => '支付宝',
			);
	
	public function checkType($type){
		return in_array($type,$this->_type) ? true : false; 
	}
	
	public function getTypeName($type){
		if($this->checkType($type) == false) return '';
		return $this->_typeName[$type];
	}
	
	public function getIconClassByType($type){
		if(!$this->checkType($type)) return false;
		$icon = array(
				'taobao' => 'icon_taobao',
				'qzone' => 'icon_qq',
				'sinaweibo' => 'icon_weibo',
				'alipay' => 'icon_alipay',
				);
		return $icon[$type];
	}
	
	public function getHrefClassByType($type){
		if(!$this->checkType($type)) return false;
		$class = array(
				'taobao' => 'account_taobao',
				'qzone' => 'account_qq',
				'sinaweibo' => 'account_weibo',
				'alipay' => 'account_alipay',
				);
		return $class[$type];
	}
	
	public function getType(){
		return $this->_type;
	}

}

?>