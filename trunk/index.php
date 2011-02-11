<?php

/*
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2011 François Kooman <fkooman@tuxed.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once ('config.php');
require_once ('utils.php');

if (!isset ($config) || !is_array($config))
	die("broken or missing configuration file?");

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

require_once ("ext/smarty/libs/Smarty.class.php");

$smarty = new Smarty();
$smarty->template_dir = 'tpl';
$smarty->compile_dir = 'tpl_c';
$smarty->plugins_dir[] = 'smarty_plugins';
$smarty->assign('error', FALSE);

try {
	if (getConfig($config, 'ssl_only', FALSE, FALSE)) {
		// only allow SSL connections
		if (!isset ($_SERVER['HTTPS']) || empty ($_SERVER['HTTPS']))
			throw new Exception("only available through secure connection");
	}

	$authType = getConfig($config, 'auth_type', TRUE);
	$dbName = getConfig($config, 'db_name', TRUE);

	require_once ("lib/Auth/Auth.class.php");
	require_once ("lib/$authType/$authType.class.php");
	require_once ("ext/sag/src/Sag.php");
	require_once ("lib/Files/Files.class.php");

	/*
	if (getConfig($config, 'allow_oauth', FALSE, FALSE)) {
		// try OAuth authentication
		require_once ("lib/OAuth/OAuth.class.php");
		$auth = new OAuth($config);
		$auth->login();
	}
	*/

	if (!isset ($auth) || empty ($auth)) {
		$auth = new $authType ($config);
	}

	if(!$auth->isLoggedIn()) {
		$auth->login();
	}

	$action = getRequest('action', FALSE, 'myFiles');

        $storage = new Sag();
        $storage->setDatabase($dbName);

	if($action === "logout") {
		$auth->logout($_SERVER["SCRIPT_NAME"]);
		exit(0);
	}

	if (!in_array($action, array (
			'deleteFile',
			'downloadFile',
			'fileInfo',
			'fileUpload',
			'getCacheObject',
			'handleUpload',
			'myFiles',
			'myMedia',
			'groupFiles',
			'rawFileInfo',
			'updateFileInfo',
		), TRUE))
		throw new Exception("unregistered action called");
	$f = new Files($config, $storage, $auth, $smarty);
	$content = $f-> $action ();

} catch (Exception $e) {
	$smarty->assign('error', TRUE);
	$smarty->assign('errorMessage', $e->getMessage());
	$smarty->assign('action', NULL);
	$smarty->assign('authenticated', FALSE);
	$smarty->display('Page.tpl');
	logHandler("ERROR: " . $e->getMessage());
	logHandler("ERROR TRACE: " . $e->getTraceAsString());
	exit (1);
}

$smarty->assign('action', $action);
$smarty->assign('authenticated', $auth->isLoggedIn());
$smarty->assign('userGroups', $auth->getUserGroups());
$smarty->assign('userId', $auth->getUserId());
$smarty->assign('userDisplayName', $auth->getUserDisplayName());
$smarty->assign('container', $content);
$smarty->display('Page.tpl');
?>
