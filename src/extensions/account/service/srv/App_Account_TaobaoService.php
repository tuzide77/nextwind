<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_InfoDm');
Wind::import('EXT:account.service.dm.App_Account_TaobaoUserInfoDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');
Wind::import('EXT:account.service.srv.App_Account_BaseService');


/**
 * App_Account_TaobaoService - 数据服务接口 淘宝相关
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TaobaoService.php 22630 2012-12-26 04:54:55Z xiao.fengx
 * @package account
 */
class App_Account_TaobaoService extends App_Account_BaseService{
	
	protected $type = 'taobao';
	private $oauthUrl = 'https://oauth.taobao.com/authorize';
	public function __construct(){
		parent::__construct();
	}
	
	
	/**
	 * 获取淘宝请求地址
	 */
	public function getAuthorizeURL($sessionId){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		$params = array(
			'response_type' => 'user',
			'client_id' => $this->appKey,
			'redirect_uri' => $this->getCallBackUrl('taobao'),
			);
		
		return $this->oauthUrl . '?' . http_build_query($params);
	}
	
	/**
	 * 获取淘宝响应信息 如果oauth2正常流程，可以走curl_init
	 */
	public function getResponseInfo(){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);

		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		if(!$sessionId || !$sessionInfo) return new PwError('验证会话失败,请重试');
		
		list($top_parameters,$top_sign) = array(trim($_REQUEST['top_parameters']),trim($_REQUEST['top_sign']));
		
		if($this->_checkSign() === false) return new PwError('与淘宝通信失败，请重试');
		
		$userInfo = $this->_getUserInfo();
		if($userInfo === false) return new PwError('获取用户信息失败，请重试');
		list($user_id,$nick) = $userInfo;
		//更新数据库
		$info = $this->_getTaobaoUserInfoDs()->get($user_id);
		if(!$info){
			$dm = new App_Account_TaobaoUserInfoDm();
			$dm->setUserId($user_id)
				->setNick($nick)
				->setCreateAt(Pw::getTime());
			
			$this->_getTaobaoUserInfoDs()->add($dm);
		}
	
		//更新session	
		$this->updateSession($user_id, $nick, 'taobao');
		return true;		
	}
	
	
	/**
	 * 通过top_parameters解析出所需的上下文参数
	 */
	private function _getUserInfo(){
		$top_parameters = trim($_REQUEST['top_parameters']);
		$url = base64_decode($top_parameters);
		parse_str($url,$params);
		$nick = trim($params['nick']);
		$user_id = intval($params['user_id']);
		if(!$nick || !$user_id) return false;
		return array($user_id,$nick);
	}
	
	/**
	 * 验证签名是否合法
	 * @return PwError
	 */
	private function _checkSign(){
		list($top_parameters,$top_sign) = array(trim($_REQUEST['top_parameters']),trim($_REQUEST['top_sign']));
		$sign = base64_encode(md5($top_parameters . $this->appSecret,true));
		if($sign != $top_sign)	return false;
		return true;
	}
	
	/*
	 * 登出 common中方法（钩子）调用
	 * 这个退出流程目前只支持web访问，起到的作用是清除taobao.com的cookie，并不是取消用户的授权。在WAP上访问无效
	 * 
	 * TODO
	 */
	public function logout($host){
		
		return true;
	}
	
	
	
	private function _getTaobaoUserInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_TaobaoUserInfo');
	}
	
}
?>