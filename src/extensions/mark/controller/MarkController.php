<?php

/**
 * 评分
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class MarkController extends PwBaseController {
	protected $perpage = 10;
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$conf = Wekit::C('app_mark');
		if (!$conf['mark.isopen']) {
			$this->showError('评分没有开启');
		}
	}
	
	public function listAction() {
		list($page, $perpage, $tid, $pid) = $this->getInput(array('page', 'perpage', 'tid', 'pid'));
		$page = $page ? $page : 2;
		$perpage = $perpage ? $perpage : $this->perpage;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		$data = $this->_getThreadData($tid, $pid);
		list(, $count, $credits, ) = $this->_getService()->splitStringToArray($data['app_mark']);
		
		$credits && $credits = $this->_getService()->buildMarkCredit($credits);
		if ($count) {
			$list = $this->_getService()->getMarkList($tid, $pid, $limit, $start);
			$this->setOutput($list, 'marks');
		}
		
		$this->setOutput($credits, 'credits');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($page, 'page');
		$this->setOutput($count, 'count');
		$this->setOutput(array('tid' => $tid, 'pid' => $pid), 'args');
	}
	
	public function markAction() {
		list($tid, $pid) = $this->getInput(array('tid', 'pid'), 'get');
		//检查权限
		if (($result = $this->_checkMarkRight($tid, $pid)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$ratelist = $this->_getService()->initMarkCredits();
		
		$this->setOutput($ratelist, 'ratelist');
		$this->setOutput($tid, 'tid');
		$this->setOutput($pid, 'pid');
		$markReasons = Wekit::C('app_mark', 'mark.mark_reasons');
		$this->setOutput(explode("\n", $markReasons), 'markReasons');
	}
	
	public function domarkAction() {
		list($_credits, $tid, $pid, $reason, $isreply) = $this->getInput(array('credits', 'tid', 'pid', 'reason', 'isreply'), 'post');
		if (!$_credits) $this->showError('还未添加评分哦');
		if (!$reason) $this->showError('请输入评分理由');
		$credits = array();
		foreach ($_credits as $k => $v) {
			$v && $credits[$k] = $v;
		}
		//检查权限
		if (($result = $this->_checkMarkRight($tid, $pid)) instanceof PwError) {
			$this->showError($result->getError());
		}
		//检查积分权限
		if (($result = $this->_checkMarkCreditsRight($credits)) instanceof PwError) {
			$this->showError($result->getError());
		}
		// 积分处理
		if (($info = $this->setCredits($credits, $tid, $pid)) instanceof PwError) {
			$this->showError($info->getError());
		}
		Wind::import('EXT:mark.service.dm.App_Mark_RecordDm');
		$newIds = array();
		foreach ($credits as $k => $v) {
			$dm = new App_Mark_RecordDm();
			$dm->setTid($tid)
				->setPid($pid)
				->setCreatedUserid($this->loginUser->uid)
				->setCreatedUsername($this->loginUser->username)
				->setPingUserid($info['created_userid'])
				->setReason($reason)
				->setCtype($k)
				->setCnum($v);
			if (($result = $this->_getDs()->addRecord($dm)) instanceof PwError) {
				$this->showError($result->getError());
			}
			($result && is_numeric($result)) && $newIds[] = $result;
		}
		// 回复帖子
		if ($isreply) {
			Wind::import ( 'SRV:forum.srv.PwPost' );
			Wind::import ( 'SRV:forum.srv.post.PwReplyPost' );
			$rpid = $pid ? $pid : 0;
			$pwPost = new PwPost(new PwReplyPost($tid, $this->loginUser));
			$postDm = $pwPost->getDm();
			$postDm->setContent($reason)
					->setReplyPid($rpid);
			$pwPost->execute($postDm);
		}
		// 更新帖子
		$this->_updateThreadAddMark($credits, $newIds, $info);
		$this->showMessage('success');
	}
	
	public function deleteAction() {
		$ids = $this->getInput('ids', 'post');
		if (!$ids) $this->showError('请至少选择一项');
		$isManage = $this->loginUser->getPermission('app_mark_manage');
		if (!$isManage) {
			$this->showError('您没有评分管理权限');
		}
		$infos = $this->_getDs()->fetchRecord($ids);
		if (!$infos) $this->showError('评分记录不存在');
		$info = current($infos);
		$data = $this->_getThreadData($info['tid'], $info['pid']);
		if (!$data) $this->showError('帖子不存在');
		$subject = Pw::substrs($data['content'], '15');
		Wind::import('SRV:credit.bo.PwCreditBo');
		$creditBo = PwCreditBo::getInstance();
		$dIds = $credits = array();
		foreach ($infos as $k => $v) {
			$dIds[] = $v['id'];
			$cnum = -$v['cnum'];
			$credits[$v['ctype']] += $cnum;
			$creditBo->set($v['ping_userid'], $v['ctype'], $cnum, true);
			// 评分记录
			$creditBo->addLog('app_mark_delete',$credits,new PwUserBo($v['ping_userid']),array(
	            'subject' => $subject,
	            'username' => $this->loginUser->username,
	        ));
		}
		$creditBo->execute();
		$this->_getDs()->batchDelete($dIds);
		$this->_updateThreadDeleteMark($credits, $data);
		
		$this->showMessage('success');
	}
	
	protected function _getThreadData($tid, $pid = 0) {
		$threadDs = Wekit::load('forum.PwThread');
		$data = array();
		if ($pid) {
			$data = $threadDs->getPost($pid);
		} elseif ($tid) {
			$data = $threadDs->getThread($tid);
		}
		return $data;
	}
	
	/**
	 * 获取积分设置 todo
	 */
	protected function setCredits($credits, $tid, $pid = 0) {
		if (!$credits) return false;
		$loginUser = Wekit::getLoginUser();
		Wind::import('SRV:credit.bo.PwCreditBo');
		$creditBo = PwCreditBo::getInstance();
		$threadDs = Wekit::load('forum.PwThread');
		if ($pid) {
			$data = $threadDs->getPost($pid);
			$subject = Pw::substrs($data['content'], '15');
			$created_userid = $data['created_userid'];
			$created_username = $data['created_username'];
		} else {
			$data = $threadDs->getThread($tid);
			$subject = Pw::substrs($data['subject'], '15');
			$created_userid = $data['created_userid'];
			$created_username = $data['created_username'];
		}
		if (!$data) {
			return new PwError('数据错误，帖子或者回复已被删除');
		}

        $app_mark_credits = $loginUser->getPermission('app_mark_credits');
        $toCredits = $creditLog = array();
		foreach ($credits as $k => $v) {
			$absV = $v > 0 ? abs($v) : $v;
			$creditLog[$k] = $absV;
			$creditBo->set($created_userid, $k, $absV, true);
			// 扣除自己的积分
			if (!$app_mark_credits[$k]['markdt']) continue;
			$affect = -abs($v);
			$toCredits[$k] = $affect;
			$creditBo->set($loginUser->uid, $k, $affect, true);
		}
		$creditBo->addLog('app_mark',$creditLog,new PwUserBo($created_userid),array(
            'subject' => $subject,
            'username' => $loginUser->username,
        ));
		$creditBo->addLog('app_tomark',$toCredits,$loginUser,array(
            'subject' => $subject,
            'username' => $loginUser->username,
        ));
		$creditBo->execute();
		return $data;
	}
	
	/**
	 * 检测是否有评分权限
	 */
	protected function _checkMarkRight($tid, $pid) {
		$loginUser = Wekit::getLoginUser();
		if (!$loginUser->isExists()) {
			$this->forwardAction('u/login/run');
		}
		$app_mark_open = $loginUser->getPermission('app_mark_open');
		if ($app_mark_open < 1) {
			return new PwError(sprintf('您所在的用户组%s没有评分权限',$loginUser->getGroupInfo('name')));
		}
		if ($app_mark_open == 1) {
			$info = $this->_getDs()->getByUid($loginUser->uid, $tid, $pid);
			if ($info) {
			 	return new PwError(sprintf('您所在的用户组%s不允许重复评分',$loginUser->getGroupInfo('name')));
			}
		}
		$threadDs = Wekit::load('forum.PwThread');
		if ($pid) {
			$data = $threadDs->getPost($pid);
		} else {
			$data = $threadDs->getThread($tid);
		}
		if (!$data) {
			return new PwError('数据错误，帖子或者回复已被删除');
		}
		if ($data['created_userid'] == $loginUser->uid) {
			 return new PwError('不要给自己评分哦');
		}
		return true;
	}
	
	/**
	 * 检测是否有评分权限
	 */
	protected function _checkMarkCreditsRight($credits) {
		$loginUser = Wekit::getLoginUser();
		Wind::import('SRV:credit.bo.PwCreditBo');
		$creditBo = PwCreditBo::getInstance();
		$app_mark_credits = $loginUser->getPermission('app_mark_credits');
		$behaviors = $this->_getService()->getMarkBehavior($loginUser->uid, $app_mark_credits);
		foreach ($credits as $k => $v) {
			if (!$v) continue;
			if (!$app_mark_credits[$k]['isopen']) {
				return new PwError(sprintf('%s积分类型未开启', $creditBo->cType[$k]));
			}
			if ($v < $app_mark_credits[$k]['min'] || $v > $app_mark_credits[$k]['max']) {
				return new PwError(sprintf('每次评分限制最小%s,最大%s', $app_mark_credits[$k]['min'].$creditBo->cType[$k], $app_mark_credits[$k]['max'].$creditBo->cType[$k]));
			}
			$absValue = abs($v);
			if ($behaviors[$k] + $absValue > $app_mark_credits[$k]['dayMax']) {
				return new PwError(sprintf('您已达每日评分上限%s', $app_mark_credits[$k]['dayMax'].$creditBo->cType[$k]));
			}
			$mycredit = $loginUser->info['credit' . $k];
			if ($loginUser->info['credit' . $k] < $absValue) {
				return new PwError(sprintf('您只有：%s，积分不足，', $mycredit.$creditBo->cType[$k]));
			}
			$this->_getUserBehaviorDs()->replaceDayNumBehavior($loginUser->uid,sprintf('app_mark_%d', $k),Pw::getTime(), $absValue);
		}
		return true;
	}
	
	protected function _updateThread($info, $appMark) {
		$threadDs = Wekit::load('forum.PwThread');
		if ($info['pid']) {
			Wind::import('EXT:mark.service.dm.App_Mark_ReplyDm');
			$dm = new App_Mark_ReplyDm($info['pid']);
			$dm->setMark($appMark);
			$threadDs->updatePost($dm);
		} else {
			Wind::import('EXT:mark.service.dm.App_Mark_TopicDm');
			$dm = new App_Mark_TopicDm($info['tid']);
			$dm->setMark($appMark);
			$threadDs->updateThread($dm, PwThread::FETCH_MAIN);
		}
		return true;
	}
	
	protected function _updateThreadDeleteMark($credits, $info) {
		if (!$info) return false;
		$count = $this->_getDs()->countByTidAndPid($info['tid'], $info['pid']);
		$newIds = array();
		if ($count) {
			$list = $this->_getDs()->getByTidAndPid($info['tid'], $info['pid']);
			$newIds = array_keys($list);
		}
		if ($info['app_mark']) {
			list($cnum, , $hCredits, ) = $this->_getService()->splitStringToArray($info['app_mark']);
		} else {
			list($cnum, , $hCredits, ) = array(0, 0, array(), array());
		}
		
		foreach ($credits as $k => $v) {
			if (!$v) {
				unset($credits[$k]);
				continue;
			}
			if ($hCredits[$k]) {
				$credits[$k] = intval($hCredits[$k]) + intval($v);
			}
			$cnum += intval($v);
		}
		$cnum = $cnum > 0 ? '+' . abs($cnum) : $cnum;
		$newCredits = $credits + $hCredits;
		ksort($newCredits);
		$strCre = '';
		foreach ($newCredits as $key => $value) {
			$strCre .= $key . ',' . ($value > 0 ? '+' . abs($value) : $value) . ',';
		}
		$app_mark = $cnum . '|'. $count . '|' . rtrim($strCre, ',') . '|' . implode(',', $newIds);
		return $this->_updateThread($info, $app_mark);
	}
	
	/**
	 * 格式化积分 格式化成'+30|21|1,+2,2,+5|1,2,3,4,5'，+30代表该帖子评分总数，21代表评分次数，1,+2,2,+5代表积分
	 *
	 * @param array $credits
	 * @param string $app_mark
	 * @return 
	 */
	protected function _updateThreadAddMark($credits, $ids, $info) {
		if (!$info) return false;
		if ($info['app_mark']) {
			list($cnum, $count, $hCredits, $hIds) = $this->_getService()->splitStringToArray($info['app_mark']);
		} else {
			list($cnum, $count, $hCredits, $hIds) = array(0, 0, array(), array());
		}
		
		foreach ($credits as $k => $v) {
			if (!$v) {
				unset($credits[$k]);
				continue;
			}
			if ($hCredits[$k]) {
				$credits[$k] = intval($hCredits[$k]) + intval($v);
			}
			$count ++;
			$cnum += intval($v);
		}
		$cnum = $cnum > 0 ? '+' . abs($cnum) : $cnum;
		$newCredits = $credits + $hCredits;
		$strCre = '';
		foreach ($newCredits as $key => $value) {
			$strCre .= $key . ',' . ($value > 0 ? '+' . abs($value) : $value) . ',';
		}
		$newIds = array_merge($ids, $hIds);
		rsort($newIds,SORT_NUMERIC);
		$newIds = array_slice($newIds, 0, 10);
		$app_mark = $cnum . '|'. $count . '|' . rtrim($strCre, ',') . '|' . implode(',', $newIds);
		return $this->_updateThread($info, $app_mark);
	}
	
	/**
	 * @return App_Mark_Record
	 */
	private function _getDs() {
		return Wekit::load('EXT:mark.service.App_Mark_Record');
	}
	
	/**
	 * @return App_Mark_Service
	 */
	private function _getService() {
		return Wekit::load('EXT:mark.service.srv.App_Mark_Service');
	}
	
	/**
	 * PwUserBehavior
	 * 
	 * @return PwUserBehavior
	 */
	private function _getUserBehaviorDs() {
		return Wekit::load('user.PwUserBehavior');
	}
}

?>