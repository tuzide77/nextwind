<?php
defined('WIND_VERSION') or exit(403);

class WidgetController extends WindController {

	public function beforeAction($handlerAdapter) {
		$url['baseUrl'] = PUBLIC_URL;
		$url['res'] = WindUrlHelper::checkUrl(PUBLIC_RES, PUBLIC_URL);
		$url['css'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/css', PUBLIC_URL);
		$url['images'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/images', PUBLIC_URL);
		$url['js'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/dev', PUBLIC_URL);
		Wekit::setGlobal($url, 'url');
		
		$config = Wind::getComponent('configParser')->parse(Wind::getRealPath('APPS:demo.WindManifest.xml', true, true));
		Wekit::setGlobal($config['application'], 'c');
	}

	public function run() {
		$data = array();
		$data[] = array('name' => 'a', 'value' => '1', 'label' => 'test1');
		$data[] = array('name' => 'a', 'value' => '2', 'label' => 'test2');
		$data[] = array('name' => 'a', 'value' => '3', 'label' => 'test3');
		$data[] = array('name' => 'a', 'value' => '4', 'label' => 'test4');
		$this->setOutput($data, 'data');
		
		$data['name'] = 'select';
		$data['items'][] = array('value' => '1', 'label' => 'test1');
		$data['items'][] = array('value' => '2', 'label' => 'test2');
		$data['items'][] = array('value' => '3', 'label' => 'test3');
		$data['items'][] = array('value' => '4', 'label' => 'test4');
		$this->setOutput($data, 'selectData');
		$this->setOutput(array('name' => 'a', 'value' => '1', 'label' => 'test1'), 'textData');
		$this->setOutput('3', 'value');
		$this->setTemplate('widget');
	}

	public function hookAction() {
		Wind::import('APPS:demo.service.DemoTestHookService');
		$bp = new DemoTestHookService();
		$filters[] = array('class' => 'APPS:demo.service.injector.DemoTestHook1', 'args' => array('', $bp));
		$this->resolveActionFilter($filters);
		$this->setOutput($bp, 'service');
		$this->setTemplate('hook');
	}
}