<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_InfoDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');
Wind::import('EXT:account.service.srv.App_Account_BaseService');
Wind::import('EXT:account.service.dm.App_Account_SinaweiboUserInfoDm');


/**
 * App_Account_SinaweiboService - 数据服务接口 weibo相关
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package account
 */
class App_Account_SinaweiboService extends App_Account_BaseService{
	protected $type = 'sinaweibo';
	private $oauthUrl = 'https://api.weibo.com/oauth2/';
	private $getUserUrl = 'https://api.weibo.com/2/users/show.json';
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 第一次请求，获取登录地址,用来获取Authorization Code
	 */
	public function getAuthorizeURL($sessionId){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		if (!$sessionId) return new PwError('登录失败，请重试');
		//state参数用于防止CSRF攻击，成功授权后回调时会原样带回
		$state = md5(uniqid(rand(), TRUE)); 
		$params = array(
			'response_type' => 'code',
			'client_id' => $this->appKey,
			'redirect_uri' => $this->getCallBackUrl('sinaweibo'),
			'state' => $state,
			);	
		$this->_getLoginSessionService()->updateLoginSession($sessionId,array('state' => $state));
		return $this->oauthUrl . 'authorize?' . http_build_query($params);
	}
	
	/**
	 * 获取回调响应
	 */
	public function getResponseInfo(){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		if(!$sessionId || !$sessionInfo) return new PwError('验证会话失败,请重试');
		
		if($_REQUEST['state'] == $sessionInfo['sessiondata']['state']){
			//step 2 通过Authorization Code获取Access Token
			$token = $this->_getAccessToken();
			if($token === false) return new PwError('验证会话失败，请重试');
			list($accessToken,$uid) = $token;
			
			//step 2 获取用户信息
			$userInfo = $this->_getUserInfo($uid, $accessToken);
			if(!$userInfo) return new PwError('获取用户信息失败，请重试');
			
			//更新数据库
			$this->_updateDb($uid, $userInfo);
			
			//更新session
			$this->updateSession($uid, $userInfo['screen_name'], 'sinaweibo');
			return true;
		}
	}
	
	private function _getAccessToken(){
		$code = trim($_REQUEST['code']);
		if (!$code) return false;
		$param = array(
				'grant_type' => 'authorization_code',
				'client_id' => $this->appKey,
				'client_secret' => $this->appSecret,
				'code' => $code,
				'redirect_uri' => $this->getCallBackUrl('sinaweibo'),
		);
		$response = $this->requestByPost($this->oauthUrl . 'access_token?' , http_build_query($param));
		$token = json_decode($response, true);
		
		if ( is_array($token) && !isset($token['error']) ) {
			return array($token['access_token'],$token['uid']);
		} else {
			return false;
		}
		
	}
	
	private function _getUserInfo($uid,$accessToken){
		list($uid,$accessToken) = array(intval($uid),trim($accessToken));
		if(!$uid || !$accessToken) return false;
		
		$param = array(
				'access_token' => $accessToken,
				'uid' => $uid,
		);
	
		$response = $this->request($this->getUserUrl . '?' . http_build_query($param));
		$userInfo = json_decode($response,TRUE);
		return $userInfo;
	}
	
	
	public function logout(){
	
	}
	
	private function _updateDb($userId,$userInfo){
		if(!intval($userId)) return false;
		if(!is_array($userInfo) || count($userInfo) < 1) return false;

		$dm = new App_Account_SinaweiboUserInfoDm();
		$dm->setUserId(intval($userId))
			->setScreenName(trim($userInfo['screen_name']))
			->setName(trim($userInfo['name']))
			->setProvince(intval($userInfo['province']))
			->setCity(intval($userInfo['city']))
			->setLocation(trim($userInfo['location']))
			->setDescription(trim($userInfo['description']))
			->setUrl(trim($userInfo['url']))
			->setProfileImageUrl(trim($userInfo['profile_image_url']))
			->setDomain(trim($userInfo['domain']))
			->setGender(trim($userInfo['gender']))
			->setFollowersCount(trim($userInfo['followers_count']))
			->setFriendsCount(intval($userInfo['friends_count']))
			->setStatusesCount(trim($userInfo['statuses_count']))
			->setFavouritesCount(trim($userInfo['favourites_count']))
			->setCreatedAt(trim($userInfo['created_at']))
			->setVerified($userInfo['verified'])
			->setCreateTime(Pw::getTime());
		$this->_getSinaweiboUserInfoDs()->replace($dm);
		
		return true;
	}
	

	private function _getSinaweiboUserInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_SinaweiboUserInfo');
	}
}
?>