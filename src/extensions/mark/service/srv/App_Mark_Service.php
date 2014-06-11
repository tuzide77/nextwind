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
class App_Mark_Service {
	
	/**
	 * 获取评分数据
	 *
	 * @param int $tid
	 * @param int $pid
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getMarkList($tid, $pid, $limit = 10, $offset = 0) {
		$_list = $this->_getMark()->getByTidAndPid($tid, $pid, $limit, $offset);
		Wind::import('SRV:credit.bo.PwCreditBo');
		$pwCreditBo = PwCreditBo::getInstance();
		$cType = $pwCreditBo->cType;
		$list = array();
		foreach ($_list as $v) {
			$v['ctype'] = $cType[$v['ctype']];
			$list[] = $v;
		}
		return $list;
	}
	
	public function initMarkCredits() {
		$loginUser = Wekit::getLoginUser();
		$app_mark_credits = $loginUser->getPermission('app_mark_credits');
		$markset = $_markset = array();
		Wind::import('SRV:credit.bo.PwCreditBo');
		$pwCreditBo = PwCreditBo::getInstance();
		$behaviors = $this->getMarkBehavior($loginUser->uid, $app_mark_credits);
		foreach ($app_mark_credits as $key => $value) {
			if (!isset($pwCreditBo->cType[$key])) continue;
			if (!$value['isopen']) continue;
			if ($value['min'] && $value['max']) {
				$_markset[$key]['min']		= (int)$value['min'];
				$_markset[$key]['max']		= (int)$value['max'];
				$_markset[$key]['dayMax']	= (int)$value['dayMax'];
				$_markset[$key]['markdt']		= (int)$value['markdt'];	
				$reduse = $value['dayMax'] - $behaviors[$key];		
				$_markset[$key]['leavepoint']		= (int)min($loginUser->info['credit' . $key], max($reduse, 0));
			}
		}
		$ratelist = $result = array();
		foreach($_markset as $id => $rating) {
			$increaseOffset = floor((abs($rating['max'])+1) /4);
			$decreaseOffset = floor((abs($rating['min'])+1)/ 4);
			if($rating['min'] >= 0){							//如果最小值大于0
				$rating['min'] == 0 && $rating['min'] = 1;
				$min[$id] = $rating['min'];						//加上$min[$id]标记
				$increaseOffset = floor(($rating['max'] - $rating['min'])/4);   //增加的步长改变
			}
			if($rating['max'] < 0){								//如果最大值小于0
				$rating['max'] == 0 && $rating['max'] = 1;
				$max[$id] = $rating['max'];						//加上$max[$id]标记
				$decreaseOffset = floor(abs($rating['min'] - $rating['max'])/4);
			}

			if($increaseOffset == 0) $increaseOffset = 1;
			if($decreaseOffset == 0) $decreaseOffset = 1;
			if($rating['max'] > $rating['min']) {
				for($i=1; $i<5; $i++){			//首和尾的数值固定，只需循环4次
					if($min[$id]){	//如果最小值大于0
						$ratelist[$id]['max'][$i] = $i > 1 ? '+'.(strval($ratelist[$id]['max'][$i-1])+$increaseOffset) : '+'.$min[$id];
						$ratelist[$id]['min'] = array();
						if($ratelist[$id]['max'][$i] >= $rating['max']) $ratelist[$id]['max'][$i] = '+'.$rating['max']; 
					}elseif($max[$id]){	// 如果最大值小于0
						$ratelist[$id]['max'] = array();
						$ratelist[$id]['min'][$i] = $i > 1 ? (strval($ratelist[$id]['min'][$i-1])-$decreaseOffset) : $max[$id];
						if($ratelist[$id]['min'][$i] && ($ratelist[$id]['min'][$i] <= $rating['min'])) $ratelist[$id]['min'][$i] = $rating['min'];
					}else{
						$ratelist[$id]['max'][$i] = $i > 1 ? '+'.(strval($ratelist[$id]['max'][$i-1])+$increaseOffset) : '+1';
						$ratelist[$id]['min'][$i] = $i > 1 ? (strval($ratelist[$id]['min'][$i-1])-$decreaseOffset) : '-1';
						if($ratelist[$id]['min'][$i] && ($ratelist[$id]['min'][$i] <= $rating['min'])) $ratelist[$id]['min'][$i] = $rating['min'];
						if($ratelist[$id]['max'][$i] >= $rating['max']) $ratelist[$id]['max'][$i] = '+'.$rating['max']; 
					}
				}
				array_push($ratelist[$id]['max'], '+'.$rating['max']);	//在末尾加上最大值
				array_push($ratelist[$id]['min'], $rating['min']);		//在末尾加上最小值
				$ratelist[$id]['max'] = array_unique($ratelist[$id]['max']);
				$ratelist[$id]['min'] = array_unique($ratelist[$id]['min']);
				if($min[$id]) $ratelist[$id]['min'] = array();		//最小值大于0，最大值小于0，则该行不显示
				if($max[$id]) $ratelist[$id]['max'] = array();
				
			} elseif ($rating['max'] == $rating['min']) {
				$ratelist[$id]['max'] = array($rating['max']);
				$ratelist[$id]['min'] = array();
			}
			$ratelist[$id]['name'] = $pwCreditBo->cType[$id];
			$ratelist[$id]['leavepoint'] = $rating['leavepoint'];
		}
		return $ratelist;
	}
	
	public function getMarkBehavior($uid, $cTypes = array()) {
		if (!$cTypes) return array();
		$behaviors = $this->_getUserBehaviorDs()->getBehaviorList($uid);
		if (!$behaviors) return array();
		$time = Pw::getTime();
		$array = array();
		foreach ($cTypes as $k => $v) {
			$key = sprintf('app_mark_%d', $k);
			if (!isset($behaviors[$key])) continue;
			if($behaviors[$key]['expired_time'] > 0 && $behaviors[$key]['expired_time'] < $time) $behaviors[$key]['number'] = 0;
			$array[$k] = $behaviors[$key]['number'];
		}
		return $array;	
	}
	
	public function splitStringToArray($string) {
		if (!$string) return array(0, 0, array(), array());
		list($cnum, $count, $hCredits, $hIds) = explode('|', $string);
		$ids = explode(',', $hIds);
		$a = explode(',', $hCredits);
		$l = count($a);
		$l % 2 == 1 && $l--;
		$credits = array();
		for ($i = 0; $i < $l; $i+=2) {
			$credits[$a[$i]] = $a[$i+1];
		}
		return array($cnum, $count, $credits, $ids);
	}
    
	public function buildMarkCredit($credits) {
		Wind::import('SRV:credit.bo.PwCreditBo');
		$pwCreditBo = PwCreditBo::getInstance();
		$cType = $pwCreditBo->cType;
		$array = array();
		foreach ($credits as $k => $v) {
			$array[$cType[$k]] = $v;
		}
		return $array;
	}
	
	/**
	 * PwUserBehavior
	 * 
	 * @return PwUserBehavior
	 */
	private function _getUserBehaviorDs() {
		return Wekit::load('user.PwUserBehavior');
	}
	
	/**
	 * @return App_Mark_Record
	 */
	private function _getMark() {
		return Wekit::load('EXT:mark.service.App_Mark_Record');
	}
}
