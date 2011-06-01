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

class StorageClient {

	private $config;
	private $endpoint;
	private $oauth;
	private $decode;

	function __construct($config) {
		if (!is_array($config)) {
			throw new Exception("config parameter should be array");
		}
		$this->config = $config;
		$this->decode = FALSE;
		/* FIXME: this should be user configurable at some point */
		$this->endpoint = getConfig($config, 'api_endpoint', TRUE);
		$this->oauth = new OAuth(getConfig($config, 'consumerKey', TRUE),
				getConfig($config, 'consumerSecret', TRUE),
				OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
	}

	/**
	 * Whether or not to decode the response from the server (JSON to array)
	 * 
	 * @param boolean $decode FALSE is not decode, TRUE is decode
	 */
	function performDecode($decode = FALSE) {
		$this->decode = $decode;
	}

	function getServerInfo() {
		$endpoint = $this->endpoint . "/?action=serverInfo";
		$params = array();
		$this->oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_GET);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function createDirectory($userName = NULL, $dirName = NULL) {
		$endpoint = $this->endpoint . "/?action=createDirectory";
		$params = array('userName' => $userName, 'dirName' => $dirName);
		$oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_POST);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function getDownloadFileLocation($userName = NULL, $fileName = NULL) {
		$endpoint = $this->endpoint . "/?action=getDownloadToken";
		$params = array('userName' => $userName, 'fileName' => $fileName);
		$oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_POST);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function getUploadFileLocation($userName = NULL, $fileName = NULL,
			$fileSize = 0) {
		$endpoint = $this->endpoint . "/?action=getUploadToken";
		$params = array('userName' => $userName, 'fileName' => $fileName,
				'fileSize' => $fileSize);
		$oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_POST);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function getFileList($userName = NULL) {
		$endpoint = $this->endpoint . "/?action=getFileList";
		$params = array('userName' => $userName);
		$oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_GET);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function createDirectory($userName = NULL, $dirName = NULL) {
		$endpoint = $this->endpoint . "/?action=getFileList";
		$params = array('userName' => $userName, 'dirName' => $dirName);
		$oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_POST);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}

	function pingServer() {
		$endpoint = $this->endpoint;
		$response = file_get_contents($endpoint);
		return ($this->decode) ? json_decode($response) : $response;
	}
}
?>
