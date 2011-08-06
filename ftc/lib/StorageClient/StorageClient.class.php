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

require_once("OAuth.php");

class StorageClient {
    private $consumer;

    function __construct($storageProvider) {
        $this->decode = FALSE;
	$this->endpoint = $storageProvider['apiUrl'];
 	$this->consumer = new OAuthConsumer($storageProvider['consumerKey'], $storageProvider['consumerSecret']);
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
    function call($action = "pingServer", $parameters = array(), $method = "GET") {
        $endpoint = "$this->endpoint/?action=$action";
        $request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, $method, $endpoint, $parameters);
	$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
	$request->sign_request($sig_method, $this->consumer, NULL);

	switch($method) {
	case "GET": 
		$response = file_get_contents($request->to_url());
		break;

	case "POST": 
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $request->to_postdata(),
		    )           
		);
		$context = stream_context_create($opts);
		$response = file_get_contents($endpoint, false, $context);
		break;

	default: 
            throw new Exception("invalid method, should be either GET or POST");
	}

        return ($this->decode) ? json_decode($response) : $response;
    }

}

?>
