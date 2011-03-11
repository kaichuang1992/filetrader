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

require_once('utils.php');
require_once('ext/opensocial-php-client/src/osapi/external/OAuth.php');

$key    = 12345;
$secret = '54321';

$consumer = new OAuthConsumer($key, $secret);
$sig_method = new OAuthSignatureMethod_HMAC_SHA1;

/* Supports web and cli */
$protocol = getProtocol();
if($protocol !== FALSE) {
	$api_endpoint = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/index.php";
} else {
	if(empty($argv[1])) {
		die("specify API endpoint URL\n");
	}
	$api_endpoint = $argv[1];
}

$params = array('useRest' => '1', 
		'action' => 'showFiles', 
		'userId' => 'anonymous');

$req = OAuthRequest::from_consumer_and_token($consumer, null, "GET", $api_endpoint, $params);
$req->sign_request($sig_method, $consumer, null);
$ch = curl_init($req->to_url());
curl_exec($ch);
curl_close($ch);
?>
