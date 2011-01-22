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

class Files {

	var $config;
	var $storage;
	var $dbName;
	var $auth;
	var $smarty;

	function __construct($config, $storage, $dbName, $auth, $smarty) {
		$this->config = $config;
		$this->storage = $storage;
		$this->dbName = $dbName;
		$this->auth = $auth;
		$this->smarty = $smarty;
	}

	function downloadFile() {
		set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($this->config, 'pear_path', TRUE));
		require_once ('HTTP/Download.php');
		$id = getRequest("id", TRUE);
		$token = getRequest("token", FALSE, 0);

		$info = $this->storage->readEntry($this->dbName, $id);
		$file = $info['fileName'];

		/* FIXME: memberOfGroups and token only if said support is enabled! */
		if ($info['fileOwner'] === $this->auth->getUserId() || $this->auth->memberOfGroups($info['shareGroups']) || array_key_exists($token, $info['downloadTokens'])) {
			/* Access */
			$ownerDir = base64_encode($info['fileOwner']);
			$filePath = getConfig($this->config, 'fileStorageDir', TRUE) . "/$ownerDir/$file";

			if (!file_exists($filePath))
				throw new Exception("file does not exist on file system");

			logHandler("User '" . $this->auth->getUserID() . "' is downloading file '" . $file . "'");

			$dl = new HTTP_Download();
			$dl->setFile($filePath);
			$dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $file);
			$dl->guessContentType();
			$dl->send();
			exit (0);
		} else {
			throw new Exception("Access denied");
		}
	}

	function groupShare() {
		$id = getRequest("id", TRUE);
		$info = $this->storage->readEntry($this->dbName, $id);
		if ($info['fileOwner'] !== $this->auth->getUserId())
			throw new Exception("access denied");

		$this->smarty->assign('sharegroups', $info['shareGroups']);
		$this->smarty->assign('groups', $this->auth->getUserGroups());
		$this->smarty->assign('id', $id);
		$content = $this->smarty->fetch('GroupShare.tpl');
	}

	function emailShare() {
		$id = getRequest("id", TRUE);
		$info = $this->storage->readEntry($this->dbName, $id);
		if ($info['fileOwner'] !== $this->auth->getUserId())
			throw new Exception("access denied");

		$this->smarty->assign('tokens', $info['downloadTokens']);
		$this->smarty->assign('id', $id);
		$this->smarty->assign('fileName', $info['fileName']);
		$content = $this->smarty->fetch('EmailShare.tpl');
	}

	function deleteFile() {
		$ids = getRequest("id", FALSE, array ());
		if (!is_array($ids))
			throw new Exception("deleteFile should receive array of files to delete");

		foreach ($ids as $id) {
			$info = $this->storage->readEntry($this->dbName, $id);
			if ($info['fileOwner'] !== $this->auth->getUserId())
				throw new Exception("access denied");

			logHandler("User '" . $this->auth->getUserID() . "' is deleting file '" . $info['fileName'] . "'");

			$this->storage->deleteEntry($this->dbName, $id, $info['_rev']);

			$file = $info['fileName'];
			$ownerDir = base64_encode($info['fileOwner']);
			$filePath = getConfig($this->config, 'fileStorageDir', TRUE) . "/$ownerDir/$file";

			/* delete from file system */
			unlink($filePath);
		}
		header("Location: index.php?action=myFiles");
	}

	function deleteToken() {
		if (!getConfig($this->config, 'email_share', FALSE, FALSE))
			throw new Exception("email share is not enabled");

		$id = getRequest("id", TRUE);
		$tokenIds = getRequest("token", FALSE, array ());
		if (!is_array($tokenIds))
			throw new Exception("deleteToken should receive array of tokens to delete");

		$info = $this->storage->readEntry($this->dbName, $id);

		if ($info['fileOwner'] !== $this->auth->getUserId())
			throw new Exception("access denied");

		foreach ($tokenIds as $tokenId) {
			logHandler("User '" . $this->auth->getUserID() . "' is deleting token for '" . $info['downloadTokens'][$tokenId] . "' belonging to file '" . $info['fileName'] . "'");
			unset ($info['downloadTokens'][$tokenId]);
		}
		$this->storage->updateEntry($this->dbName, $id, $info);
		header("Location: index.php?action=emailShare&id=$id");
	}

	function updateGroupShare() {
		if (!getConfig($this->config, 'group_share', FALSE, FALSE))
			throw new Exception("group share is not enabled");

		$id = getRequest("id", TRUE);

		$info = $this->storage->readEntry($this->dbName, $id);

		if ($info['fileOwner'] !== $this->auth->getUserId())
			throw new Exception("access denied");

		$groupId = getRequest('groupid', TRUE);
		$checked = getRequest('checked', TRUE);

		if ($checked === 'true') {
			/* add to list */
			if (!in_array($groupId, $info['shareGroups'])) {
				array_push($info['shareGroups'], $groupId);
			}
		} else {
			if (in_array($groupId, $info['shareGroups'])) {
				$k = array_search($groupId, $info['shareGroups']);
				unset ($info['shareGroups'][$k]);
			}
		}
		$this->storage->updateEntry($this->dbName, $id, $info);
		header("Location: index.php?action=groupShare&id=$id");
	}

	function updateEmailShare() {
		if (!getConfig($this->config, 'email_share', FALSE, FALSE))
			throw new Exception("sharing through email not enabled");

		$id = getRequest("id", TRUE);
		$info = $this->storage->readEntry($this->dbName, $id);
		if ($info['fileOwner'] !== $this->auth->getUserId())
			throw new Exception("access denied");
		$address = getRequest('address', TRUE);
		$address = filter_var($address, FILTER_VALIDATE_EMAIL);
		if ($address === FALSE)
			throw new Exception("invalid email address specified");

		/* add token */
		$token = generateToken();

		/* add token to data store */
		$info['downloadTokens'][$token] = $address;
		$this->storage->updateEntry($this->dbName, $id, $info);

		$url = getProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?action=downloadFile&id=$id&token=$token";

		$this->smarty->assign('sender', $this->auth->getUserDisplayName());
		$this->smarty->assign('fileName', $info['fileName']);
		$this->smarty->assign('url', $url);
		$content = $this->smarty->fetch('EmailInvite.tpl');
		$message = wordwrap($content, 70);

		/* send email */
		$subject = '[FileTrader] A file has been shared with you!';
		$from = getConfig($this->config, 'email_share_sender', FALSE, 'FileTrader <filetrader@' . $_SERVER['HTTP_HOST'] . '>');
		$headers = "From: $from\r\n" .
		"Reply-To: $from\r\n" .
		"X-Mailer: PHP/" . phpversion();
		$status = mail($address, $subject, $message, $headers);

		if ($status !== TRUE)
			logHandler("Sending mail to $address failed!");
		else
			logHandler("User '" . $this->auth->getUserID() . "' is sharing file '" . $info['fileName'] . "' with '" . $address . "'");

		header("Location: index.php?action=emailShare&id=$id");
	}

	function myFiles() {
		$files = $this->storage->listEntries($this->dbName);
		foreach ($files as $k => $v) {
			if ($v['fileOwner'] !== $this->auth->getUserId()) {
				unset ($files[$k]);
			} else {
				$files[$k]['fileSize'] = bytesToHuman($v['fileSize']);
			}
		}
		$this->smarty->assign('files', $files);
		$this->smarty->assign('type', $action);
		$this->smarty->assign('email_share', getConfig($this->config, 'email_share', FALSE, FALSE));
		$this->smarty->assign('group_share', getConfig($this->config, 'group_share', FALSE, FALSE));
		$content = $this->smarty->fetch('FileList.tpl');
	}

	function groupFiles() {
		if (!getConfig($this->config, 'group_share', FALSE, FALSE))
			throw new Exception("group share is not enabled");

		$files = $this->storage->listEntries($this->dbName);
		foreach ($files as $k => $v) {
			if ($v['fileOwner'] === $this->auth->getUserId()) {
				unset ($files[$k]);
			} else {
				if (!$this->auth->memberOfGroups($v['shareGroups'])) {
					unset ($files[$k]);
				} else {
					$files[$k]['fileSize'] = bytesToHuman($v['fileSize']);
				}
			}
		}
		$this->smarty->assign('files', $files);
		$this->smarty->assign('type', $action);
		$content = $this->smarty->fetch('FileList.tpl');
	}

	function logout() {
		$this->auth->logout();
		header("Location: " . $_SERVER['SCRIPT_NAME']);
	}

	function handleUpload() {
		// FIXME: this seems to be WAY too crazy
		// Remove HTML4 support completely, only support HTML5 file upload (and maybe Flash/Silverlight?!)

		// HTTP headers for no cache etc
		header('Content-type: text/plain; charset=UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Settings
		$ownerDir = base64_encode($this->auth->getUserId());
		$targetDir = getConfig($this->config, 'fileStorageDir', TRUE) . "/$ownerDir";

		// FIXME: are these variables really needed?
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 60 * 60; // Temp file age in seconds

		// 5 minutes execution time
		@ set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Get parameters
		$chunk = getRequest('chunk', FALSE, 0);
		$chunks = getRequest('chunks', FALSE, 0);
		$fN = getRequest('name', FALSE, '');

		// Clean the fileName for security reasons
		$fileName = filter_var($fN, FILTER_SANITIZE_SPECIAL_CHARS);
		if ($fileName === FALSE)
			throw new Exception("Unable to sanitize filename '" . $fN . "' uploaded by user '" . $this->auth->getUserId() . "'");

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($targetDir))
			@ mkdir($targetDir);

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;
				// Remove temp files if they are older than the max age
				if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
					@ unlink($filePath);
			}
			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

		// Look for the content type header
		if (isset ($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset ($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset ($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

					fclose($out);
					@ unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		/* only add entry to the database after receiving the last block */
		if ($chunk == $chunks -1 || $chunks === 0) {
			$metaData = analyzeFile($targetDir . DIRECTORY_SEPARATOR . $fileName);
			$metaData['fileOwner'] = $this->auth->getUserId();
			$this->storage->createEntry($this->dbName, $metaData);
			logHandler("User '" . $this->auth->getUserID() . "' uploaded file '" . $metaData['fileName'] . "'");
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
}
?>
