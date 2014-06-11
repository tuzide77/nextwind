<?php
Wind::import('SRC:library.base.PwBaseDao');

/**
 * 评分记录
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_RecordDao extends PwBaseDao {
	
	protected $_table = 'app_mark_record';
	protected $_dataStruct = array('id', 'fid', 'tid', 'pid', 'created_userid', 'created_username', 'ping_userid', 'created_time', 'ctype', 'cnum', 'reason');
	
	/**
	 * 获取一条信息
	 *
	 * @param int $id
	 * @return array
	 */
	public function get($id) {
		return $this->_get($id);
	}
	
	/**
	 * 获取一条信息
	 *
	 * @param array $ids
	 * @return array
	 */
	public function fetch($ids) {
		return $this->_fetch($ids);
	}
	
	/**
	 * 获取评分数据
	 *
	 * @param int $tid
	 * @param int $pid
	 * @param int $uid
	 * @return array
	 */
	public function getByUid($uid, $tid, $pid) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE `tid`=? AND `pid`=? AND `created_userid`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($tid, $pid, $uid));
	}
	
	/**
	 * 单条添加
	 *
	 * @param array $data
	 * @return bool
	 */
	public function add($data) {
		return $this->_add($data);
	}
	
	/**
	 * 单条删除
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		return $this->_delete($id);
	}
	
	/**
	 * 批量删除
	 *
	 * @param array $ids
	 * @return bool
	 */
	public function batchDelete($ids) {
		return $this->_batchDelete($ids);
	}
	
	/**
	 * 单条修改
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id,$data) {
		return $this->_update($id,$data);
	}
	
	/**
	 * 统计评分数量
	 *
	 * @param int $tid
	 * @param int $pid
	 * @return int
	 */
	public function countByTidAndPid($tid, $pid) {
		$sql = $this->_bindTable('SELECT COUNT(*) FROM %s WHERE `tid`=? AND `pid`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($tid, $pid));
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
	public function getByTidAndPid($tid, $pid, $limit, $offset) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `tid`=? AND `pid`=? ORDER BY `created_time` DESC %s', $this->getTable(), $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($tid, $pid), 'id');
	}
	
	public function alterAddThreadMark() {
		
		$sql = $this->_bindSql("ALTER TABLE %s ADD `app_mark` varchar(150) NOT NULL DEFAULT ''", $this->getTable('bbs_threads'));
		$this->getConnection()->execute($sql);
		$sql = $this->_bindSql("ALTER TABLE %s ADD `app_mark` varchar(150) NOT NULL DEFAULT ''", $this->getTable('bbs_posts'));
		return $this->getConnection()->execute($sql);
	}
	
	public function alterDropThreadMark() {
		$sql = $this->_bindSql("ALTER TABLE %s DROP `app_mark`", $this->getTable('bbs_threads'));
		$this->getConnection()->execute($sql);
		$sql = $this->_bindSql("ALTER TABLE %s DROP `app_mark`", $this->getTable('bbs_posts'));
		$this->getConnection()->execute($sql);
	}
}