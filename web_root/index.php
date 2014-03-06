<?php
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', true);
	date_default_timezone_set('America/Halifax'); //GMT -4
	
	$rootDir = dirname(dirname(__FILE__));
	set_include_path($rootDir . '/library' . PATH_SEPARATOR . get_include_path());
	
	require_once 'Zend/Controller/Front.php';
	require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
	require_once 'Zend/Config.php';
	require_once 'Zend/Registry.php';

	$config = new Zend_Config(require '../application/config.php');
	
	$params = $config->database->toArray();
	
	$DB = new Zend_Db_Adapter_Pdo_Mysql($params);
	Zend_Registry::set('DB',$DB);
	
	Zend_Controller_Action_HelperBroker::addPath('../application/controllers/helpers');

	Zend_Controller_Front::run('../application/controllers');

?>