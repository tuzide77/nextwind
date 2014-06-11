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
		$conf = Wekit::C('app_mark');
		$this->setOutput($conf, 'conf');
	}

	/**
	 * 保存设置
	 *
	 */
	public function doRunAction() {
		$conf = $this->getInput('conf', 'post');
		$config = new PwConfigSet('app_mark');
		$config->set('mark.isopen', $conf['isopen'])
			->set('mark.mark_reasons', $conf['mark_reasons'])
			->flush();
		$this->showMessage('success');
	}
	
}

?>