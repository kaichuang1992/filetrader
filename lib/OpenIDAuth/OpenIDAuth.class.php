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

	require_once('ext/lightopenid/openid.php');

	class OpenIDAuth extends Auth {
		function login() {
			if($this->isLoggedIn())
				return;
			try {
				if(!isset($_GET['openid_mode'])) {
					if(isset($_POST['openid_identifier'])) {
						$id = $_POST['openid_identifier'];

						if(isset($this->config['openid_whitelist']) && !empty($this->config['openid_whitelist'])) {
							if(!isset($_POST['domain']))
								throw new Exception('domain expected');
							$domain = $_POST['domain'];
							if(array_key_exists($domain, $this->config['openid_whitelist'])) {
								$id = str_replace('@ID@', $id, $domain);
							} else {
								throw new Exception("domain not whitelisted");
							}
						} else {
							// all is allowed...
						}
						$openid = new LightOpenID;
						$openid->identity = $id;
						
						//$openid->required = array('namePerson/first', 'namePerson/last');	// for Google
						$openid->optional = array('namePerson');	// for Wordpress
						header('Location: ' . $openid->authUrl());
					}

	                                $smarty = new Smarty();
	                                $smarty->template_dir = 'tpl';
	                                $smarty->compile_dir = 'tpl_c';
					$domains = $this->config['openid_whitelist'];
					if(!empty($domains))
						$domains = array_merge(array('---' => 'Select an Identity Provider'), $domains);
					$smarty->assign('domains', $domains);
	                                $smarty->assign('content', $smarty->fetch('openidauth.tpl'));
	                                $smarty->display('index.tpl');
	                                die();

				} elseif($_GET['openid_mode'] == 'cancel') {
					die('User has canceled authentication!');
				} else {
					/* FIXME: check whitelist */

					$openid = new LightOpenID;
					if($openid->validate()) {
						$_SESSION['userId'] = $openid->identity;
						$attributes = $openid->getAttributes();
						$_SESSION['userAttr'] = $attributes;

						if(isset($attributes['namePerson']) && !empty($attributes['namePerson'])) {
						 	$dn = $attributes['namePerson'];
						} else if(isset($attributes['namePerson/first']) && isset($attributes['namePerson/last']) && !empty($attributes['namePerson/first']) && !empty($attributes['namePerson/last'])) {
							$dn = $attributes['namePerson/first'] . ' ' . $attributes['namePerson/last'];
						} else {
							$dn = '~Not Available~';
						}
						$_SESSION['userDisplayName'] = $dn;
					}
				}
			} catch(ErrorException $e) {
				echo $e->getMessage();
			}
		}

		function getUserGroups() {
                        if(!$this->isLoggedIn())
				throw new Exception("not logged in");
			return array('1337' => 'All');
		}
	}
?>
