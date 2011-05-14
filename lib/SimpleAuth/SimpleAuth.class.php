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

class SimpleAuth extends Auth {
	var $userConfig;

	function __construct($config) {
		parent :: __construct($config);
		$this->userConfig = getConfig($this->config, 'simple_auth_users', TRUE);
	}

	function login() {
		if ($this->isLoggedIn())
			return;

		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                        $userId = $_SERVER['PHP_AUTH_USER'];
                        $userPass = $_SERVER['PHP_AUTH_PW'];
                        if(!empty($userId) && !empty($userPass) && array_key_exists($userId, $this->userConfig) && $this->userConfig[$userId]['password'] === $userPass) {
                                $_SESSION['userId'] = $userId;
                                $_SESSION['userAttr'] = array();
                                $_SESSION['userDisplayName'] = $this->userConfig[$userId]['display_name'];
                                return;
			}
		}
                header('WWW-Authenticate: Basic realm="Restricted Area"');
                header('HTTP/1.0 401 Unauthorized');
                throw new Exception("authentication required");
	}
}
?>
