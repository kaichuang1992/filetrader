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

    private $db;
    private $consumerKey;

    function __construct($db) {
        if ($db == NULL) {
            throw new Exception("no database provided");
        }
        $this->db = $db;
        $this->consumerKey = NULL;
    }

    function authenticate() {
        $db = $this->db;

        $provider = new OAuthProvider();
        $provider->is2LeggedEndpoint(TRUE);

        $provider->consumerHandler(
                function ($provider) use ($db) {
	        	$stmt = $db->prepare('SELECT consumerSecret FROM storageConsumers WHERE consumerKey = :key');
			$stmt->bindParam(':key', $provider->consumer_key);
		        $stmt->execute();
		        $row = $stmt->fetch();
			if($row === FALSE || empty($row)) {
                        	return OAUTH_CONSUMER_KEY_UNKNOWN;
			}
                        $provider->consumer_secret = $row['consumerSecret'];
                    	return OAUTH_OK;
                });

        $provider->timestampNonceHandler(
                function ($provider) {
                    if ($provider->nonce == "bad") {
                        return OAUTH_BAD_NONCE;
                    } else if ($provider->timestamp == "0") {
                        return OAUTH_BAD_TIMESTAMP;
                    }
                    return OAUTH_OK;
                });
        $provider->checkOAuthRequest();
        $this->consumerKey = $provider->consumer_key;
    }

    function getConsumerIdentifier() {
        return $this->consumerKey;
    }

}

?>
