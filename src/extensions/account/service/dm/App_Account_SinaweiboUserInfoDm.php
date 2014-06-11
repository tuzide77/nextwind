<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_SinaweiboUserInfoDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TaobaoUserInfoDm.php 22630 2012-12-26 04:54:55Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_SinaweiboUserInfoDm extends PwBaseDm {
	
	private $user_id;
	
	public function __construct($user_id = 0){
		$user_id = trim($user_id);
		if(!$user_id) return;
		$this->user_id = intval($user_id);
	}
	
	public function setUserId($user_id){
		$this->_data['user_id'] = intval($user_id);
		return $this;
	}
	
	public function setScreenName($screen_name){
		$this->_data['screen_name'] = Pw::convert(trim($screen_name), Wind::getApp()->getResponse()->getCharset(),'UTF-8');;
		return $this;
	}
	
	public function setName($name){
		$this->_data['name'] = Pw::convert(trim($name), Wind::getApp()->getResponse()->getCharset(),'UTF-8');;
		return $this;
	}
	
	public function setProvince($province){
		$this->_data['province'] = intval($province);
		return $this;
	}
	
	public function setCity($city){
		$this->_data['city'] = intval($city);
		return $this;
	}
	
	public function setLocation($location){
		$this->_data['location'] = Pw::convert(trim($location), Wind::getApp()->getResponse()->getCharset(),'UTF-8');;
		return $this;
	}
	
	public function setDescription($description){
		$this->_data['description'] = Pw::convert(trim($description), Wind::getApp()->getResponse()->getCharset(),'UTF-8');;
		return $this;
	}
	
	public function setUrl($url){
		$this->_data['url'] = Pw::convert(trim($url), Wind::getApp()->getResponse()->getCharset(),'UTF-8');;
		return $this;
	}
	
	public function setProfileImageUrl($profile_image_url){
		$this->_data['profile_image_url'] = $profile_image_url;
		return $this;
	}
	
	public function setDomain($domain){
		$this->_data['domain'] = $domain;
		return $this;
	}
	
	public function setGender($gender){
		$this->_data['gender'] = $gender;
		return $this;
	}
	
	public function setFollowersCount($followers_count){
		$this->_data['followers_count'] = intval($followers_count);
		return $this;
	}
	
	public function setFriendsCount($friends_count){
		$this->_data['friends_count'] = intval($friends_count);
		return $this;
	}
	
	public function setStatusesCount($statuses_count){
		$this->_data['statuses_count'] = intval($statuses_count);
		return $this;
	}
	
	public function setFavouritesCount($favourites_count){
		$this->_data['favourites_count'] = intval($favourites_count);
		return $this;
	}
	
	public function setCreatedAt($created_at){
		$this->_data['created_at'] = $created_at;
		return $this;
	}
	
	public function setVerified($verified){
		$this->_data['verified'] = intval($verified);
		return $this;
	}
	
	public function setCreateTime($create_time){
		$this->_data['create_time'] = intval($create_time);
		return $this;
	}
	
	
	public function getUserId(){
		return $this->user_id;
	}
	
	public function checkData(){
		if(empty($this->_data)){
			return new PwError('数据为空');
		}
		return true;
	}
	
	
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if(empty($this->_data['user_id'])) return new PwError('非法数据');
		if(empty($this->_data['screen_name'])) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
		if($this->user_id < 1) return new PwError('非法数据');
		return true;
	}
}

?>