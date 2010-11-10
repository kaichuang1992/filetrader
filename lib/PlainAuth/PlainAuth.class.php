<?php
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
