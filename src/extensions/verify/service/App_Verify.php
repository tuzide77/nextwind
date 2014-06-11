<?php

/**
 * 实名认证Ds
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify {
	
	const VERIFY_REALNAME = 1; // 真实姓名
	const VERIFY_AVATAR = 2; // 头像
	const VERIFY_EMAIL = 3; // 邮箱
	const VERIFY_ALIPAY = 4; // 支付宝
	const VERIFY_MOBILE = 5; // 手机
	
	const RIGHT_MESSAGE = 1; // 写私信
	const RIGHT_POSTTOPIC = 2; // 发表主题
	const RIGHT_POSTREPLY = 3; // 发表回复
	
	/**
	 * 获取一条数据
	 *
	 * @param int $uid
	 * @return array 
	 */
	public function getVerify($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		return $this->_getDao()->get($uid);
	}
	
	/**
	 * 添加
	 *
	 * @param App_Verify_Dm $dm
	 * @return bool 
	 */
	public function addVerify(App_Verify_Dm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->add($dm->getData());
	}
	
	/**
	 * 编辑
	 *
	 * @param App_Verify_Dm $dm
	 * @return bool 
	 */
	public function updateVerify($uid, App_Verify_Dm $dm) {
		if (($result = $dm->beforeUpdate()) instanceof PwError) return $result;
		return $this->_getDao()->update($uid, $dm->getData(), $dm->getIncreaseData(), $dm->getBitData());
	}
	
	/**
	 * 添加替换
	 *
	 * @param App_Verify_Dm $dm
	 * @return bool 
	 */
	public function replaceVerify(App_Verify_Dm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->replace($dm->getData());
	}
	
	/**
	 * 删除一条
	 *
	 * @param int $id
	 * @return array 
	 */
	public function deleteVerify($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		return $this->_getDao()->delete($uid);
	}	 

	/**
	 * 搜索统计
	 *
	 * @param PwWordSo $so
	 * @return int
	 */
	public function countSearchVerify(App_Verify_So $so) {
		return $this->_getDao()->countSearchVerify($so->getData());
	}

	/**
	 * 搜索数据
	 *
	 * @param PwWordSo $so
	 * @return array
	 */
	public function searchVerify(App_Verify_So $so, $limit = 20, $offset = 0) {
		return $this->_getDao()->searchVerify($so->getData(), $limit, $offset);
	}
	
	/**
	 * @return App_Verify_Dao
	 */
	protected function _getDao() {
		return Wekit::loadDao('EXT:verify.service.dao.App_Verify_Dao');
	}
}