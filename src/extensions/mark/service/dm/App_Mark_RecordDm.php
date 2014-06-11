<?php

/**
 * 评分记录DM
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_RecordDm extends PwBaseDm {
	
	/** 
	 * 设置版块ID
	 *
	 * @param int $fid
	 * @return App_Mark_RecordDm
	 */
	public function setFid($fid) {
		$this->_data['fid'] = intval($fid);
		return $this;
	}
	
	/** 
	 * 设置帖子ID
	 *
	 * @param int $tid
	 * @return App_Mark_RecordDm
	 */
	public function setTid($tid) {
		$this->_data['tid'] = intval($tid);
		return $this; 
	}
	
	/** 
	 * 设置回复ID
	 *
	 * @param int $pid
	 * @return App_Mark_RecordDm
	 */
	public function setPid($pid) {
		$this->_data['pid'] = intval($pid);
		return $this; 
	}
	
	/** 
	 * 设置评分人uid
	 *
	 * @param int $created_userid
	 * @return App_Mark_RecordDm
	 */
	public function setCreatedUserid($created_userid) {
		$this->_data['created_userid'] = intval($created_userid);
		return $this; 
	}
	
	/** 
	 * 设置评分人username
	 *
	 * @param string $username
	 * @return App_Mark_RecordDm
	 */
	public function setCreatedUsername($username) {
		$this->_data['created_username'] = trim($username);
		return $this; 
	}
	
	/** 
	 * 设置被评分人uid
	 *
	 * @param int $autoherUid
	 * @return App_Mark_RecordDm
	 */
	public function setPingUserid($autoherUid) {
		$this->_data['ping_userid'] = intval($autoherUid);
		return $this; 
	}
	
	/** 
	 * 设置评分时间
	 *
	 * @return App_Mark_RecordDm
	 */
	public function setCreatedTime() {
		$this->_data['created_time'] = Pw::getTime();
		return $this; 
	}
	
	/** 
	 * 设置积分类型
	 *
	 * @param int $ctype
	 * @return App_Mark_RecordDm
	 */
	public function setCtype($ctype) {
		$this->_data['ctype'] = intval($ctype);
		return $this; 
	}
	
	/** 
	 * 设置积分数量
	 *
	 * @param int $cnum
	 * @return App_Mark_RecordDm
	 */
	public function setCnum($cnum) {
		$this->_data['cnum'] = $cnum;
		return $this; 
	}
	
	/** 
	 * 设置原因
	 *
	 * @param string $reason
	 * @return App_Mark_RecordDm
	 */
	public function setReason($reason) {
		$this->_data['reason'] = trim($reason);
		return $this; 
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		$this->setCreatedTime();
		return $this->check();
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		return $this->check();
	}
	
	/**
	 * 检查数据
	 *
	 * @return PwError
	 */
	protected function check() {
		if (!isset($this->_data['created_userid'])) return new PwError('user.not.login');
		if (!isset($this->_data['tid'])) return new PwError('data.error');
		return true;
	}
}