<?php
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
