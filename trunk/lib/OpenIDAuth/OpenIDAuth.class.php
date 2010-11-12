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

class OpenIDAuth extends Auth {

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
		if (isset ($_REQUEST['PARAM']) && !empty ($_REQUEST['PARAM']) && !isset ($_SESSION['returnUrl'])) {
			$_SESSION['returnUrl'] = $_REQUEST['PARAM'];
		}

		if (!isset ($_GET['openid_mode'])) {
			if (isset ($_POST['openid_identifier'])) {
				$id = $_POST['openid_identifier'];

				if (isset ($this->config['openid_whitelist']) && !empty ($this->config['openid_whitelist'])) {
					if (!isset ($_POST['domain']))
						throw new Exception('domain expected');
					$domain = $_POST['domain'];
					if (array_key_exists($domain, $this->config['openid_whitelist'])) {
						$id = str_replace('@ID@', $id, $domain);
					} else {
						throw new Exception("domain not whitelisted");
					}
				} else {
					// all is allowed...
				}
				$openid = new LightOpenID();
				$openid->identity = $id;

				$openid->optional = array (
					'namePerson'
				);
				header('Location: ' . $openid->authUrl());
			}

			$smarty = new Smarty();
			$smarty->template_dir = 'tpl';
			$smarty->compile_dir = 'tpl_c';
			$domains = $this->config['openid_whitelist'];
			$smarty->assign('domains', $domains);
			$smarty->assign('content', $smarty->fetch('OpenIDAuth.tpl'));
			$smarty->display('index.tpl');
			exit (0);

		}
		elseif ($_GET['openid_mode'] == 'cancel') {
			die('User has canceled authentication!');
		} else {
			$openid = new LightOpenID();
			if ($openid->validate()) {
				$_SESSION['userId'] = $openid->identity;
				$attributes = $openid->getAttributes();
				$_SESSION['userAttr'] = $attributes;

				if (isset ($attributes['namePerson']) && !empty ($attributes['namePerson']))
					$_SESSION['userDisplayName'] = $attributes['namePerson'];
				else
					throw new Exception("not provided nick name");

				/* jump to returnUrl if it was set */
				if (isset ($_SESSION['returnUrl']) && !empty ($_SESSION['returnUrl'])) {
					$returnUrl = $_SESSION['returnUrl'];
					unset ($_SESSION['returnUrl']);
					header("Location: " . $returnUrl);
				}
			}
		}
	}
}
?>
