<?php

/**
 * 马甲绑定的BP
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_MajiaBandingBp.php 24332 2013-01-28 11:28:58Z xiaoxia.xuxx $
 * @package majia.service.srv
 */
class App_Majia_MajiaBandingBp {
	/**
	 * 当前登录
	 *
	 * @var PwUserBo 
	 */
	private $loginBo = null;
	private $bandedUser = array();
	
	/**
	 * 构造函数
	 *
	 * @param PwUserBo $bo
	 * @param int $uid
	 */
	public function __construct(PwUserBo $bo) {
		$this->loginBo = $bo;
	}
	
	/**
	 * 马甲绑定相关检查
	 * 
	 * @return PwError|true
	 */
	public function check($username, $password) {
		if (true !== ($result = $this->_check())) return $result;
		if (!$username || !$password) return new PwError('被绑定的用户帐号和密码不能为空');
		if ($username == $this->loginBo->username) return new PwError('不可以和自己绑定');
		/* @var $userSrv PwUserService */
		$userSrv = Wekit::load("SRV:user.srv.PwUserService");
		$info = $userSrv->verifyUser($username, $password, 2);
		if ($info instanceof PwError) return $info;
		$bandedUser = $this->_loadUser()->getUserByUid($info['uid']);
		if (!$bandedUser) return new PwError(sprintf('用户%s没有同步到系统中，请联系管理原同步或实现用该帐号登录系统进行同步', $username));
		//个数判断
		if (($num = intval(Wekit::C('app_majia', 'band.max.num'))) > 0) {
			$num_A = count($this->_loadMajia()->getByUid($this->loginBo->uid));
			$num_B = count($this->_loadMajia()->getByUid($info['uid']));
			if ((($num_A + $num_B - 1) >= $num) || (!$num_A && $num_B && ($num_B >= $num))) {
				return new PwError('马甲绑定不能超过' . $num . '个');
			}
		} else {
			return new PwError('马甲绑定不能超过0个');
		}
		$this->bandedUser = $bandedUser;
		return true;
	}
	
	/**
	 * 执行绑定
	 * 
	 * @param string $username
	 * @param string $password
	 * @return PwError|true
	 */
	public function doBanding($username, $password) {
		if (true !== ($isCheck = $this->check($username, $password))) return $isCheck;
		$list = $this->_loadMajia()->fetchByUid(array($this->loginBo->uid, $this->bandedUser['uid']));
		$dms = $this->buildDms();
		if (empty($list)) {
			$id = $this->_loadMajia()->add($dms[1]);
			if ($id instanceof PwError) return $id;
			$dms[2]->setId($id);
			$this->_loadMajia()->add($dms[2]);
		} elseif (isset($list[$this->loginBo->uid]) && !isset($list[$this->bandedUser['uid']])) {
			$dms[2]->setId($list[$this->loginBo->uid]['id']);
			$this->_loadMajia()->add($dms[2]);
			$this->update($dms[1], $list[$this->loginBo->uid]);
		} elseif (!isset($list[$this->loginBo->uid]) && isset($list[$this->bandedUser['uid']])) {
			$dms[1]->setId($list[$this->bandedUser['uid']]['id']);
			$this->_loadMajia()->add($dms[1]);
			$this->update($dms[2], $list[$this->bandedUser['uid']]);
		} elseif (isset($list[$this->loginBo->uid]) && isset($list[$this->bandedUser['uid']])) {
			if ($list[$this->loginBo->uid]['id'] == $list[$this->bandedUser['uid']]['id']) {
				$this->update($dms[1], $list[$this->loginBo->uid]);
				$this->update($dms[2], $list[$this->bandedUser['uid']]);
				$isCheck[1] = $isCheck[2] = true;
				return new PwError('该用户已经被绑定，不能重复绑定');
			} else {
				//$this->_loadMajia()->updateId($list[$this->bandedUser['uid']]['id'], $list[$this->loginBo->uid]['id']);
				$this->update($dms[1], $list[$this->loginBo->uid]);
				$dms[2]->setId($list[$this->loginBo->uid]['id']);
				$this->_loadMajia()->updateByUid($dms[2]);
			}
		}		
		return true;
	}

