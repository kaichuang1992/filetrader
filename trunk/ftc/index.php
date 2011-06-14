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
require_once ('../fts/utils.php');
require_once ("lib/StorageClient/StorageClient.class.php");
require_once ("lib/StorageProvider/StorageProvider.class.php");
require_once ("/usr/share/php/Smarty/Smarty.class.php");


if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

try {
	if (getConfig($config, 'ssl_only', FALSE, FALSE)) {
		// only allow SSL connections
		if (!isset ($_SERVER['HTTPS']) || empty ($_SERVER['HTTPS'])) {
			throw new Exception("only available through secure connection");
		} else {
			/* support HTTP Strict Transport Security */
			if(getConfig($config, 'ssl_hsts', FALSE, FALSE)) {
				header('Strict-Transport-Security: max-age=3600');
			}
		}
	}

	/* prepare config variables for location to db */
	$config['ftc_data'] = realpath(getConfig($config, 'ftc_data', TRUE));
	if ($config['ftc_data'] === FALSE) {
		throw new Exception("ftc_data diretory does not exist");
	}
	$config['ftc_data_db'] = $config['ftc_data'] . DIRECTORY_SEPARATOR
			. "ftc.sqlite";

	$authType = getConfig($config, 'auth_type', TRUE);
	$groupType = getConfig($config, 'group_type', TRUE);

	require_once ("../lib/Auth/Auth.class.php");
	require_once ("../lib/$authType/$authType.class.php");
        require_once ("../lib/Groups/Groups.class.php");
        require_once ("../lib/$groupType/$groupType.class.php");

	$auth = new $authType ($config);
	$auth->login();

	$groups = new $groupType ($config, $auth);

	/* FIXME: this makes me cry... maybe we should have getUserGroups already
	   prepend the group identifier with a "@" */
	$userGroups = array();
	foreach($groups->getUserGroups() as $gid => $gname) {
		array_push($userGroups, "@" . $gid);
	}
	array_push($userGroups, $auth->getUserId());

	$sp = new StorageProvider($config);
	$storageProviders = $sp->getStorageByOwner($userGroups);
	$activeStorageProvider = isset($_SESSION['storageProvider']) ? $_SESSION['storageProvider'] : -1;
	/* FIXME: make sure user has access to this provider! */

	$action = getRequest('action', FALSE, FALSE);
	if($action) {
		if ($action == "setStorageProvider") {
			$_SESSION['storageProvider'] = getRequest('storageProvider', FALSE, 0);
			$activeStorageProvider = $_SESSION['storageProvider'];
		} else if ($action == "addStorageProvider") {
			$group = getRequest('group', FALSE, '');
			$owner = empty($group) ? $auth->getUserId() : "@" . $group;
			$sp->addStorage(getRequest('displayName', TRUE), getRequest('apiUrl', TRUE), getRequest('consumerKey', TRUE), 
getRequest('consumerSecret', TRUE), $owner);
		} else {
			$sc = new StorageClient($sp->getStorageById($activeStorageProvider));
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
	}

	$smarty = new Smarty();
	$smarty->template_dir = 'tpl';
	$smarty->compile_dir = 'data/tpl_c';
	$sps = array();
	foreach($storageProviders as $s) {
		$sps[$s['id']] = $s['displayName'];
	}
	$smarty->assign('storageProviders', $sps);
	$smarty->assign('groups', $groups);
	$smarty->assign('activeStorageProvider', $activeStorageProvider);
	$smarty->display('index.tpl');
}catch(Exception $e) {
	echo $e->getMessage();
}
?>
