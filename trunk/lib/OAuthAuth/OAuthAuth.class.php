<?php

/*  
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2010 François Kooman <fkooman@tuxed.net>
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

class OAuthAuth extends Auth {

	function login() {
		if ($this->isLoggedIn())
			return;

		/* if no attempt to use OAuth stop */
		if (!isset ($_GET['oauth_signature']))
			return;

		/* Idea taken from: 
		 * http://developer.yahoo.com/blogs/ydn/posts/2010/04/a_twolegged_oauth_serverclient_example/
		 */
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1;

		$method = $_SERVER['REQUEST_METHOD']; // POST or GET
		/* we show determine whether http or https should be used, depends party on
		   configuration setting and current request mode */
		$uri = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$sig = $_GET['oauth_signature'];
		$key = $_GET['oauth_consumer_key'];

		/* check if key exists */
		$consumers = getConfig($this->config, 'oauth_consumers', TRUE);
		if (!array_key_exists($key, $consumers))
			throw new Exception("OAuth consumer not registered");
		$consumer = new OAuthConsumer($key, $consumers[$key]);

		$req = new OAuthRequest($method, $uri);
		//token is null because we're doing 2-leg
		$valid = $sig_method->check_signature($req, $consumer, NULL, $sig);
		if (!$valid)
			throw new Exception('invalid OAuth signature');

		/* userId should be actual user the oAuth client is acting on behalf of, 
		   or do we want a special "super account"? */
		$_SESSION['userId'] = "OAuth_$key";
		$_SESSION['userAttr'] = array ();
		$_SESSION['userDisplayName'] = 'OAuth Consumer';
		return;
	}
}
?>
