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

require_once('config.php');
require_once('utils.php');

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

date_default_timezone_set(
		getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

try {
	if (getConfig($config, 'ssl_only', FALSE, FALSE)) {
		if (getProtocol() != "https") {
			throw new Exception("only available through secure connection");
		}
	}

	/* FIXME: use better URLparser with something like htaccess */
	if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
		$action = 'pingServer';
	} else {
		$action = $_REQUEST['action'];
	}

	$validActions = array('pingServer', 'serverInfo', 'getDirList',
			'downloadFile', 'uploadFile', 'getUploadToken', 'getDownloadToken',
			'deleteDirectory', 'deleteFile', 'setDescription', 'getDescription', 'createDirectory');
	if (!in_array($action, $validActions, TRUE)) {
		throw new Exception("unregistered action called");
	}

	/* some actions are allowed without authentication */
	$noAuthActions = array('pingServer', 'downloadFile', 'uploadFile');
	if (!in_array($action, $noAuthActions, TRUE)) {
		require_once("lib/MyOAuthProvider/MyOAuthProvider.class.php");
		$auth = new MyOAuthProvider($config);
		$auth->authenticate();
	}

	/* prepare config variables for location to files and db */
	$config['fts_data'] = realpath(getConfig($config, 'fts_data', TRUE));
	if ($config['fts_data'] === FALSE) {
		throw new Exception("fts_data diretory does not exist");
	}
	$config['fts_data_files'] = $config['fts_data'] . DIRECTORY_SEPARATOR
			. "files";
	/* create the files directory if it does not exist yet */
	if(!file_exists($config['fts_data_files'])) {
		if (@mkdir($config['fts_data_files'], 0775) === FALSE) {
			throw new Exception("unable to create fts_data_files directory");
		}
	}
	$config['fts_data_db'] = $config['fts_data'] . DIRECTORY_SEPARATOR
			. "fts.sqlite";

	require_once("lib/Files/Files.class.php");
	$f = new Files($config);
	$content = $f->$action();
	$content["ok"] = TRUE;
	echo json_encode($content);
} catch (OAuthException $e) {
	echo json_encode(
			array("ok" => FALSE, "errorMessage" => $e->getMessage(),
					"errorCode" => $e->getCode()));
} catch (Exception $e) {
	if ($e->getCode() !== 0) {
	        /* This is for non OAuthExceptions that get throw in methods not protected
	           by OAuth, like downloadFile and uploadFile, they set HTTP headers */
		header("HTTP/1.1 " . $e->getCode() . " " . $e->getMessage());
		echo $e->getMessage();
	} else {
		/* This is for non OAuthExceptions that get thrown in methods protected
		   by OAuth, so we want to convey the error, and not return different
		   HTTP header */
		echo json_encode(
				array("ok" => FALSE, "errorMessage" => $e->getMessage()));
	}
}
?>
