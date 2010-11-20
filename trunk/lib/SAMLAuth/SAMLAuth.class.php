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

require_once(getConfig($config, 'saml_path', TRUE) . '/lib/_autoload.php');

class SAMLAuth extends Auth {
	var $saml;

	function __construct($config) {
		parent::__construct($config);
		$this->saml = new SimpleSAML_Auth_Simple(getConfig($this->config, 'saml_sp', TRUE));
	}

	function login() {
		if($this->isLoggedIn())
			return;

			if(isset($_POST['samlProceed'])) {
		
				$this->saml->requireAuth();
				$attr = $this->saml->getAttributes();

       			        $_SESSION['userId'] = $attr[getConfig($this->config, 'saml_uid', TRUE)][0];
				$_SESSION['userAttr'] = $attr;
				$_SESSION['userDisplayName'] = $attr[getConfig($this->config, 'saml_display_name', TRUE)][0];
				return;
			}

                $smarty = new Smarty();
                $smarty->template_dir = 'tpl';
                $smarty->compile_dir = 'tpl_c';
                $smarty->assign('content', $smarty->fetch('SAMLAuth.tpl'));
                $smarty->display('index.tpl');
                exit (0);
	}

	function logout() {
		parent::logout();
		$this->saml->logout($_SERVER['SCRIPT_NAME']);
	}		
}
?>
