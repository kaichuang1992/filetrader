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

require_once ('ext/lightopenid/openid.php');

class GoogleAuth extends Auth {
	function login() {
		if ($this->isLoggedIn()) {
			return;
		}

		try {
		    if(!isset($_GET['openid_mode'])) {
		        $openid = new LightOpenID;
#			$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
#			$openid->returnUrl = $openid->realm . $_SERVER['REQUEST_URI'];
                        $openid->identity = 'https://www.google.com/accounts/o8/id';
		        header('Location: ' . $openid->authUrl());
		    } elseif($_GET['openid_mode'] == 'cancel') {
		        echo 'User has canceled authentication!';
		    } else {
		        $openid = new LightOpenID;
			if($openid->validate()) {
				$_SESSION['userId'] = $openid->identity;
				$_SESSION['userAttr'] = array();
				$_SESSION['userDisplayName'] = 'Google User';
			}else {
				throw new Exception("login failed");
			}
		    }
		} catch(ErrorException $e) {
		    echo $e->getMessage();
		}
	}
}
?>
