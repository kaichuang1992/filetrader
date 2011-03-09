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

abstract class Auth {
	var $config;

	function __construct($config) {
		if (!is_array($config))
			throw new Exception("config parameter should be array");
		$this->config = $config;
		if (!isset ($_SESSION))
			session_start();
	}

	function isLoggedIn() {
		return (isset ($_SESSION['userId']) && isset ($_SESSION['userAttr']) && isset ($_SESSION['userDisplayName']));
	}

	function getUserId() {
		if ($this->isLoggedIn())
			return $_SESSION['userId'];
		else
			throw new Exception("not logged in");
	}

	function getUserDisplayName() {
		if ($this->isLoggedIn())
			return $_SESSION['userDisplayName'];
		else
			throw new Exception("not logged in");
	}

	/**
	 * Function must set session variables userId, userAttr, userDisplayName
	 */
	abstract function login();

	function logout($url = NULL) {
		if ($this->isLoggedIn()) {
			unset ($_SESSION['userId']);
			unset ($_SESSION['userAttr']);
			unset ($_SESSION['userDisplayName']);
			if ($url !== NULL)
				header("Location: $url");
		} else {
			throw new Exception("not logged in");
		}
	}
}
?>
