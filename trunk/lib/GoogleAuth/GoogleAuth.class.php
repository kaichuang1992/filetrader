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
		if (!isset ($_GET['openid_mode'])) {
				if (isset ($_POST['openid_identifier'])) {
					$id = $_POST['openid_identifier'];

					$openid = new LightOpenID();
					$openid->identity = $id;

					$openid->required = array('namePerson/first', 'namePerson/last');
					header('Location: ' . $openid->authUrl());
				}

				$smarty = new Smarty();
				$smarty->template_dir = 'tpl';
				$smarty->compile_dir = 'tpl_c';
				$smarty->assign('content', $smarty->fetch('GoogleAuth.tpl'));
				$smarty->display('index.tpl');
				die();

			}elseif ($_GET['openid_mode'] == 'cancel') {
				die('User has canceled authentication!');
			} else {
				$openid = new LightOpenID();
				if ($openid->validate()) {
					$_SESSION['userId'] = $openid->identity;
					$attributes = $openid->getAttributes();
					$_SESSION['userAttr'] = $attributes;

					if (isset ($attributes['namePerson/first']) && isset ($attributes['namePerson/last']) && !empty ($attributes['namePerson/first']) && !empty ($attributes['namePerson/last'])) {
							$_SESSION['userDisplayName'] = $attributes['namePerson/first'] . ' ' . $attributes['namePerson/last'];
						} else {
							throw new Exception("no first and last name available from IDP");
						}
				}
			}
	}
?>
