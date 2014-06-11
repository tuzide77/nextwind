<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.srv.bo.App_Account_InfoBo');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');

/**
 * App_Account_Service - 调用服务
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_CommonService.php 28793 2013-05-24 03:55:18Z jieyin $
 * @package account
 */

class App_Account_CommonService {
	
	private $configKey = 'app_account';
	
	public function __construct(){
		
	}
	
	/**
	 * 获取单个用户的绑定状态
	 * 返回信息
	 *  0           未绑定
	 *  -1         账号通未开启  
	 *  正整数 app_uid 
	 *  空数组 传入参数有问题
	 */
	public function getUserBoundInfo($uid,$type = ''){
		$uid = intval($uid);
		if($uid < 1) return array();
		$result = array();
		if(empty($type)){
			$tmp = array();
			$isClosed = array();//账号通未开启
			$isOpened = array();
			$allType = $this->_getAccountTypeService()->getType();
			$info = $this->_getAccountInfoDs()->fetch($allType);
			foreach($info as $v){
				if(intval($v['status']) == 1) $isOpened[] = $v['type'];
			}
			$isClosed = array_diff($allType,$isOpened);
			
			$bindInfo = $this->_getAccountBindDs()->getByUid($uid);
			foreach($bindInfo as $k => $v){
				if(in_array($v['type'],$isClosed)){
					$result[$v['type']] = -1;
				}else{
					$result[$v['type']] = $v['app_uid'];
				}
				$tmp[] = $v['type'];
			}
			$diff = array_diff($allType,$tmp);		
			foreach($diff as $value){
				if(in_array($value,$isClosed)){
					$result[$value] = -1;
				}else{
					$result[$value] = 0;
				}
			}
			
		}else{
			if(!$this->_getAccountTypeService()->checkType($type)) return array();
			$info = $this->_getAccountInfoDs()->get($type);
			if(!$info['status']) $result = array($type => -1);
			$bindInfo = $this->_getAccountBindDs()->getByUidAndType($uid,$type);
			if($bindInfo){
				$result = array($type => $bindInfo['app_uid']);
			}else{
				$result = array($type => 0);
			}
			
		}
		return $result;
	}
	
	/**
	 * 钩子---用户退出触发
	 */
	public function logout($loginUser){
		return true;
		$uid = intval($loginUser->uid);
		if($uid < 1) return false;
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		if(!$sessionId) return false;
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		$sessionData = $sessionInfo['sessiondata'];
		$type = $sessionData['type'];
		if(!$sessionData || !$this->_getAccountTypeService()->checkType($type)) return false;
		if(!$this->_getAccountBindDs()->getByUidAndType($uid,$type)) return false;
				
		$host = $this->getHost();
		return $this->_getAccountService($type)->logout($host);
		
	}

	/**
	 * 钩子  个人设置--菜单扩展
	 */
	public function getMenus($menus){
		if(!is_array($menus)) return false;
		$info = App_Account_InfoBo::getInstance($this->_getAccountTypeService()->getType())->getAccountInfo();
		$tag = false;
		foreach($info as $v){
			if(intval($v['status']) == 1) {
				$tag = true;
				break;
			}
		}
		if($tag == false) return $menus;
		
		$menus['profile_left'] += array(
				$this->configKey => array('title' => '账号绑定'),
		);
		
		return $menus;
	}	
	
	/**
	 * 获取js地址
	 */
	public function getWindowOpenScriptByType($type,$action,$windowHeight = 520, $windowWidth = 850){
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		$host = $this->getHost();
		return "window.open('{$host}?app=account&m=app&c=index&a={$action}&type={$type}', 'accountLogin', 'height={$windowHeight}, width={$windowWidth}, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no');";
	}
	
	/**
	 * 获取登录js地址
	 */
	public function getLoginAddress(array $types){
		if(!is_array($types) || count($types) < 1) return array();
		$address = array();
		foreach($types as $type){
			$address[$type] = $this->getWindowOpenScriptByType($type,'login');
		}
		return $address;
	}

	/**
	 *获取启用中的账号通的类型 
	 */
	public function getAccountType(){
		$info = App_Account_InfoBo::getInstance($this->_getAccountTypeService()->getType())->getAccountInfo();
		if(!$info) return false;
		$typeFilter = array();
		foreach($info as $v){
			if($v['status']) $typeFilter[] = array('type' => $v['type'],'order' => intval($v['display_order']));
		}
		if(!$typeFilter) return false;
		$typeFilter = $this->array_sort($typeFilter,'order');

		$result = array();
		foreach($typeFilter as $key =>$value){
			$result[] = $value['type'];
		}

		return $result;
	}
	
	/**
	 * s_header_nav - 钩子
	 */
	public function head_login(){
		$types = $this->getAccountType();
		if($types === false) return false;
		$address = $this->getLoginAddress($types);
		
		$info = array();
		foreach($types as $type){
			$info[$type] = array(
					'href' => $address[$type],
					'class' => $this->_getAccountTypeService()->getHrefClassByType($type),
					'name' => $this->_getAccountTypeService()->getTypeName($type),
			);
		}		
		PwHook::template('displayHeadLoginHtml', 'EXT:account.template.head_login', true, $info);
	}
	
	/**
	 *s_login_sidebar - 钩子  
	 */
	public function login_sidebar(){
		$types = $this->getAccountType();
		if($types === false) return false;
		$address = $this->getLoginAddress($types);
		
		$info = array();
		foreach($types as $type){
			$info[$type] = array(
					'href' => $address[$type],
					'class' => $this->_getAccountTypeService()->getHrefClassByType($type),
					'name' => $this->_getAccountTypeService()->getTypeName($type),
					);
 		}		
		PwHook::template('displayLoginSidebarHtml', 'EXT:account.template.login_sidebar', true, $info);
	}
	
	public function array_sort($arr,$keys,$type='asc'){ 
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array; 
	} 
	
	
	/**
	 *获取域名地址 
	 */
	public function getHost(){
		$hostInfo = Wind::getApp()->getRequest()->getHostInfo();
		$scriptUrl = Wind::getApp()->getRequest()->getScriptUrl();
		return $hostInfo . $scriptUrl;
	}
	
	private function _getLoginSessionService(){
		return Wekit::load('EXT:account.service.srv.App_Account_LoginSessionService');
	}
	
	private function _getAccountService($type){
		Wind::import('EXT:account.service.srv.App_Account_Factory');
		return App_Account_Factory::getInstance($type);
	}
	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
	
	private function _getAccountBindDs(){
		return Wekit::load('EXT:account.service.App_Account_Bind');
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
}

?>