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

	abstract class Auth {
		var $config;

                function __construct($config) {
			$this->config = $config;
                        session_start();
		}

                function isLoggedIn() {
                        return isset($_SESSION['userId']);
                }

                function getUserInfo() {
			if($this->isLoggedIn())
	                        return $_SESSION['userAttr'];
			else
				throw new Exception("not logged in");
                }

                function getUserId() {
			if($this->isLoggedIn())
	                        return $_SESSION['userId'];
			else
				throw new Exception("not logged in");
                }

		function getUserDisplayName() {
                        if($this->isLoggedIn())
                                return $_SESSION['userDisplayName'];
                        else
                                throw new Exception("not logged in");
		}

		/**
		 * Function must set session variables userId, userAttr, userDisplayName
		 */
		abstract function login();
	
		function getUserGroups() {
			return array();
		}

		/**
		 * Determines if user is member of one or more of the specified
		 * groups.
		 */
		function memberOfGroups($groups) {
			if(!is_array($groups))
				throw new Exception("groups should be specified as array");
			if(empty($groups))
				return FALSE;
			$userGroups = $this->getUserGroups();
			$intersect = array_intersect(array_keys($userGroups), $groups);
			return !empty($intersect);
		}
	}
?>