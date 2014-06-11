<?php
Wind::import("LIB:engine.hook.PwBaseHookService");
class DemoTestHookService extends PwBaseHookService {

	public function run($args) {
		print_r($args);
		echo 'hello i am display1111111.';
		return 'abcdefg';
	}

	public function display() {
		$this->runDo('display');
		echo 'hello i am display.';
	}

	/* (non-PHPdoc)
	 * @see PwBaseHookService::_getInterfaceName
	 */
	protected function _getInterfaceName() {
		return 'DemoHookInterface';
	}
}

?>