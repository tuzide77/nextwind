<?php

/**
 * 评分记录
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_Record {
	
	/**
	 * 获取一条数据
	 *
	 * @param int $id
	 * @return array 
	 */
	public function getRecord($id) {
		$id = intval($id);
		if ($id < 1) return array();
		return $this->_getRecordDao()->get($id);
	}
	
	/**
	 * 获取多条数据
	 *
	 * @param array $ids
	 * @return array 
	 */
	public function fetchRecord($ids) {
		if (!is_array($ids) || !$ids) return array();
		return $this->_getRecordDao()->fetch($ids);
	}
	
	/**
	 * 统计评分数量
	 *
	 * @param int $tid
	 * @param int $pid
	 * @return int
	 */
	public function countByTidAndPid($tid, $pid = 0) {
		$tid = intval($tid);
		$pid = intval($pid);
		if ($tid < 1) return 0;
		return $this->_getRecordDao()->countByTidAndPid($tid, $pid);
	}
	
	/**
	 * 根据用户获取
	 *
	 * @param int $uid
	 * @param int $tid
	 * @param int $pid
	 * @return array 
	 */
	public function getByUid($uid, $tid, $pid = 0) {
		$uid = intval($uid);
		$tid = intval($tid);
		$pid = intval($pid);
		if ($uid < 1 || $tid < 1) return array();
		return $this->_getRecordDao()->getByUid($uid, $tid, $pid);
	}
	
	/**
	 * 获取评分数据
	 *
	 * @param int $tid
	 * @param int $pid
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getByTidAndPid($tid, $pid, $limit = 10, $offset = 0) {
		$tid = intval($tid);
		$pid = intval($pid);
		if ($tid < 1) return array();
		return $this->_getRecordDao()->getByTidAndPid($tid, $pid, $limit, $offset);
	}
	
	/**
	 * 添加
	 *
	 * @param App_Mark_RecordDm $dm
	 * @return bool 
	 */
	public function addRecord(App_Mark_RecordDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getRecordDao()->add($dm->getData());
	}
	
	/**
	 * 删除一条
	 *
	 * @param int $id
	 * @return bool 
	 */
	public function deleteRecord($id) {
		$id = intval($id);
		if ($id < 1) return false;
		return $this->_getRecordDao()->delete($id);
	}	 
	
	/**
	 * 批量删除
	 *
	 * @param array $ids
	 * @return bool 
	 */
	public function batchDelete($ids) {
		if (!is_array($ids) || !$ids) return array();
		return $this->_getRecordDao()->batchDelete($ids);
	}
	
	/**
	 * 编辑
	 *
	 * @param int $id
	 * @param App_Mark_RecordDm $dm
	 * @return array 
	 */
	public function updateRecord($id, App_Mark_RecordDm $dm) {
		if (($result = $dm->beforeUpdate()) instanceof PwError) return $result;
		return $this->_getRecordDao()->update($id,$dm->getData());
	}	
	
	/**
	 * 安装加字段
	 *
	 * @return bool 
	 */
	public function alterAddThreadMark() {
		return $this->_getRecordDao()->alterAddThreadMark();
	}
	
	/**
	 * 卸载加字段
	 *
	 * @return bool 
	 */
	public function alterDropThreadMark() {
		return $this->_getRecordDao()->alterDropThreadMark();
	}
	
	/**
	 * @return App_Mark_RecordDao
	 */
	protected function _getRecordDao() {
		return Wekit::loadDao('EXT:mark.service.dao.App_Mark_RecordDao');
	}
}