	/**
	 * 执行重新绑定
	 * 
	 * @param string $username
	 * @param string $password
	 * @return PwError|true
	 */
	public function doReBanding($username, $password) {
		if (true !== ($isCheck = $this->check($username, $password))) return $isCheck;
		$list = $this->_loadMajia()->getByUid($this->loginBo->uid);
		if (!isset($list[$this->bandedUser['uid']])) return new PwError('该用户没有被绑定');
		$dms = $this->buildDms();
		$this->update($dms[1], $list[$this->loginBo->uid]);
		return $this->_loadMajia()->updateByUid($dms[2]);
	}
	
	/**
	 * 解绑绑定
	 *
	 * @param array $ids
	 * @return int
	 */
	public function doUnBanding($uids) {
		if (!is_array($uids)) $uids = array($uids);
		if (true !== ($result = $this->_check())) return $result;
		$list = $this->_loadMajia()->getByUid($this->loginBo->uid);
		$delUids = array_intersect(array_keys($list), $uids);
		if (!$delUids) return new PwError('解除绑定错误');
		$this->_loadMajia()->batchDeleteByUid($delUids);
		return true;
	}
	
	/**
	 * 获取绑定的用户列表
	 *
	 * @return array
	 */
	public function doGetBanded() {
		if (true !== ($result = $this->_check(false))) return $result;
		$list = $this->_loadMajia()->getByUid($this->loginBo->uid);
		unset($list[$this->loginBo->uid]);
		$list = $this->_loadUser()->fetchUserByUid(array_keys($list));
		$groups = Wekit::load('SRV:usergroup.PwUserGroups')->getAllGroups();
		foreach ($list as $_uid => $_one) {
			$gid = $_one['groupid'] ? $_one['groupid'] : $_one['memberid'];
			$list[$_uid]['group'] = $groups[$gid]['name'];
		}
		return $list;
	}
	
	/**
	 * 切换马甲
	 *
	 * @param int $id
	 * @return PwError|int
	 */
	public function doChangeAccount($uid) {
		if (true !== ($result = $this->_check(false))) return $result;
		$list = $this->_loadMajia()->getByUid($this->loginBo->uid);
		if (!array_key_exists($uid, $list)) return new PwError('该用户没有被绑定');
		$newInfo = $this->_loadUser()->getUserByUid($uid);
		if (!$newInfo) {
			$this->_loadMajia()->deleteByUid($uid);
			return new PwError('非法的用户ID');
		}
		if ($list[$uid]['password'] != $newInfo['password']) return new PwError('密码失效，请重新绑定');
		return true;
	}

	/**
	 * 检查用户登录及应用马甲绑定的状况
	 *
	 * @return PwError|boolean
	 */
	private function _check($ifCheck = true) {
		if (!Wekit::C('app_majia', 'isopen')) return new PwError('马甲切换应用没有开启');
		if (!$this->loginBo->isExists()) return new PwError('用户没有登录');
		if ($ifCheck && !in_array($this->loginBo->gid, Wekit::C('app_majia', 'band.allow.groups'))) return new PwError('您所在的用户组不能使用马甲绑定功能');
		return true;
	}
	
	/**
	 * 更新信息
	 * @param App_Majia_MajiaDm $newDm
	 * @param array $oldData
	 * @return true
	 */
	private function update(App_Majia_MajiaDm $newDm, $oldData) {
		if ($oldData['password'] != $newDm->getField('password')) {
			$this->_loadMajia()->updateByUid($newDm);
		}
		return true;
	}

	/**
	 * 构建DM
	 *
	 * @return array
	 */
	private function buildDms($type = 3) {
		Wind::import('EXT:majia.service.dm.App_Majia_MajiaDm');
		$dms = array();
		if ($type & 1) {
			$dms[1] = new App_Majia_MajiaDm();
			$newInfo = $this->_loadUser()->getUserByUid($this->loginBo->uid);
			$dms[1]->setUid($this->loginBo->uid)->setPassword($newInfo['password']);
		}
		if ($type & 2) {
			$dms[2] = new App_Majia_MajiaDm();
			$dms[2]->setUid($this->bandedUser['uid'])->setPassword($this->bandedUser['password']);
		}
		return $dms;
	}
	
	/**
	 * 返回马甲绑定的DS
	 *
	 * @return APP_Majia_Majia
	 */
	private function _loadMajia() {
		return Wekit::load('EXT:majia.service.App_Majia_Majia');
	}
	
	/**
	 * 加载用户DS
	 *
	 * @return PwUser
	 */
	private function _loadUser() {
		return Wekit::load('SRV:user.PwUser');
	}
}