<?php

/**
 * 搜索记录Ds
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Search_Record {
	
	const TYPE_THREAD = 1; // 搜索帖子
	const TYPE_USER = 2; // 搜索用户
	const TYPE_FORUM = 3; // 搜索版块
	
	/**
	 * 添加
	 *
	 * @param PwRecordDm $dm
	 * @return bool 
	 */
	public function addRecord(App_Search_RecordDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getRecordDao()->replace($dm->getData());
	}
	
	/**
	 * 添加替换 - 最多保存20条
	 *
	 * @param PwRecordDm $dm
	 * @return bool 
	 */
	public function replaceRecord(App_Search_RecordDm $dm) {
		$uid = $dm->getField('created_userid');
		$type = $dm->getField('search_type');
		$count = $this->countByUidAndType($uid, $type);
		if ($count >= 5) {
			$this->_getRecordDao()->deleteByTime();
		}
		return $this->addRecord($dm);
	}
	
	/**
	 * 删除一条
	 *
	 * @param int $id
	 * @return bool 
	 */
	public function deleteRecord($id) {
		$id = intval($id);
		if ($id < 1) {
			return false;
		}
		return $this->_getRecordDao()->delete($id);
	}	 
	
	/**
	 * 根据用户和类型删除
	 *
	 * @param int $uid
	 * @param int $type
	 * @return bool 
	 */
	public function deleteByUidAndType($uid, $type) {
		$uid = intval($uid);
		$type = intval($type);
		if ($uid < 1 || $type < 1) return false;
		return $this->_getRecordDao()->deleteByUidAndType($uid, $type);
	}	
	
	/**
	 * 根据uid获取num条数据
	 * 
	 * @param int $uid
	 * @param int $num
	 * @return array 
	 */
	public function getByUidAndType($uid, $type){
		$uid = intval($uid);
		$type = intval($type);
		if ($uid < 1 || $type < 1) return array();
		return $this->_getRecordDao()->getByUidAndType($uid, $type);
	}
	
	/**
	 * 根据用户统计草稿箱数量
	 * 
	 * @param int $uid
	 * @return array 
	 */
	public function countByUidAndType($uid, $type){
		$uid = intval($uid);
		$type = intval($type);
		if ($uid < 1 || $type < 1) return array();
		return $this->_getRecordDao()->countByUidAndType($uid, $type);
	}
	
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
	 * 编辑
	 *
	 * @param int $id
	 * @param array $data
	 * @return array 
	 */
	public function updateRecord($id, App_Search_RecordDm $dm) {
		if (($result = $dm->beforeUpdate()) instanceof PwError) return $result;
		return $this->_getRecordDao()->update($id,$dm->getData());
	}	
	
	/**
	 * @return App_Search_RecordDao
	 */
	protected function _getRecordDao() {
		return Wekit::loadDao('EXT:search.service.dao.App_Search_RecordDao');
	}
}