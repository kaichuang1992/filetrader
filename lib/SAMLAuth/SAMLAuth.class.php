<?php
require_once($config['saml_path'] . '/lib/_autoload.php');

class SAMLAuth extends Auth {
	var $saml;

	function __construct($config) {
		parent::__construct($config);
		$this->saml = new SimpleSAML_Auth_Simple($this->config['saml_sp']);
	}

	function login() {
		if($this->isLoggedIn())
			return;

		$this->saml->requireAuth();
		$attr = $this->saml->getAttributes();

                $_SESSION['userId'] = $attr[$this->config['saml_uid']][0];
		$_SESSION['userAttr'] = $attr;
		$_SESSION['userDisplayName'] = $attr[$this->config['saml_display_name']][0];
	}

	function logout() {
		parent::logout();
		$this->saml->logout();
	}		
}
?>
