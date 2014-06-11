<?php
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('EXT:verify.service.App_Verify');

/**
 * 本地搜索后台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class ManageController extends AdminBaseController {
	protected $perpage = 20;

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$restMessage = Wekit::load('SRV:mobile.srv.PwMobileService')->getRestMobileMessage();
		if ($restMessage instanceof PwError) {
			$restMessage = 0;
		}
		$filePath = Wind::getRealPath('ADMIN:conf.openplatformurl.php', true);
		$openPlatformUrl = Wind::getComponent('configParser')->parse($filePath);
		$appMobileUrl = $openPlatformUrl.'index.php?m=appcenter&c=SmsManage';
		$this->setOutput($restMessage, 'restMessage');
		$this->setOutput($appMobileUrl, 'appMobileUrl');
		$this->_setOutConfig();
	}

	/**
	 * 保存认证设置
	 *
	 */
	public function doRunAction() {
		$verifyType = $this->getInput('verifyType', 'post');
		$verifyTypeBit = $this->_getService()->buildVerifyBit($verifyType);
		$config = new PwConfigSet('app_verify');
		$config->set('verify.isopen', $this->getInput('verifyOpen', 'post'))
			->set('verify.type', $verifyTypeBit)
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证邮箱设置
	 *
	 */
	public function emailAction() {
		$this->_setOutConfig();
	}

	/**
	 * 保存认证邮箱设置
	 *
	 */
	public function doEmailAction() {
		$config = new PwConfigSet('app_verify');
		$config->set('verify.email.title', $this->getInput('emailTitle', 'post'))
			->set('verify.email.content', $this->getInput('emailContent', 'post'))
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证权限设置
	 *
	 */
	public function rightsAction() {
		$this->_setOutConfig();
		$openTypes = $this->_getService()->getOpenVerifyType();
		$rightType = $this->_getService()->getRightType();
		$this->setOutput($rightType, 'rightType');
		$this->setOutput($openTypes, 'openTypes');
	}

	/**
	 * 保存认证权限设置
	 *
	 */
	public function doRightsAction() {
		$config = new PwConfigSet('app_verify');
		$rights = $this->getInput('rights', 'post');
		$array = array();
		foreach ($rights as $key => $value) {
			$array[$key] = $this->_getService()->buildVerifyBit($value);
		}
		
		$config->set('verify.rights', $array)
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证会员管理
	 *
	 */
	public function usersAction() {
		list($page, $perpage, $username, $type) = $this->getInput(array('page', 'perpage', 'username', 'type'));
		$page = $page ? $page : 1;
		$perpage = $perpage ? $perpage : $this->perpage;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		if ($username) {
			Wind::import('SRV:user.vo.PwUserSo');
			$vo = new PwUserSo();
			$vo->setUsername($username);
			$searchDs = Wekit::load('SRV:user.PwUserSearch');
			$userInfos = $searchDs->searchUser($vo, $perpage);
		}
		Wind::import('EXT:verify.service.vo.App_Verify_So');
		$so = new App_Verify_So();
		$userInfos && $so->setUid(array_keys($userInfos));
		if ($type) {
			$bitType = 1 << $type - 1;
			$so->setType($bitType);
		}
		$count = $this->_getDs()->countSearchVerify($so);
		if ($count) {
			$list = $this->_getDs()->searchVerify($so, $limit, $start);
			$list = $this->_buildData($list);
		}
		
		$verifyTypes = $this->_getService()->getVerifyType();
		$this->setOutput($verifyTypes, 'verifyTypes');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($list, 'list');
		$this->setOutput(array('username' => $username, 'type' => $type), 'args');
		
	}

	/**
	 * 认证会员管理
	 *
	 */
	public function usersetAction() {
		$uid = $this->getInput('uid', 'get');
		$info = $this->_getDs()->getVerify($uid);
		$data = $this->_buildData(array($info['uid'] => $info));

		$this->setOutput($data[$info['uid']], 'info');
	}

	/**
	 * 取消认证
	 *
	 */
	public function deleteVerifyAction() {
		list($uid, $type) = $this->getInput(array('uid', 'type'), 'get');
		$this->_getService()->updateVerifyInfo($uid, $type, false);
		$this->showMessage('success');
	}

	/**
	 * 认证审核
	 *
	 */
	public function checkAction() {
		list($page, $perpage, $type) = $this->getInput(array('page', 'perpage', 'type'));
		$page = $page ? $page : 1;
		$perpage = $perpage ? $perpage : $this->perpage;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		$list = array();
		$count = $this->_getCheckDs()->countVerifyCheckByType($type);
		if ($count) {
			$list = $this->_getCheckDs()->getVerifyCheckByType($type, $limit, $start);
			$list = $this->_getService()->buildDetail($list);
		}
		
		$verifyTypes = $this->_getService()->getCheckVerifyType();
		$this->setOutput($verifyTypes, 'verifyTypes');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($list, 'list');
		$this->setOutput(array('type' => $type), 'args');
	}

	/**
	 * do认证审核操作
	 *
	 */
	public function doCheckAction() {
		list($ids, $action) = $this->getInput(array('ids', 'action'));
		!is_array($ids) && $ids = array($ids);
		if ($action == 'pass') {
			$this->_getService()->checkVerify($ids);
		} else {
			list($sendnotice, $reason) = $this->getInput(array('sendnotice', 'reason'));
			$this->_sendNotice($ids, $sendnotice, $reason);
		}
		$this->_getCheckDs()->batchDeleteVerifyCheck($ids);
		$this->showMessage('success');
	}
	
	private function _sendNotice($ids, $sendnotice, $reason) {
		if (!$sendnotice) return false;
		$checks = $this->_getCheckDs()->fetchVerifyCheck($ids);
		if (!$checks) return false;
		$typeNames = $this->_getService()->getVerifyType();
		foreach ($checks as $v) {
			$extendParams = array(
				'title' => '你申请的<a href="' . WindUrlHelper::createUrl('profile/extends/run', array('_left' => 'verify'), '', 'pw') . '" target="_blank">'. $typeNames[$v['type']] .'认证</a>未通过管理员审核。',
				'content' => '你申请的<a href="' . WindUrlHelper::createUrl('profile/extends/run', array('_left' => 'verify'), '', 'pw') . '" target="_blank">'. $typeNames[$v['type']] .'认证</a>未通过管理员审核。<br >拒绝理由：'.$reason,
			);
			Wekit::load('message.srv.PwNoticeService')->sendNotice($v['uid'],'app',$v['id'],$extendParams);
		}
	}
	
	private function _setOutConfig() {
		$conf = Wekit::C('app_verify');
		$this->setOutput($conf, 'conf');
	}

	private function _buildData($data) {
		$users = $this->_getUserDs()->fetchUserByUid(array_keys($data),PwUser::FETCH_MAIN + PwUser::FETCH_INFO);
		$list = array();
		foreach ($users as $k => $v) {
			if (!$data[$k]['type']) continue;
			$_tmp['uid'] = $v['uid'];
			$_tmp['username'] = $v['username'];
			$_tmp['realname'] = Pw::getstatus($data[$k]['type'], App_Verify::VERIFY_REALNAME) ? $v['realname'] : '';
			$_tmp['email'] = Pw::getstatus($data[$k]['type'], App_Verify::VERIFY_EMAIL) ? $v['email'] : '';
			$_tmp['alipay'] = Pw::getstatus($data[$k]['type'], App_Verify::VERIFY_ALIPAY) ? $v['alipay'] : '';
			$_tmp['mobile'] = Pw::getstatus($data[$k]['type'], App_Verify::VERIFY_MOBILE) ? $v['mobile'] : '';
			$_tmp['avatar'] = Pw::getstatus($data[$k]['type'], App_Verify::VERIFY_AVATAR) ? 1 : 0;
			$_tmp['passVerify'] = $this->_getPassVerify($data[$k]['type']) ;
			$list[$k] = $_tmp;
		}
		return $list;
	}
	
	private function _getPassVerify($type) {
		$types = $this->_getService()->getVerifyType();
		$array = array();
		foreach ($types as $k => $v) {
			Pw::getstatus($type, $k) && $array[] = $v;
		}
		
		return implode('、', $array);
	}
	
	/**
	 * @return PwUser
	 */
	protected function _getUserDs() {
		return Wekit::load('user.PwUser');
	}
	
	/**
	 * @return App_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.App_Verify');
	}
	
	/**
	 * @return App_Verify_Check
	 */
	protected function _getCheckDs() {
		return Wekit::load('EXT:verify.service.App_Verify_Check');
	}
	
	/**
	 * @return App_Verify_Service
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.App_Verify_Service');
	}
}

?>