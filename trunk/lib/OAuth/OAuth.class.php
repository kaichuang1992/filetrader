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

require_once ('ext/oauth/OAuth.php');

class OAuth {

	var $config;
	var $userId;

	function __construct($config) {
		if (!is_array($config))
			throw new Exception("config parameter should be array");
		$this->config = $config;
		$this->isValidRequest = FALSE;
	}

	function isLoggedIn() {
		return !empty($this->userId);
	}

	function getUserId() {
		if ($this->isLoggedIn())
			return $this->userId;
		else
			throw new Exception("not logged in");
	}

	function getUserDisplayName() {
		if ($this->isLoggedIn())
			return "OAuth Consumer";
		else
			throw new Exception("not logged in");
	}

	function login() {
		/* See: http://developer.yahoo.com/blogs/ydn/posts/2010/04/a_twolegged_oauth_serverclient_example/ */

                $sig = getRequest('oauth_signature', TRUE);
                $key = getRequest('oauth_consumer_key', TRUE);
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1;
		$req_method = $_SERVER['REQUEST_METHOD'];
		$url = getProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		/* check if consumer key is in list of consumers */
		$consumers = getConfig($this->config, 'oauth_consumers', TRUE);
		if (!array_key_exists($key, $consumers))
			throw new Exception("oauth consumer key not registered");
		$consumer = new OAuthConsumer($key, $consumers[$key]);

		$req = new OAuthRequest($req_method, $url);
		$valid = $sig_method->check_signature($req, $consumer, NULL, $sig);
		if (!$valid)
			throw new Exception('invalid oauth signature');

                $this->userId = getRequest('userId', TRUE);
	}
}
?>
