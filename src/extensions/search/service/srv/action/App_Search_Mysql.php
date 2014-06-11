<?php
Wind::import('EXT:search.service.srv.action.App_Search_Action');

class App_Search_Mysql extends App_Search_Action{
	

	/**
	 * 搜索统计版块
	 * 
	 * @param PwForumSo $so
	 * @return int
	 */
	function countSearchForum(PwForumSo $so){
		return $this->_getForumSearch()->countSearchForum($so);
	}
	
	/**
	 * 搜索版块
	 * 
	 * @param PwForumSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchForum(PwForumSo $so, $limit = 20, $start = 0){
		return $this->_getForumSearch()->searchDesignForum($so, $limit, $start);
	}
	
	/**
	 * 搜索统计帖子
	 * 
	 * @param PwThreadSo $so
	 * @return int
	 */
	function countSearchThread(PwThreadSo $so){
		return $this->_getThreadSearch()->countSearchThread($so);
	}
	
	/**
	 * 搜索帖子
	 * 
	 * @param PwThreadSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchThread(PwThreadSo $so, $limit = 20, $start = 0, $fetchmode = PwThread::FETCH_ALL){
		return $this->_getThreadSearch()->searchThread($so, $limit, $start, $fetchmode);
	}
	
	/**
	 * 搜索统计用户
	 * 
	 * @param PwUserSo $so
	 * @return int
	 */
	function countSearchUser(PwUserSo $so){
		return $this->_getUserSearch()->countSearchUser($so);
	}
	
	/**
	 * 搜索用户
	 * 
	 * @param PwUserSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchUser(PwUserSo $so, $limit = 20, $start = 0){
		return $this->_getUserSearch()->searchUserAllData($so, $limit, $start);
	}
	
	/**
	 * PwForum
	 *
	 * @return PwForum
	 */
	protected function _getForumSearch() {
		return Wekit::load('SRV:forum.PwForum');
	}
	
	/**
	 * PwThread
	 *
	 * @return PwThread
	 */
	protected function _getThreadSearch() {
		return Wekit::load('SRV:forum.PwThread');
	}
	
	/**
	 * PwUserSearch
	 *
	 * @return PwUserSearch
	 */
	protected function _getUserSearch() {
		return Wekit::load('SRV:user.PwUserSearch');
	}
}