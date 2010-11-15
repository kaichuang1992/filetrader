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

		/* 
		 * record returnUrl in session variable as the OpenID authentication
		 * seems to mangle the returnUrl. This is a bit ugly, have to dive in
		 * OpenID specs to see if this can be fixed in a nice way. SAML
		 * has no problem with this...
		 */
		if (isset ($_SERVER['REQUEST_URI']) && !empty ($_SERVER['REQUEST_URI']) && !isset ($_SESSION['returnUrl'])) {
			$_SESSION['returnUrl'] = $_SERVER['REQUEST_URI'];
		}

		try {
			if (!isset ($_GET['openid_mode'])) {
				if (isset ($_POST['openid_identifier'])) {
					$id = $_POST['openid_identifier'];
					$openid = new LightOpenID();
					$openid->identity = $id;
					$openid->required = array (
						'namePerson/first',
						'namePerson/last'
					);
					header('Location: ' . $openid->authUrl());
				}
			}
			elseif ($_GET['openid_mode'] == 'cancel') {
				throw new Exception('User has canceled authentication.');
			} else {
				$openid = new LightOpenID();
				if ($openid->validate()) {
					$_SESSION['userId'] = $openid->identity;
					$attributes = $openid->getAttributes();

					$_SESSION['userAttr'] = $attributes;

					$_SESSION['userDisplayName'] = $attributes['namePerson/first'] . " " . $attributes['namePerson/last'];

					/* Jump to returnUrl if it was set, but not before unsetting it */
					if (isset ($_SESSION['returnUrl']) && !empty ($_SESSION['returnUrl'])) {
						$returnUrl = $_SESSION['returnUrl'];
						unset ($_SESSION['returnUrl']);
						header("Location: " . $returnUrl);
					}
				}
			}
		} catch (Exception $e) {
			$this->error = TRUE;
			$this->errorMessage = $e->getMessage();
		}

		$smarty = new Smarty();
		$smarty->template_dir = 'tpl';
		$smarty->compile_dir = 'tpl_c';
		$smarty->assign('error', $this->error);
		$smarty->assign('errorMessage', $this->errorMessage);
		$smarty->assign('content', $smarty->fetch('GoogleAuth.tpl'));
		$smarty->display('index.tpl');
		exit (0);
	}
}
?>
