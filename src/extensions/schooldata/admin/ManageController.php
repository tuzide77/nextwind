<?php
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('WINDID:service.school.dm.WindidSchoolDm');
Wind::import('WINDID:service.school.WindidSchool');

/**
 * 中国数据
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class ManageController extends AdminBaseController {
	// 小学 
	const PRIMARY = 6;
	// 初中 
	const HIGN = 5;
	// 大学 
	const UNIVERSITY = 3;
	// 高中
	const SENIOR = 4;
	
	
	protected static $_map = null;

	public function run() {
		
	}
	
	/**
	 * 生成大学数据
	 *
	 * @return 
	 */
	public function unversityAction() {
		$key = $this->getInput('key' , 'get');
		!$key && $key = 0;
		$areas = $this->buildThreeArea();
		$maxKey = count($areas);
		$id = $areas[$key];
		$data = $this->buildData($id, self::UNIVERSITY);
		
		if ($key < $maxKey) {
			$key = $key + 1;
			$this->showMessage('正在生成大学数据，共：'.$maxKey.'步骤，已完成：'.$key.'步骤','app/schooldata/manage/unversity?key='.$key,true);
		} else {
			$this->showMessage('大学数据生成完成。。。');
		}
	}

	/**
	 * 生成高中数据
	 *
	 * @return 
	 */
	public function seniorAction() {
		$key = $this->getInput('key' , 'get');
		!$key && $key = 0;
		$areas = $this->buildThreeArea();
		$maxKey = count($areas);
		$id = $areas[$key];
		$data = $this->buildData($id, self::SENIOR);
		
		if ($key < $maxKey) {
			$key = $key + 1;
			$this->showMessage('正在生成高中数据，共：'.$maxKey.'步骤，已完成：'.$key.'步骤','app/schooldata/manage/senior?key='.$key,true);
		} else {
			$this->showMessage('高中数据生成完成。。。');
		}
	}
	
	/**
	 * 生成初中数据
	 *
	 * @return 
	 */
	public function highAction() {
		$key = $this->getInput('key' , 'get');
		!$key && $key = 0;
		$areas = $this->buildThreeArea();
		$maxKey = count($areas);
		$id = $areas[$key];
		$data = $this->buildData($id, self::HIGN);
		
		if ($key < $maxKey) {
			$key = $key + 1;
			$this->showMessage('正在生成初中数据，共：'.$maxKey.'步骤，已完成：'.$key.'步骤','app/schooldata/manage/high?key='.$key,true);
		} else {
			$this->showMessage('初中数据生成完成。。。');
		}
	}
	
	/**
	 * 生成小学数据
	 *
	 * @return 
	 */
	public function primaryAction() {
		$key = $this->getInput('key' , 'get');
		!$key && $key = 0;
		$areas = $this->buildThreeArea();
		$maxKey = count($areas);
		$id = $areas[$key];
		$data = $this->buildData($id, self::PRIMARY);
		
		if ($key < $maxKey) {
			$key = $key + 1;
			$this->showMessage('正在生成小学数据，共：'.$maxKey.'步骤，已完成：'.$key.'步骤','app/schooldata/manage/primary?key='.$key,true);
		} else {
			$this->showMessage('小学数据生成完成。。。');
		}
	}
	
	protected function buildData($id, $type) {
		$url = 'http://api.pengyou.com/json.php?cb=__i_5&mod=school&act=selector&schooltype='.$type.'&district='.$id;
		$content = file_get_contents($url);
		preg_match('/{(.*)}/is', $content, $matches);
		$content = WindJson::decode($matches[0]);
		$result = $content['result'];
		preg_match_all('/javascript:choose_school\((\d+),\'(.*)\'\);/iUs', $result, $matches);
		
		$addDms = array();
		foreach ($matches[1] as $k=>$v) {
			$dm = new WindidSchoolDm();
			$dm->setName($matches[2][$k])
				->setTypeid($this->getSchoolType($type))
				->setAreaid($id)
				->setFirstChar($this->_getService()->getFirstChar($matches[2][$k]));
			$addDms[] = $dm;
		}
		return $this->_getSchoolDs()->batchAddSchool($addDms);
	}
	
	protected function buildThreeArea() {
		$fileTmp = WindSecurity::escapePath(Wind::getRealDir('DATA:'). 'tmp/' . 'area.php');
		file_exists($fileTmp) && self::$_map = include $fileTmp;
		if (!self::$_map) {
			$area = $this->_getAreaDs()->fetchAll();
			foreach ($area as $key => $value) {
				if ($value['parentid'] < 100) continue;
				self::$_map[] = $value['areaid'];
			}
			WindFile::savePhpData($fileTmp, self::$_map);
		}
		return self::$_map;
		
	}
	
	protected function getSchoolType($type) {
		$types = array(
			self::SENIOR => WindidSchool::HIGN,
			self::HIGN => WindidSchool::HIGN,
			self::PRIMARY => WindidSchool::PRIMARY,
		);
		return $types[$type];
	}
	
	/**
	 * 学校的service
	 *
	 * @return PwSchoolService
	 */
	private function _getService() {
		return Wekit::load('school.srv.PwSchoolService');
	}
		
	/**
	 * @return WindidArea
	 */
	protected function _getAreaDs() {
		return Windid::load('area.WindidArea');
	}
	
	/**
	 * @return WindidSchool
	 */
	protected function _getSchoolDs() {
		return Windid::load('school.WindidSchool');
	}
}

?>