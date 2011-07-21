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

class OAuthProv {

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
	$request = OAuthRequest::from_request();
	$consumer_key = $request->get_parameter('oauth_consumer_key');
	$signature_method = $request->get_parameter('oauth_signature_method');
	$signature = $request->get_parameter('oauth_signature');

	if($signature_method === "HMAC-SHA1") {
		$sm = new OAuthSignatureMethod_HMAC_SHA1();
                $stmt = $this->db->prepare('SELECT consumerSecret FROM storageConsumers WHERE consumerKey = :key');
                $stmt->bindParam(':key', $consumer_key);
                $stmt->execute();
                $row = $stmt->fetch();
                if($row === FALSE || empty($row)) {
			throw new Exception("consumer not found");
                }
                $consumer_secret = $row['consumerSecret'];
		$valid = $sm->check_signature($request, new OAuthConsumer($consumer_key, $consumer_secret), NULL, $signature);
	} else if($signature_method === "RSA-SHA1") {
	        $sm = new MyOAuthSignatureMethod_RSA_SHA1($this->db);
	        $valid = $sm->check_signature($request, NULL, NULL, $signature);
	} else {
		throw new Exception("invalid signature method");
	}

	if(!$valid) {
		throw new Exception("invalid signature");
	} else {
		/* SURFconext (contains groupContext) */
		$instance_id = $request->get_parameter('opensocial_instance_id');
		/* iGoogle and other OpenSocial/Shindig portals/containers */
		$owner_id = $request->get_parameter('opensocial_owner_id');

		if($instance_id !== NULL) {
			$this->consumerKey = $consumer_key . '_' . $instance_id;
		} else if($owner_id !== NULL) {
                        $this->consumerKey = $consumer_key . '_' . $owner_id;
		} else {
                        $this->consumerKey = $consumer_key;
		}
	}
    }

    function getConsumerIdentifier() {
        return $this->consumerKey;
    }

}

class MyOAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod_RSA_SHA1 {
	private $db;

	function __construct($db) {
	        if ($db == NULL) {
	            throw new Exception("no database provided");
	        }
	        $this->db = $db;
	}

	public function fetch_private_cert(&$request) {
		return NULL;
	}

	public function fetch_public_cert(&$request) {
	        $consumer_key = $request->get_parameter('oauth_consumer_key');
		$stmt = $this->db->prepare('SELECT consumerSecret FROM storageConsumers WHERE consumerKey = :key');
                $stmt->bindParam(':key', $consumer_key);
                $stmt->execute();
                $row = $stmt->fetch();
                if($row === FALSE || empty($row)) {
                        throw new Exception("consumer not found");
                }
                return $row['consumerSecret'];
	}
}

?>
