<?php
Wind::import('EXT:verify.service.App_Verify');
Wind::import('EXT:verify.service.dm.App_Verify_Dm');
Wind::import('LIB:utility.PwMail');
Wind::import('SRV:user.dm.PwUserInfoDm');

/**
 * 实名认证前台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class IndexController extends PwBaseController {
	
	const VERIFY_EMAIL = 3;//邮件认证
	protected $conf = array();
	private $loginConfig = array();
	private $ipLimit = 100;
	private $trySpace = 1800;
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		if (!$this->loginUser->isExists()) {
			$this->forwardRedirect(WindUrlHelper::createUrl('u/login/run', array('_type' => $this->getInput('_type'))));
		}
		$this->conf = Wekit::C('app_verify');
		if (!$this->conf['verify.isopen']) {
			$this->forwardRedirect(WindUrlHelper::createUrl('bbs/index/run'));
		}
	}
	
	public function run() {
		$this->forwardRedirect(WindUrlHelper::createUrl('profile/extends/run', array('_left' => 'verify')));
	}
	
	public function realnameAction() {
		$statu = $this->_checkState();
		$info = $this->_getCheckDs()->getVerifyByUidAndType($this->loginUser->uid, App_Verify::VERIFY_REALNAME);
		$info && $ischeck = 1;
		$types = $this->_getService()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], App_Verify::VERIFY_REALNAME)) && $isVerify = 1;
		$info = $this->loginUser->info;
		
		$this->setOutput(array('title' => $types[App_Verify::VERIFY_REALNAME], 'ischeck' => $ischeck, 'isVerify' => $isVerify, 'statu' => $statu, 'realname' => $info['realname']), 'data');
		$this->setOutput('realname', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function passwdAction() {
		$type = $this->getInput('type', 'get');
		$this->setOutput(array('type' => $type), 'data');

		$this->setOutput('password', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function checkPasswdAction() {
		list($type, $passwd) = $this->getInput(array('type', 'passwd'), 'post');
		Wind::import('EXT:verify.service.srv.App_Verify_Service');
		if (($result = $this->checkPasswd($passwd)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$statu = App_Verify_Service::createIdentify($this->loginUser->uid, $type, $passwd);
		$this->setOutput('password', 'type_segment');
		
		$this->setOutput(array('statu' => $statu), 'data');
		$this->showMessage('success',"app/verify/index/$type");
	}
	
	public function avatarAction() {
		$types = $this->_getService()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		Pw::getstatus($verify['type'], App_Verify::VERIFY_AVATAR) && $isVerify = 1;

		$this->setOutput(array('title' => $types[App_Verify::VERIFY_AVATAR], 'isVerify' => $isVerify), 'data');
		$this->setOutput('avatar', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function emailAction() {
		$statu = $this->_checkState();
		$types = $this->_getService()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], App_Verify::VERIFY_EMAIL)) && $isVerify = 1;
		$info = $this->loginUser->info;
		$this->setOutput(array('email' => $info['email'], 'isVerify' => $isVerify, 'statu' => $statu), 'data');
		$this->setOutput('email', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function mobileAction() {
		$statu = $this->getInput('statu');
		$types = $this->_getService()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], App_Verify::VERIFY_MOBILE)) && $isVerify = 1;
		$info = $this->_getUser()->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		
		$this->setOutput(array('mobile' => $info['mobile'], 'isVerify' => $isVerify), 'data');
		$this->setOutput('mobile', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function alipayAction() {
		$statu = $this->_checkState();
		$types = $this->_getService()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], App_Verify::VERIFY_ALIPAY)) && $isVerify = 1;
		$info = $this->_getUser()->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		
		$this->setOutput(array('alipay' => $info['alipay'], 'isVerify' => $isVerify), 'data');
		$this->setOutput('alipay', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	/**
	 * 邮箱认证 - 发送认证邮件
	 */
	public function doEmailAction() {
		$statu = $this->_checkState();
		$email = $this->getInput('email');
		if (!$email) $this->showError('请输入邮箱');
		$info = $this->_getUser()->getUserByEmail($email);
		if ($info && $info['uid'] != $this->loginUser->uid) $this->showError('该邮箱已存在');

		if (($result = $this->sendVerifyEmail($email)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->setOutput(array('email' => $email), 'data');
		$this->showMessage('success');
	}

	/**
	 * 确认邮箱认证
	 */
	public function verifyEmailAction() {
		$code = $this->getInput('code');
		$activeCodeDs = Wekit::load('user.PwUserActiveCode');
		$data = $activeCodeDs->getInfoByUid($this->loginUser->uid, self::VERIFY_EMAIL);
		if ($code != $data['code']) {
			$this->showError('该邮箱认证链接已过期，请重新认证');
		}
		$info = $this->_getUser()->getUserByEmail($data['email']);
		if ($info && $info['uid'] != $this->loginUser->uid) {
			$this->showError('该邮箱已存在');
		}
		if ($data['email'] != $this->loginUser->info['email']) {
			$dm = new PwUserInfoDm($this->loginUser->uid);
			$dm->setEmail($data['email']);
			$this->_getUser()->editUser($dm, PwUser::FETCH_MAIN);
		}
		$result = $this->_getService()->updateVerifyInfo($this->loginUser->uid, App_Verify::VERIFY_EMAIL);
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		//认证完成删除
		$activeCodeDs->deleteInfoByUid($this->loginUser->uid);
		$this->forwardRedirect(WindUrlHelper::createUrl('profile/extends/run?_left=verify'));
	}
	
	public function verifyRealnameAction() {
		$statu = $this->_checkState();
		$realname = trim($this->getInput('realname'));
		if (!$realname) $this->showError('请输入真实姓名');
		if (Pw::strlen($realname) > 50) $this->showError('真实姓名长度不能超过50个字');
		$dm = new App_Verify_Dm();
		$dm->setUid($this->loginUser->uid)
			->setUsername($this->loginUser->username)
			->setType(App_Verify::VERIFY_REALNAME)
			->setData(serialize(array('realname' => $realname)));
		$this->_getService()->addCheck($dm);
		$this->showMessage('success');
	}
	
	public function loginAlipayAction() {
		$alipay = $this->getInput('alipay');
		Wind::import('APPCENTER:service.srv.helper.PwApplicationHelper');
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'alipayAuth', 'callback' => WindUrlHelper::createUrl('app/verify/index/verifyAlipay', array('uid' => $this->loginUser->uid)), 'account' => $alipay));
		$info = PwApplicationHelper::requestAcloudData($url);
	    if ($info['code'] !== '0') $this->showError($info['msg']);
		$this->setOutput(array('referer' => $info['info']), 'data');
		$this->showMessage('success');
	}
	
	public function verifyAlipayAction() {
		list($uid, $alipay) = $this->getInput(array('uid', 'email'));
		$info = $this->loginUser->info;
		if ($uid != $this->loginUser->uid) {
			$this->forwardRedirect(WindUrlHelper::createUrl('bbs/index/run'));
		}
		$result = $this->_getService()->updateVerifyInfo($this->loginUser->uid, App_Verify::VERIFY_ALIPAY);
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		$dm = new PwUserInfoDm($this->loginUser->uid);
		$dm->setAlipay($alipay);
		$this->_getUser()->editUser($dm, PwUser::FETCH_INFO);
		$this->forwardRedirect(WindUrlHelper::createUrl('profile/extends/run?_left=verify'));
	}
	
	public function verifyMobileAction() {
		list($mobileCode, $mobile) = $this->getInput(array('mobileCode', 'mobile'), 'post');
		if (!$mobile || !$mobileCode) $this->showError('USER:mobile.code.mobile.empty');
		if (($result = $this->_checkMobileRight($mobile, $this->loginUser->uid)) instanceof PwError) {
			$this->showError($result->getError());
		}
		if (($result = Wekit::load('SRV:mobile.srv.PwMobileService')->checkVerify($mobile, $mobileCode)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$result = $this->_getService()->updateVerifyInfo($this->loginUser->uid, App_Verify::VERIFY_MOBILE);
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		// 插入用户手机数据
		Wekit::load('user.PwUserMobile')->replaceMobile($this->loginUser->uid,$mobile);
		// 更新用户信息表
		$dm = new PwUserInfoDm($this->loginUser->uid);
		$dm->setMobile($mobile);
		$this->_getUser()->editUser($dm, PwUser::FETCH_INFO);
		$this->showMessage('success');
	}
	
	/**
	 * 发送手机验证码
	 */
	public function sendmobileAction() {
		$mobile = $this->getInput('mobile', 'post');
		if (($result = $this->_checkMobileRight($mobile, $this->loginUser->uid)) instanceof PwError) {
			$this->showError($result->getError());
		}
		if (($result = Wekit::load('SRV:mobile.srv.PwMobileService')->sendMobileMessage($mobile)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage('success');
	}
	
	/**
	 * 验证手机号码
	 */
	public function checkmobileAction() {
		$mobile = $this->getInput('mobile', 'post');
		if (($result = $this->_checkMobileRight($mobile, $this->loginUser->uid)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$result = Wekit::load('SRV:mobile.srv.PwMobileService')->checkTodayNum($mobile);
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage();
	}
	
	public function typeTabAction() {
		$type = $this->getInput('type');
		$userInfo = Wekit::load('user.PwUser')->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		$userInfo = array_merge($this->loginUser->info, $userInfo);
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		$typeId = $this->_getService()->getVerifyTypeByName($type);
		(Pw::getstatus($verify['type'], $typeId)) && $isVerify = 1;
		if (App_Verify::VERIFY_REALNAME == $typeId) {
			$info = $this->_getCheckDs()->getVerifyByUidAndType($this->loginUser->uid, App_Verify::VERIFY_REALNAME);
			$realname = $info ? '审核中' : $this->loginUser->info['realname'];
			$this->setOutput($realname, 'realname');
		}
		$this->setOutput($userInfo, 'userinfo');
		$this->setOutput($isVerify, 'isVerify');
		$this->setTemplate('verify_segment_'.$type);
	}
	
	private function _checkMobileRight($mobile, $uid) {
		Wind::import('SRV:user.validator.PwUserValidator');
		if (!PwUserValidator::isMobileValid($mobile)) {
			return new PwError('USER:error.mobile');
		}
		$mobileInfo = Wekit::load('user.PwUserMobile')->getByMobile($mobile);
		if ($mobileInfo && $mobileInfo['uid'] != $uid) $this->showError('USER:mobile.mobile.exist');
		return true;
	}
	
	protected function checkPasswd($oldPwd) {
		if (!$oldPwd) {
			$this->showError('USER:pwd.change.oldpwd.require');
		}
		Wind::import('SRV:user.srv.PwTryPwdBp');
		$tryPwdBp = new PwTryPwdBp();
		$ip = $this->getRequest()->getClientIp();
		if (($result = $tryPwdBp->checkPassword($this->loginUser->uid, $oldPwd, $ip)) instanceof PwError) {
			list($error, $data) = $result->getError();
			switch ($error) {
				case 'USER:login.error.tryover.pwd':
					return new PwError("已经连续".$data['{totalTry}']."次密码错误,您将在".$data['{min}']."分钟后再尝试");
				case 'USER:login.error.pwd':
					return new PwError("密码错误,您还可以尝试".$data['{num}']."次");
			}
		}
		return $result;
	}
	
	protected function sendVerifyEmail($email) {
		$userBo = Wekit::getLoginUser();
		$user = $userBo->info;
		Wind::import('SRV:user.srv.PwRegisterService');
		
		$code = substr(md5(Pw::getTime()), mt_rand(1, 8), 8);
		$url = WindUrlHelper::createUrl('app/verify/index/verifyEmail', array('code' => $code));
		list($title, $content) = $this->_buildTitleAndContent('verify.email.title', 'verify.email.content', $user['username'], $url);

		$activeCodeDs = Wekit::load('user.PwUserActiveCode');
		$activeCodeDs->addActiveCode($user['uid'], $email, $code, Pw::getTime(), self::VERIFY_EMAIL);
	
		$mail = new PwMail();
		$mail->sendMail($email, $title, $content);
		return true;
	}
	
	protected function _buildTitleAndContent($titleKey, $contentKey, $username, $url = '') {
		$search = array('{username}', '{sitename}');
		$replace = array($username, Wekit::C('site', 'info.name'));
		$title = str_replace($search, $replace, $this->conf[$titleKey]);
		$search[] = '{time}';
		$search[] = '{url}';
		$replace[] = Pw::time2str(Pw::getTime(), 'Y-m-d H:i:s');
		$replace[] = $url ? sprintf('<a href="%s">%s</a>', $url, $url) : '';
		$content = str_replace($search, $replace, $this->conf[$contentKey]);
		return array($title, $content);
	}
	
	/**
	 * 检查是否符合要求
	 */
	private function _checkState() {
		$statu = $this->getInput('statu');
		if (!$statu) return false;
		Wind::import('EXT:verify.service.srv.App_Verify_Service');
		list($uid, $type, $passwd) = App_Verify_Service::parserIdentify($statu);
		if (($result = $this->checkPasswd($passwd)) instanceof PwError) {
			$this->showError($result->getError());
		}
		return $statu;
	}
	
	protected function checkVerifyExist($uid, $type) {
		$info = $this->_getDs()->getVerify($uid);
		if (Pw::getstatus($info['type'], $type)) {
			return true;
		}
		return false;
	}
	
	/**
	 * @return App_Verify_Service
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.App_Verify_Service');
	}
	
	/** 
	 * 获得PwUser
	 *
	 * @return PwUser
	 */
	protected function _getUser() {
		return Wekit::load('user.PwUser');
	}
	
	/** 
	 * 获得windidDS
	 *
	 * @return WindidUser
	 */
	protected function _getWindid() {
		return WindidApi::api('user');
	}
	
	/**
	 * @return App_Verify_Check
	 */
	protected function _getCheckDs() {
		return Wekit::load('EXT:verify.service.App_Verify_Check');
	}
	
	/**
	 * @return App_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.App_Verify');
	}
}

?>