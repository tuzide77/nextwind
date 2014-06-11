<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_InfoDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');
Wind::import('EXT:account.service.srv.App_Account_BaseService');
Wind::import('EXT:account.service.dm.App_Account_QzoneUserInfoDm');


/**
 * App_Account_QzoneService - 数据服务接口 QQ相关
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package account
 */
class App_Account_QzoneService extends App_Account_BaseService{
	protected $type = 'qzone';
	private $oauthUrl = 'https://graph.qq.com/oauth2.0/';
	private $getInfoUrl = 'https://graph.qq.com/user/get_user_info';
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 第一次请求，获取qq登录地址,用来获取Authorization Code
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
				'redirect_uri' => $this->getCallBackUrl('qzone'),
				'state' => $state,
				);		
		$this->_getLoginSessionService()->updateLoginSession($sessionId,array('state' => $state));		
		return $this->oauthUrl . 'authorize?' . http_build_query($params);
	}
	
	/**
	 * 获取QQ回调响应
	 */
	public function getResponseInfo(){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		$params = array_merge($_GET, $_POST);
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		if(!$sessionId || !$sessionInfo) return new PwError('验证会话失败,请重试');
		
		
		if($params['state'] == $sessionInfo['sessiondata']['state']){
			//step 2 通过Authorization Code获取Access Token
			$accessToken = $this->_getAccessToken();
			if($accessToken === false) return new PwError('验证会话失败,请重试');
			
			/*
			 * step3：使用Access Token来获取用户的OpenID
			 * openid 用户唯一标识
			 */
			$openId = $this->_getOpenIdByAccessToken($accessToken);
			if($openId === false) return new PwError('验证会话失败,请重试');
			
			//step4: 使用openid获取用户信息
			$userInfo = $this->_getUserInfo($openId, $accessToken);
			if($userInfo === false || !$userInfo) return new PwError('获取用户信息失败，请重试');
			
			//更新数据库
			$userId = $this->_updateDb($openId, $userInfo);
			if($userId === false || $userId < 1) return new PwError('更新数据库失败');
			
			//更新session
			$this->updateSession($userId, $userInfo['nickname'], 'qzone');
			
			return true;
			
			
		}

	}
	
	public function logout(){
		
	}
	
	/**
	 * 升级账号通的数据
	 */
	public function upgrade(){
		Wind::import("WIND:db.WindConnection");
		$configFile = include Wind::getRealPath($this->upgradeConfig);
		$dsn = 'mysql:dbname='.$configFile['old_db_name'].';host=' . $configFile['old_db_host'] . ';port=' . $configFile['old_db_port'];
		$siteId = $configFile['siteId'];
		$siteHash = $configFile['siteHash'];
		$appsUrl = 'http://apps.phpwind.com/upgradeweiboapi.php';
		
		//分页
		$limit = $configFile['limit'];//每次升级200个
		$page = $_GET['page'];
		$page = $page < 1 ? 1 : intval($page);
		list($offset, $limit) = Pw::page2limit($page, $limit);
		$sql = "SELECT * FROM pw_weibo_bind WHERE weibotype = '$this->type' LIMIT ". max(0, intval($offset)) . " , " . max(1, intval($limit));
		try {
			$pdo = new WindConnection($dsn, $configFile['old_db_user'], $configFile['old_db_pass']);
			$result = $pdo->query($sql)->fetchAll();
			
		} catch (PDOException $e) {
			$error = $e->getMessage();
			return new PwError($error);
		}
				
		if(empty($result)){
			return true;
		}
		
		$bbsUids = array();
		$bind = array();
		foreach($result as $key => $value){
			$bbs_uid = intval($value['uid']);
			if($bbs_uid){
				$bbsUids[] = intval($bbs_uid);
			}
		}
		
		
		$uids = implode($bbsUids,',');
		$param = array('uids'=>$uids,'site_id'=>$siteId,'type'=>$this->type);
		ksort($param);
		$checkSign = md5(http_build_query($param).$siteHash);
		$url = $appsUrl . '?uids='.$uids.'&siteid='.$siteId.'&type='.$this->type.'&sign='. $checkSign;
		$response = $this->request($url);
		unset($param,$checkSign,$url);
		$info = json_decode($response,TRUE);
		if($info[0] === false) return new PwError('接口通信失败');
		$info = $info[1];
			
		$dm = new App_Account_QzoneUserInfoDm();
		foreach($bbsUids as $key => $value){
			if($info[$value]){
				$qqInfo = $this->_getQzoneUserInfoDs()->getByOpenId($info[$value]);
				if($qqInfo){
					$user_id = $qqInfo['user_id'];
				}else{
					$dm->setOpenId($info[$value])
						->setCreateAt(Pw::getTime());
					$user_id = $this->_getQzoneUserInfoDs()->add($dm);
				}
					
				$bind[] = array('uid'=>$value,'type'=>$this->type,'app_uid'=>$user_id);
			}
	
		}
			
		$this->_getAccountBindDs()->batchAdd($bind);
		unset($bind,$bbsUids);
		return $page + 1;
	}
	
	private function _updateDb($openId,$userInfo){
		if(!trim($openId)) return false;
		if(!is_array($userInfo) || count($userInfo) < 1) return false;
		
		$info = $this->_getQzoneUserInfoDs()->getByOpenId($openId);
		if($info){
			//更新
			$dm = new App_Account_QzoneUserInfoDm($openId);
			$dm->setOpenId($openId)
				->setNickName(trim($userInfo['nickname']))
				->setAvatar(trim($userInfo['avatar']))
				->setAvatarMid(trim($userInfo['avatar_mid']))
				->setAvatarBig(trim($userInfo['avatar_big']))
				->setGender(trim($userInfo['gender']))
				->setCreateAt(Pw::getTime());
			$this->_getQzoneUserInfoDs()->updateByOpenId($dm);
			$user_id = $info['user_id'];
		}else{
			//添加
			$dm = new App_Account_QzoneUserInfoDm();
			$dm->setOpenId($openId)
				->setNickName(trim($userInfo['nickname']))
				->setAvatar(trim($userInfo['avatar']))
				->setAvatarMid(trim($userInfo['avatar_mid']))
				->setAvatarBig(trim($userInfo['avatar_big']))
				->setGender(trim($userInfo['gender']))
				->setCreateAt(Pw::getTime());
			$user_id = $this->_getQzoneUserInfoDs()->add($dm);
		}
		return $user_id;
	}
	
	private function _getUserInfo($openId,$accessToken){
		list($openId,$accessToken) = array(trim($openId),trim($accessToken));
		if(!$openId || !$accessToken) return false;
		$param = array(
				'access_token' => $accessToken,
				'oauth_consumer_key' => $this->appKey,
				'openid' => $openId,
		);
		
		$response = $this->request($this->getInfoUrl . '?' . http_build_query($param));
		$userInfo = json_decode($response,TRUE);
		if($userInfo['ret'] != 0 || !$userInfo) return false;
		$gender = trim($userInfo['gender']) ?  trim($userInfo['gender']) : '男';
		
		return array(
				'nickname' => trim($userInfo['nickname']),
				'avatar' => trim($userInfo['figureurl']),
				'avatar_mid' => trim($userInfo['figureurl_1']),
				'avatar_big' => trim($userInfo['figureurl_2']),
				'gender' => $gender,
				);
	}
		
	private function _getOpenIdByAccessToken($accessToken){
		$accessToken = trim($accessToken);
		if (!$accessToken) return false;
		$response = $this->request($this->oauthUrl . 'me?access_token='.$accessToken);
		if(strpos($response, "callback") !== false){
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response = substr($response, $lpos + 1, $rpos - $lpos -1);
		}
		$user = json_decode($response);
		if(isset($user->error)){
			return false;
		}
		return $user->openid;
	}
	
	private function _getAccessToken(){
		$code = trim($_REQUEST['code']);
		if (!$code) return false;
		$param = array(
				'grant_type' => 'authorization_code',
				'client_id' => $this->appKey,
				'client_secret' => $this->appSecret,
				'code' => $code,
				'redirect_uri' => $this->getCallBackUrl('qzone'),
		);
		$response = $this->request($this->oauthUrl . 'token?' . http_build_query($param));
		if (strpos($response, "callback") != false){
			return false;
		}
		parse_str($response, $params);
		return $params['access_token'];
	}
	
	
	private function _getQzoneUserInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_QzoneUserInfo');
	}
	
}
?>