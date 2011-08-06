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

	$params = $_REQUEST;
	$endpoint = $params['proxy_to'];
	$key = $params['proxy_consumer_key'];
	$secret = $params['proxy_consumer_secret'];
	$action = $params['action'];
	unset($params['proxy_to']);
	unset($params['proxy_consumer_key']);
	unset($params['proxy_consumer_secret']);
	unset($params['action']);

	require_once('lib/StorageClient/StorageClient.class.php');

        $sc = new StorageClient(array('apiUrl' => $endpoint, 'consumerKey' => $key, 'consumerSecret' => $secret ));
        echo $sc->call($action, $params, $_SERVER['REQUEST_METHOD']);
        exit(0);
} catch (Exception $e) {
}
?>
