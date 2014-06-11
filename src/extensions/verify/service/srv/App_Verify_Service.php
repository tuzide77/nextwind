<?php
Wind::import('EXT:verify.service.App_Verify');

/**
 * 实名认证service
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify_Service {
	
	public function getVerifyType() {
		return array(
			App_Verify::VERIFY_REALNAME => '真实姓名',
			App_Verify::VERIFY_AVATAR => '头像',
			App_Verify::VERIFY_EMAIL => '电子邮箱',
			App_Verify::VERIFY_ALIPAY => '支付宝',
			App_Verify::VERIFY_MOBILE => '手机',
		);
	}
	
	public function getVerifyTypeName() {
		return array(
			App_Verify::VERIFY_REALNAME => 'realname',
			App_Verify::VERIFY_AVATAR => 'avatar',
			App_Verify::VERIFY_EMAIL => 'email',
			App_Verify::VERIFY_ALIPAY => 'alipay',
			App_Verify::VERIFY_MOBILE => 'mobile',
		);
	}
	
	public function getVerifyTypeByName($name) {
		$types = array_flip($this->getVerifyTypeName());
		return $types[$name];
	}
	
	public function getRightType() {
		return array(
			App_Verify::RIGHT_MESSAGE => '写私信',
			App_Verify::RIGHT_POSTTOPIC => '发表主题',
			App_Verify::RIGHT_POSTREPLY => '发表回复',
		);
	}
	
	public function getRightTypeName() {
		return array(
			App_Verify::RIGHT_MESSAGE => 'message',
			App_Verify::RIGHT_POSTTOPIC => 'postTopic',
			App_Verify::RIGHT_POSTREPLY => 'postReply',
		);
	}
	
	public function getOpenVerifyType() {
		$openType = Wekit::C('app_verify', 'verify.type');
		if (!$openType) return array();
		$array = array();
		foreach ($this->getVerifyType() as $k => $v) {
			Pw::getstatus($openType, $k) && $array[$k] = $v;
		}
		return $array;
	}
	
	public function getCheckVerifyType() {
		return array(
			App_Verify::VERIFY_REALNAME => '真实姓名',
		);
	}

	/**
	 * 后台审核数据组装
	 *
	 * @param int $ifcheck
	 * @return array
	 */
	public function buildDetail($list) {
		$array = $uids = array();
		foreach ($list as $k => $v) {
			$uids[] = $v['uid'];
			$class = $this->_getFactory($v['type']);
			$v = $class->buildDetail($v);
			$array[$k] = $v;
		}
		$users = Wekit::load('user.PwUser')->fetchUserByUid($uids,PwUser::FETCH_MAIN);
		foreach ($array as $k => $v) {
			if (!isset($users[$v['uid']])) {
				unset($array[$k]);
			}
		}
		return $array;
	}

	/**
	 * 后台审核操作
	 * 
	 * @param int $ifcheck
	 * @param string $type
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function checkVerify($ids){
		if (!is_array($ids) || !$ids) return false;
		$checks = $this->_getCheckDs()->fetchVerifyCheck($ids);
		$typeNames = $this->getVerifyType();
		foreach ($checks as $v) {
			$class = $this->_getFactory($v['type']);
			$res = $class->checkVerify($v);
			if ($res !== true) continue;
			$result = $this->updateVerifyInfo($v['uid'], $v['type']);
			if ($result !== true) continue;
			$extendParams = array(
				'title' => '恭喜！你申请的<a href="' . WindUrlHelper::createUrl('profile/extends/run', array('_left' => 'verify'), '', 'pw') . '" target="_blank">'. $typeNames[$v['type']] .'认证</a>已通过审核。',
				'content' => '恭喜！你申请的<a href="' . WindUrlHelper::createUrl('profile/extends/run', array('_left' => 'verify'), '', 'pw') . '" target="_blank">'. $typeNames[$v['type']] .'认证</a>已通过审核。',
			);
			Wekit::load('message.srv.PwNoticeService')->sendNotice($v['uid'],'app',$v['id'],$extendParams);
		}
		return true;
	}

	/**
	 * 增加审核数据
	 * 
	 * @param $checkDm
	 * @return array
	 */
	public function addCheck(App_Verify_Dm $checkDm) {
		$typeId = $checkDm->getField('type');
		$uid = $checkDm->getField('uid');
		$class = $this->_getFactory($typeId);
		if ($class->unique) {
			$check = $this->_getCheckDs()->getVerifyByUidAndType($uid, $typeId);
			if ($check) {
				return $this->_getCheckDs()->updateVerifyCheck($check['id'], $checkDm);
			}
		}
		return $this->_getCheckDs()->addVerifyCheck($checkDm);
	}

	/**
	 * 检测用户权限
	 * 
	 * @param $checkDm
	 * @return array
	 */
	public function checkVerifyRights($uid, $operator) {
		$rights = Wekit::C('app_verify', 'verify.rights');
		$verify = $this->_getDs()->getVerify($uid);
		$types = $this->getOpenVerifyType();
		$rightType = $this->getRightType();
		$right = $rights[$operator];
		if (!$right) return true;
		foreach ($types as $tk => $tv) {
			if (!Pw::getstatus($right, $tk)) continue;
			if (!Pw::getstatus($verify['type'], $tk)) {
				return new PwError('必须通过"'.$tv.'认证"才能'.$rightType[$operator]);
			}
		}
		return true;
	}
	
	public function updateVerifyInfo($uid, $type, $bool = true) {
		$result = $this->checkVerifyOpen($type);
		if (!$result) return new PwError('未开启该类型实名认证');
		Wind::import('EXT:verify.service.dm.App_Verify_Dm');
		$dm = new App_Verify_Dm($uid);
		$info = $this->_getDs()->getVerify($uid);
		if (!$info) {
			$dm->setUid($uid)
			->setType($this->buildVerifyBit(array($type => $bool)));
			$result = $this->_getDs()->addVerify($dm);
			if ($result instanceof PwError) return $result;
		}
		$dm->setBitType($type, $bool);
		$result = $this->_getDs()->updateVerify($uid, $dm);
		if ($result instanceof PwError) return $result;
		return true;
	}
	
	public function checkVerifyOpen($type) {
		if (!Wekit::C('app_verify', 'verify.isopen')) return false;
		$openType = Wekit::C('app_verify', 'verify.type');
		if (!Pw::getstatus($openType, $type)) return false;
		return true;
	}
	
	public function buildVerifyBit($array) {
		if (!is_array($array)) return '';
		$str = '';
		foreach ($array as $bit => $v) {
			$str += $v ? (1 << $bit - 1) : 0;
		}
		return $str;
	}
	
	/**
	 * 创建找回密码的唯一标识
	 *
	 * @param string $uid 需要找回密码的用户名
	 * @param string $way 找回方式标识
	 * @param string $value 找回方式对应的值
	 * @return string
	 */
	public static function createIdentify($uid, $type, $passwd) {
		$code = Pw::encrypt($uid . '|' . $type . '|' . $passwd, Wekit::C('site', 'hash') . '___verify');
		return rawurlencode($code);
	}
	
	/**
	 * 解析找回密码的标识
	 *
	 * @param string $identify
	 * @return array array($username, $way, $value)
	 */
	public static function parserIdentify($identify) {
		return explode("|", Pw::decrypt(rawurldecode($identify), Wekit::C('site', 'hash') . '___verify'));
	}
	
	protected function _getFactory($typeId){
		if (!$typeId) return null;
		$types = $this->getVerifyTypeName();
		$type = $types[$typeId];
		if (!$type) return null;
		$type = strtolower($type);
		$className = sprintf('App_Verify_%s', ucfirst($type));
		if (class_exists($className,false)) {
			return new $className();
		}
		$fliePath = 'EXT:verify.service.srv.action.'.$className;
		Wind::import($fliePath);
		return new $className();
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
}
