<?php
abstract class App_Search_Action{

	abstract function countSearchForum(PwForumSo $so);
	
	abstract function searchForum(PwForumSo $so, $limit = 20, $start = 0);
	
	abstract function countSearchThread(PwThreadSo $so);
	
	abstract function searchThread(PwThreadSo $so, $limit = 20, $start = 0);
	
	abstract function countSearchUser(PwUserSo $so);
	
	abstract function searchUser(PwUserSo $so, $limit = 20, $start = 0);
	
	/**
	 * 检查关键字查询条件
	 * 
	 * @param string $keyword
	 * @return string 关键字
	 */
	protected function _checkKeywordCondition($keyword) {
		if (strlen ( $keyword ) < 3) {
			return array ();
		}
		$keyword = trim ( ($keyword) );
		$keyword = str_replace ( array ("&#160;", "&#61;", "&nbsp;", "&#60;", "<", ">", "&gt;", "(", ")", "&#41;" ), ' ', $keyword );
		$ks = explode ( " ", $keyword );
		$keywords = array ();
		foreach ( $ks as $v ) {
			$v = trim ( $v );
			($v) && $keywords [] = $v;
		}
		if (! $keywords) {
			return array ();
		}
		$keywords = implode ( " ", $keywords );
		return $keywords;
	}
}