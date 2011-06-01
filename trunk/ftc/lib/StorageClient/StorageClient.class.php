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

	function __construct($config, $sp_name) {
		if (!is_array($config)) {
			throw new Exception("config parameter should be array");
		}
		$this->config = $config;
		$this->decode = FALSE;

		$storage = getConfig($config, 'storage_providers', TRUE);
		$storage = $storage[$sp_name];

		$this->endpoint = $storage['apiEndPoint'];
		$this->oauth = new OAuth($storage['consumerKey'],
				$storage['consumerSecret'], OAUTH_SIG_METHOD_HMACSHA1,
				OAUTH_AUTH_TYPE_AUTHORIZATION);
	}

	/**
	 * Whether or not to decode the response from the server (JSON to array)
	 * 
	 * @param boolean $decode FALSE is not decode, TRUE is decode
	 */
	function performDecode($decode = FALSE) {
		$this->decode = $decode;
	}

	/**
	 * Perform the call to the storage server
	 * Enter description here ...
	 * @param string $action action to call
	 * @param array $parameters call parameters
	 * @param string $method (POST, GET)
	 */
	function call($action = "pingServer", $parameters = array(),
			$method = "GET") {
		if ($method == "GET") {
			$method = OAUTH_HTTP_METHOD_GET;
		} elseif ($method == "POST") {
			$method = OAUTH_HTTP_METHOD_POST;
		} else {
			throw new Exception("invalid method, should be either GET or POST");
		}
		$endpoint = "$this->endpoint/?action=$action";
		$this->oauth->fetch($endpoint, $parameters, $method);
		$response = $this->oauth->getLastResponse();
		return ($this->decode) ? json_decode($response) : $response;
	}
}
?>
