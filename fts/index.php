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

/* Disable Caching */
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if (!isset($config) || !is_array($config)) {
    die("broken or missing configuration file?");
}

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

try {
    if (getConfig($config, 'ssl_only', FALSE, FALSE)) {
        if (getProtocol() != "https://") {
            throw new Exception("only available through secure connection");
        }
    }

    set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($config, "oauth_lib_dir", TRUE));
	
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

    /* prepare config variables for location to files and db */
    $config['fts_data'] = realpath(getConfig($config, 'fts_data', TRUE));
    if ($config['fts_data'] === FALSE || !is_dir($config['fts_data'])) {
        throw new Exception("fts_data directory does not exist");
    }

    $dbh = new PDO(getConfig($config, 'fts_db_dsn', TRUE),
	                   getConfig($config, 'fts_db_user', FALSE, NULL),
	                   getConfig($config, 'fts_db_pass', FALSE, NULL),
	                   getConfig($config, 'fts_db_options', FALSE, array()));

    /* some actions are allowed without authentication */
    $noAuthActions = array('pingServer', 'downloadFile', 'uploadFile');
    if (!in_array($action, $noAuthActions, TRUE)) {
	require_once("lib/OAuthProv/OAuthProv.class.php");
	$auth = new OAuthProv($dbh);

        $auth->authenticate();
        $consumerIdentifier = urlencode($auth->getConsumerIdentifier());

        /* append the OAuth consumer key to the path to keep file storages separate */
        $config['fts_data'] = $config['fts_data'] . DIRECTORY_SEPARATOR . $consumerIdentifier;
        if (!file_exists($config['fts_data'])) {
            if (@mkdir($config['fts_data'], 0775) === FALSE) {
                throw new Exception("unable to create specific fts_data directory for this consumer");
            }
        }
    }

    require_once("lib/Files/Files.class.php");

    $f = new Files($dbh, $config);
    $content = $f->$action();
    $content["ok"] = TRUE;
    echo json_encode($content);
} catch (OAuthException $e) {
    echo json_encode(array("ok" => FALSE, "errorMessage" => $e->getMessage(), "errorCode" => $e->getCode()));
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
        echo json_encode(array("ok" => FALSE, "errorMessage" => $e->getMessage()));
    }
}
?>
