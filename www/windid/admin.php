<?php
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
require '../../src/wekit.php';

Wekit::run('windidadmin', array('router' => array()));
?>