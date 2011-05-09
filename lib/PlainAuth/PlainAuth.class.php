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

class PlainAuth extends Auth {
	var $userConfig;

	function __construct($config) {
		parent :: __construct($config);
		$this->userConfig = getConfig($this->config, 'plain_users', TRUE);
	}

	function login() {
		if ($this->isLoggedIn())
			return;

		if (isset ($_POST['plainProceed'])) {
			$userId = getRequest('userId', TRUE);
			$userPass = getRequest('userPass', TRUE);

			if(!empty($userId) && !empty($userPass) && array_key_exists($userId, $this->userConfig) && $this->userConfig[$userId]['pass'] === $userPass) {
				$_SESSION['userId'] = $userId;
				$_SESSION['userAttr'] = array();
				$_SESSION['userDisplayName'] = $this->userConfig[$userId]['dn'];
				return;
			} else {
				throw new Exception("invalid credentials");	
			}
		}

	        require_once ("ext/smarty/libs/Smarty.class.php");
		$smarty = new Smarty();
		$smarty->template_dir = 'tpl';
		$smarty->compile_dir = 'tpl_c';
		$output = $smarty->fetch('PlainAuth.tpl');
		$smarty->assign('error', FALSE);
		$smarty->assign('container', $output);
		$smarty->assign('action', NULL);
		$smarty->display('Page.tpl');
		exit (0);
	}
}
?>
