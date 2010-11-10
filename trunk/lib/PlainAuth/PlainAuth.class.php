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

	class PlainAuth extends Auth {
	
		function login() {
			if($this->isLoggedIn())
				return;

			if(isset($_POST['username']) && isset($_POST['password'])) {
				$user = $_POST['username'];
				$pass = $_POST['password'];

                                switch($user.":".$pass) {
                                        case "admin:test":
                                                $_SESSION['userAttr'] = array();
                                                $_SESSION['userId'] = 'admin';
                                                $_SESSION['userDisplayName'] = 'Administrator';
                                                return;
                                        case "john:johndoe":
                                                $_SESSION['userAttr'] = array();
                                                $_SESSION['userId'] = 'john';
                                                $_SESSION['userDisplayName'] = 'John Doe';
                                                return;
                                }
			} else {
	                        $smarty = new Smarty();
                                $smarty->template_dir = 'tpl';
                                $smarty->compile_dir = 'tpl_c';
				$smarty->assign('content', $smarty->fetch('plainauth.tpl'));
                                $smarty->display('index.tpl');
				die();
			}
		}

		function getUserGroups() {
			if(!$this->isLoggedIn())
				return array();
			switch($_SESSION['userId']) {
				case "admin":
					$groups = array( 
						'12345' => 'Human Resources',
						'12346' => 'Finance',
						'12347' => 'IT Staff',
					);
					break;
				case "john":
                                        $groups = array(
                                                '12345' => 'Human Resources',
                                                '12346' => 'Finance',
                                                '12348' => 'Management',
                                        );
					break;
			}
			return $groups;
		}
	}
?>
