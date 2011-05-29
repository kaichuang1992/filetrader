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

$consumer_key = '12345';
$consumer_secret = '54321';

$serviceUrl = 'http://192.168.56.101/fts';
$getInfoUrl = $serviceUrl . "/index.php?action=serverInfo";

try {
	$oauth = new OAuth($consumer_key, $consumer_secret,
			OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
	$oauth->fetch($getTokenUrl, $params, OAUTH_HTTP_METHOD_POST);
} catch (OAuthException $e) {
	die($e->getMessage());
}

$response = $oauth->getLastResponse();
echo "---  RESPONSE ---\n";
echo $response . "\n";
echo "--- /RESPONSE ---\n";
$decodedResponse = json_decode($response);

if ($decodedResponse === NULL)
	throw new Exception("not a json response");
var_dump($decodedResponse);
?>
