<?php
require_once ('config.php');
require_once ('../fts/utils.php');
require_once ("lib/StorageClient/StorageClient.class.php");
require_once ("lib/Auth/Auth.class.php");
require_once ("lib/SimpleAuth/SimpleAuth.class.php");
require_once ("/usr/share/php/Smarty/Smarty.class.php");

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

$auth = new SimpleAuth($config);
$auth->login();
$storageProviders = getConfig($config, 'storage_providers', TRUE);
$spNumber = isset($_SESSION['storageProvider']) ? $_SESSION['storageProvider'] : 0;

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	if ($action == "setSP") {
		$_SESSION['storageProvider'] = $_REQUEST['sp'];
		$spNumber = $_SESSION['storageProvider'];
	} else {
		try {
			$sc = new StorageClient($storageProviders[$spNumber]);
			$parameters = array();
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$parameters = array_merge($parameters, $_POST);
			}
			if ($_SERVER['REQUEST_METHOD'] === 'GET') {
				$parameters = array_merge($parameters, $_GET);
			}
			echo $sc->call($action, $parameters, $_SERVER['REQUEST_METHOD']);
			exit(0);
		}
		catch(Exception $e) {
			echo $e->getMessage();
			exit(1);
		}
	}
}
        $smarty = new Smarty();
        $smarty->template_dir = 'tpl';
        $smarty->compile_dir = 'data/tpl_c';
        // $smarty->plugins_dir[] = 'smarty_plugins';
	$smarty->assign('storageProviders', array_map(function ($item) { 
		return $item['displayName']; 
	}, $storageProviders));
	$smarty->assign('spNumber', $spNumber);
	$smarty->display('index.tpl');

?>
