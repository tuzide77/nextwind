<?php
defined('RUN_STARTTIME') or define('RUN_STARTTIME', microtime(true));
Wind::import('EXT:search.service.App_Search_Record');
Wind::import('SRV:seo.bo.PwSeoBo');

/**
 * 本地搜索
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class IndexController extends PwBaseController {
	protected $perpage = 20;
	protected $maxNum = 500;
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		if (!Wekit::C('site', 'search.isopen')) {
			$this->forwardRedirect(WindUrlHelper::createUrl('search/search/run'));
		}
		if ($this->loginUser->uid < 1) {
			$this->forwardAction('u/login/run', array('backurl' => WindUrlHelper::createUrl('app/search/index/run')));
		}
		$a = $this->getInput('a');
		!$a && $a = 'run';
		$this->setOutput($this->_limitTimeMap(), 'limittime');
		$this->setOutput($a, 'src');
	}
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		if (($result = $this->_checkRight()) instanceof PwError) {
			$this->showError($result->getError());
		}
		list($page, $perpage, $keywords, $fid, $limittime, $orderby) = $this->getInput(array('page', 'perpage', 'keywords', 'fid', 'limittime', 'orderby'));
		if ($keywords) {
			//最后搜索时间
			if (($result = $this->_checkSearch()) instanceof PwError) {
				$this->showError($result->getError());
			}
			$page = $page ? $page : 1;
			$perpage = $perpage ? $perpage : $this->perpage;
			list($start, $limit) = Pw::page2limit($page, $perpage);
			!$orderby && $orderby = 'lastpost_time';
			Wind::import('SRV:forum.vo.PwThreadSo');
			$so = new PwThreadSo();
			$keywords = urldecode($keywords);
			$so->setKeywordOfTitleOrContent($keywords);
			$fid && $so->setFid($fid);
			$limittime && $so->setCreateTimeStart($this->_getLimitTime($limittime));
			$so = $this->_getOrderBy($so, $orderby);
			$count = $this->_getSearchService()->countSearchThread($so);
			$count = $count > $this->maxNum ? $this->maxNum : $count;
			if ($count) {
				$threads = $this->_getSearchService()->searchThread($so, $limit, $start);
				$threads = $this->_getSearchService()->buildThreads($threads, $keywords);
				$this->_replaceRecord($keywords, App_Search_Record::TYPE_THREAD);
			}
			$this->setOutput($page, 'page');
			$this->setOutput($perpage, 'perpage');
			$this->setOutput($count, 'count');
			$this->setOutput($threads, 'threads');
			$this->setOutput(array(1 => 'img', 3 => 'img', 4 => 'file', 5 => 'img',7 => 'img'), 'uploadIcon');
			$this->setOutput(array('img' => '图片帖','file' => '附件'), 'icon');

		}
		$args = array('keywords' => $keywords, 'fid' => $fid, 'limittime' => $limittime, 'orderby' => $orderby);
		$this->setOutput($args, 'args');
		$forumList = Wekit::load('forum.srv.PwForumService')->getForumList();
		$this->setOutput(App_Search_Record::TYPE_THREAD, 'recordType');
		$this->setOutput($forumList, 'forumList');
		$this->setOutput($this->getCommonForumList($forumList), 'forumdb');
		$this->setTemplate('index_run');
		
		//seo设置
		$seoBo = PwSeoBo::getInstance();
		$seoBo->setCustomSeo($keywords . ' - {sitename}', '', '');
		Wekit::setV('seo', $seoBo);
	}
	
	public function userAction() {
		if (($result = $this->_checkRight()) instanceof PwError) {
			$this->showError($result->getError());
		}
		list($page, $perpage, $keywords, $limittime, $orderby) = $this->getInput(array('page', 'perpage', 'keywords', 'limittime', 'orderby'));
		$args = array();
		if ($keywords) {
			//最后搜索时间
			if (($result = $this->_checkSearch()) instanceof PwError) {
				$this->showError($result->getError());
			}
			$page = $page ? $page : 1;
			$perpage = $perpage ? $perpage : $this->perpage;
			list($start, $limit) = Pw::page2limit($page, $perpage);
			Wind::import('SRV:user.vo.PwUserSo');
			$keywords = urldecode($keywords);
			$so = new PwUserSo();
			$so->setUsername($keywords)
				->orderbyLastpost(0);
			$limittime && $so->setRegdate($this->_getLimitTime($limittime));
			$count = $this->_getSearchService()->countSearchUser($so);
			$count = $count > $this->maxNum ? $this->maxNum : $count;
			if ($count) {
				$users = $this->_getSearchService()->searchUser($so, $limit, $start);
				$users = $this->_getSearchService()->buildUsers($users, $keywords);
				$uids = array_keys($users);
				$follows = Wekit::load('attention.PwAttention')->fetchFollows($this->loginUser->uid, $uids);
				$fans = Wekit::load('attention.PwAttention')->fetchFans($this->loginUser->uid, $uids);
				$friends = array_intersect_key($fans, $follows);
				$this->setOutput($fans, 'fans');
				$this->setOutput($friends, 'friends');
				$this->setOutput($follows, 'follows');
				$this->_replaceRecord($keywords, App_Search_Record::TYPE_USER);
			}
			$this->setOutput($page, 'page');
			$this->setOutput($perpage, 'perpage');
			$this->setOutput($count, 'count');
			$this->setOutput(array('keywords' => $keywords), 'args');
			$this->setOutput($users, 'users');
		}
		$this->setOutput(App_Search_Record::TYPE_USER, 'recordType');
		$this->setTemplate('index_user');
		//seo设置
		$seoBo->setCustomSeo($keywords . ' - {sitename}', '', '');
	}
	
	public function forumAction() {
		if (($result = $this->_checkRight()) instanceof PwError) {
			$this->showError($result->getError());
		}
		list($page, $perpage, $keywords, $limittime, $orderby) = $this->getInput(array('page', 'perpage', 'keywords', 'limittime', 'orderby'));
		$args = array();
		if ($keywords) {
			//最后搜索时间
			if (($result = $this->_checkSearch()) instanceof PwError) {
				$this->showError($result->getError());
			}
			$page = $page ? $page : 1;
			$perpage = $perpage ? $perpage : $this->perpage;
			list($start, $limit) = Pw::page2limit($page, $perpage);
			!$orderby && $orderby = 'lastpost_time';
			Wind::import('SRV:forum.vo.PwForumSo');
			$keywords = urldecode($keywords);
			$so = new PwForumSo();
			$so->setName($keywords);
			$so = $this->_getOrderBy($so, $orderby);
			$count = $this->_getSearchService()->countSearchForum($so);
			$count = $count > $this->maxNum ? $this->maxNum : $count;
			if ($count) {
				$forums = $this->_getSearchService()->searchForum($so, $limit, $start);
				$forums = $this->_getSearchService()->buildForums($forums, $keywords);
				$joinForums = Wekit::load('forum.PwForumUser')->getFroumByUid($this->loginUser->uid);
				$joinForums && $this->setOutput(array_keys($joinForums), 'joinForums');
				$this->_replaceRecord($keywords, App_Search_Record::TYPE_FORUM);
			}
			$this->setOutput($page, 'page');
			$this->setOutput($perpage, 'perpage');
			$this->setOutput($count, 'count');
			$this->setOutput(array('keywords' => $keywords), 'args');
		}
		$this->setOutput($forums, 'forums');
		$this->setOutput(App_Search_Record::TYPE_FORUM, 'recordType');
		$this->setTemplate('index_forum');
		
		//seo设置
		$seoBo->setCustomSeo($keywords . ' - {sitename}', '', '');
	}
	
	public function truncateAction() {
		$type = $this->getInput('type');
		$src = $this->getInput('src');
		$this->_getSearchRecord()->deleteByUidAndType($this->loginUser->uid, $type);
		$this->showMessage('success');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see src/library/base/PwBaseController::afterAction()
	 */
	public function afterAction($handlerAdapter) {
		parent::afterAction($handlerAdapter);
		$this->setOutput($this->_getExecTime(), 'exectime');
	}
	
	protected function _replaceRecord($keywords,$type) {
		$loginUser = Wekit::getLoginUser();
		if (!$keywords || !$loginUser || !$type) return false;
		Wind::import('EXT:search.service.dm.App_Search_RecordDm');
		$ds = $this->_getSearchRecord();
		$dm = new App_Search_RecordDm();
		$dm->setKeywords($keywords)
			->setSearchType($type)
			->setCreatedUserid($loginUser->uid)
			->setCreatedTime(Pw::getTime());
		$this->_getSearchRecord()->replaceRecord($dm);
	}
	
	private function _getOrderBy($so, $orderby = null) {
		switch ($orderby) {
			case 'created_time' :
				$so->orderbyCreatedTime(0);
				break;
			case 'lastpost_time' :
				$so->orderbyLastPostTime(0);
				break;
			default:
				$so->orderbyLastPostTime(0);
				break;
		}
		return $so;
	}
	
	private function _getLimitTime($daytime = '') {
		if (!$daytime) return false;
		$timestamp = Pw::getTime();
		$times = array(
			'today'	=>	$timestamp - 86400,
			'week'	=>	$timestamp - 7 * 86400,
			'month'	=>	$timestamp - 30 * 86400,
			'year'	=>	$timestamp - 365 * 86400,
		);
		return $times[$daytime];
	}
	
	public function getUrlArgs($args, $key) {
		$urlargs = '';
		if (!is_array($args) || !$args) return $urlargs;
		foreach ($args as $k => $v) {
			if ($k == $key || !$v) continue;
			$urlargs .= "&$k=$v";
		}
		return rtrim($urlargs, '&');
	}
	
	private function _limitTimeMap() {
		return array(
			'today'	=>	'最近一天',
			'week'	=>	'最近一周',
			'month'	=>	'最近一月',
			'year'	=>	'最近一年',
		);
	}

	/**
	 * 取得系统运行所耗时间
	 */
	private static function _getExecTime() {
		$useTime = microtime(true) - RUN_STARTTIME;
		return $useTime ? round($useTime, 6) : 0;
	}
	
	private function _checkRight() {
		$loginUser = Wekit::getLoginUser();
		if ($loginUser->gid == 6 || $loginUser->gid == 7) {
			$this->showError('啊哦，你所在的用户组不允许搜索', 'bbs/index/run');
		}
		if ($loginUser->getPermission('app_search_open') < 1) {
			return new PwError('permission.search.allow.not', array('{grouptitle}' =>  $loginUser->getGroupInfo('name')));
		}
		return true;
	}
	
	private function _checkSearch() {
		$loginUser = Wekit::getLoginUser();
		$search_time_interval = $loginUser->getPermission('app_search_time_interval');
		$stampTime = Pw::getTime();
		if ($stampTime - $loginUser->info['last_search_time'] < $search_time_interval) {
			return new PwError('permission.search.limittime.allow', array('{limittime}' =>  $search_time_interval));
		}
		Wind::import('EXT:search.service.dm.App_Search_Dm');
		$dm = new App_Search_Dm($loginUser->uid);
		$dm->setLastSearchTime($stampTime);
		Wekit::load('user.PwUser')->editUser($dm, PwUser::FETCH_DATA);
		return true;
	}
	
	public function getCommonForumList($forumList) {
		$forumdb = array(0 => array());
		if (!$forumList) return $forumdb;
		foreach ($forumList as $forums) {
			if ($forums['issub'] != 0) continue;
			if (!$forums['isshow']) continue;
			if ($forums['type'] === 'forum') {
				$forumdb[$forums['parentid']][$forums['fid']] = $forums;
			} elseif ($forums['type'] === 'category') {
				$forumdb[0][$forums['fid']] = $forums;
			}
		}
		return $forumdb;
	}
	
	/**
	 * @return App_Search_Record
	 */
	private function _getSearchRecord() {
		return Wekit::load('EXT:search.service.App_Search_Record');
	}
	
	/**
	 * @return App_Search_Service
	 */
	private function _getSearchService() {
		return Wekit::load('EXT:search.service.srv.App_Search_Service');
	}
	
}

?>