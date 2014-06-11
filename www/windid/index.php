<?php
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
require '../../src/wekit.php';
$components = array('router' => array());
Wekit::run('windid', $components);
?>