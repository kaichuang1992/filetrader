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

$consumer_key = 'abcde';
$consumer_secret = 'edcba';

$serviceUrl = 'http://192.168.56.101/ftstorage';
$getTokenUrl = $serviceUrl . "/index.php?action=getUploadToken";
$uploadFileUrl = $serviceUrl . "/index.php?action=uploadFile";

$fileName = "COPYING";

$params = array("fileName" => $fileName, "userName" => "demoUser",
		"fileSize" => filesize($fileName));

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

if (isset($decodedResponse->uploadToken)) {
	$token = $decodedResponse->uploadToken;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $uploadFileUrl . "&token=$token");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_INFILE, fopen($fileName, "rb"));
	curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
	curl_setopt($ch, CURLOPT_PUT, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$uploadResponse = curl_exec($ch);
	curl_close($ch);
	echo "---  RESPONSE ---\n";
	echo $uploadResponse . "\n";
	echo "--- /RESPONSE ---\n";
} else if (isset($decodedResponse->message)) {
	echo "ERROR: $decodedResponse->message\n";
} else {
	echo "UNKNOWN ERROR!\n";
	var_dump($response);
}
?>
