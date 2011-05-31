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

class MyOAuthProvider {

	private $config;

	function __construct($config) {
		if (!is_array($config)) {
			throw new Exception("config parameter should be array");
		}
		$this->config = $config;
	}

	function authenticate() {
		$provider = new OAuthProvider();
		$provider->is2LeggedEndpoint(TRUE);

		$config = $this->config;
		$provider
				->consumerHandler(
						function ($provider) use ($config) {
							/* use the OAuth credentials from the config file */
							if (!array_key_exists($provider->consumer_key,
									$config['oauth_consumers']))
								return OAUTH_CONSUMER_KEY_UNKNOWN;
							$provider->consumer_secret = $config['oauth_consumers'][$provider
									->consumer_key];
							return OAUTH_OK;
						});

		$provider
				->timestampNonceHandler(
						function ($provider) {
							if ($provider->nonce == "bad") {
								return OAUTH_BAD_NONCE;
							} else if ($provider->timestamp == "0") {
								return OAUTH_BAD_TIMESTAMP;
							}
							return OAUTH_OK;
						});
		$provider->checkOAuthRequest();
	}
}

?>
