<?php

/**
 * 本地搜索
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Search_Service {
	protected $_service = null;

	/**
	 * 构造函数
	 *
	 */
	public function __construct() {
		$this->_service = $this->_getFactory('mysql');
	}

	/**
	 * 搜索统计版块
	 * 
	 * @param PwForumSo $so
	 * @return int
	 */
	function countSearchForum(PwForumSo $so) {
		return $this->_service->countSearchForum($so);
	}

	/**
	 * 搜索版块
	 * 
	 * @param PwForumSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchForum(PwForumSo $so, $limit = 20, $start = 0) {
		return $this->_service->searchForum($so, $limit, $start);
	}

	/**
	 * 搜索统计帖子
	 * 
	 * @param PwThreadSo $so
	 * @return int
	 */
	function countSearchThread(PwThreadSo $so) {
		return $this->_service->countSearchThread($so);
	}

	/**
	 * 搜索帖子
	 * 
	 * @param PwThreadSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchThread(PwThreadSo $so, $limit = 20, $start = 0) {
		return $this->_service->searchThread($so, $limit, $start);
	}

	/**
	 * 搜索统计用户
	 * 
	 * @param PwUserSo $so
	 * @return int
	 */
	function countSearchUser(PwUserSo $so) {
		return $this->_service->countSearchUser($so);
	}

	/**
	 * 搜索用户
	 * 
	 * @param PwUserSo $so
	 * @param int $limit 查询条数
	 * @param int $start 开始查询的位置
	 * @return array
	 */
	function searchUser(PwUserSo $so, $limit = 20, $start = 0) {
		return $this->_service->searchUser($so, $limit, $start);
	}

	/**
	 * 组装帖子数据
	 * @param array $threads
	 * @param string $keywords
	 * @return unknown_type
	 */
	public function buildThreads($threads, $keywords) {
		if (!$threads) return false;
		$keywords = (is_array($keywords)) ? $keywords : explode(" ", $keywords);
		$data = array();
		foreach ($threads as $t) {
			$t['subject'] = strip_tags($t['subject']);
			$t['content'] = strip_tags($t['content']);
			$t['content'] = Wekit::load('forum.srv.PwThreadService')->displayContent($t['content'], $t['useubb'], array(), 170);
			foreach ($keywords as $keyword) {
				$keyword = stripslashes($keyword);
				$keyword && $t['subject'] = $this->_highlighting($t['subject'], $keyword);
				$keyword && $t['content'] = $this->_highlighting($t['content'], $keyword);
			}
			$data[] = $t;
		}
		return $data;
	}
	
	private function _highlighting($subject, $pattern) {
		return str_ireplace($pattern, '<em>' . $pattern . '</em>', $subject);
	}
	
	public function buildForums($forums, $keywords) {
		$forum = array();
		foreach ($forums as $_key => $_item) {
			if (1 != $_item['isshow']) continue;
			$_item['name'] = $this->_highlighting(strip_tags($_item['name']), $keywords);
			$_item['manager'] = $this->_setManages(array_unique(explode(',', $_item['manager'])));
			$forum[$_key] = $_item;
		}
		return $forum;
	}
	
	public function buildUsers($users, $keywords) {
		$user = array();
		foreach ($users as $_key => $_item) {
			$_item['username'] = $this->_highlighting(strip_tags($_item['username']), $keywords);
			$user[$_key] = $_item;
		}
		return $user;
	}
	
	/**
	 * 设置版块的版主UID
	 *
	 * @param array $manage
	 * @param array $userList
	 * @return array
	 */
	private function _setManages($manage) {
		$_manage = array();
		foreach ($manage as $_v) {
			if ($_v) $_manage[] = $_v;
		}
		return $_manage;
	}
	
	/**
	 * 举报工厂服务加载
	 * 
	 * @param string $type
	 * @return unknown_type
	 */
	protected function _getFactory($name) {
		if (!$name) return null;
		$name = strtolower($name);
		$className = sprintf('App_Search_%s', ucfirst($name));
		if (class_exists($className, false)) {
			return new $className();
		}
		$fliePath = 'EXT:search.service.srv.action.' . $className;
		Wind::import($fliePath);
		return new $className();
	}
}
