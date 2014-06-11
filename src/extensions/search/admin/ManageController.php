<?php
Wind::import('ADMIN:library.AdminBaseController');

/**
 * 本地搜索后台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class ManageController extends AdminBaseController {

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$conf['isopen'] = Wekit::C('site', 'search.isopen');
		$this->setOutput($conf, 'conf');
	}

	/**
	 * 保存搜索设置
	 *
	 */
	public function doRunAction() {
		$conf = $this->getInput('conf', 'post');
		$config = new PwConfigSet('site');
		$config->set('search.isopen', $conf['isopen'])
			->flush();
		$this->showMessage('success');
	}
	
}

?>