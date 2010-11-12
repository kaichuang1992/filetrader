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

include_once ('config.php');
include_once ('utils.php');

date_default_timezone_set($config['time_zone']);

# FIXME: 
# system libraries required:
# - Pear HTTP Download
# - Smarty

include_once ("ext/sag/src/Sag.php");
include_once ("lib/CRUDStorage/CRUDStorage.class.php");
include_once ("lib/CouchCRUDStorage/CouchCRUDStorage.class.php");
include_once ("lib/Auth/Auth.class.php");
include_once ("/usr/share/php/Smarty/Smarty.class.php");

$authType = $config['auth_type'];
$dbName = $config['db_name'];

include_once ("lib/$authType/$authType.class.php");

if ($config['allow_opensocial']) {
	/* do something with OpenSocial signature verification */
	$userId = 'OpenSocial';
} else {
	$auth = new $authType ($config);
	$auth->login();
}

$storage = new CouchCRUDStorage();
$files = $storage->listEntries($dbName);

$smarty = new Smarty();
$smarty->template_dir = 'tpl';
$smarty->compile_dir = 'tpl_c';

$smarty->assign('authenticated', $auth->isLoggedIn());

$action = getRequest('action', FALSE, 'index');

switch ($action) {
	case "download" :
		include_once ('/usr/share/pear/HTTP/Download.php');
		$id = getRequest("id", TRUE);
		$token = getRequest("token", FALSE, 0);

		$info = $storage->readEntry($dbName, $id);
		$file = $info['fileName'];

		if ($info['fileOwner'] === $auth->getUserId() || $auth->memberOfGroups($info['shareGroups']) || array_key_exists($token, $info['downloadTokens'])) {
			/* Access */
			$ownerDir = base64_encode($info['fileOwner']);
			$filePath = "data/files/$ownerDir/$file";

			if (!file_exists($filePath))
				throw new Exception("file does not exist on file system");

			logHandler("User '" . $auth->getUserID() . "' is downloading file '" . $file . "'");

			$dl = new HTTP_Download();
			$dl->setFile($filePath);
			$dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $file);
			$dl->guessContentType();
			$dl->send();
			exit (0);
		} else {
			throw new Exception("access denied");
		}
		break;

	case "share" :
		$id = getRequest("id", TRUE);

		/* FIXME: ugly way of passing the id! */
		list ($type, $id) = explode("_", $id);

		$info = $storage->readEntry($dbName, $id);

		if ($info['fileOwner'] !== $auth->getUserId())
			die("access denied");

		$smarty->assign('sharegroups', $info['shareGroups']);
		$smarty->assign('tokens', $info['downloadTokens']);
		$smarty->assign('groups', $auth->getUserGroups());
		$smarty->assign('id', $id);
		$smarty->assign('email_share', $config['email_share']);
		$smarty->display('share.tpl');
		break;

	case "delete" :
		$id = getRequest("id", TRUE);

		/* FIXME: ugly way of passing the id! */
		list ($type, $id) = explode("_", $id);

		$info = $storage->readEntry($dbName, $id);

		if ($info['fileOwner'] !== $auth->getUserId())
			die("access denied");

		logHandler("User '" . $auth->getUserID() . "' is deleting file '" . $info['fileName'] . "'");

		$storage->deleteEntry($dbName, $id, $info['_rev']);

		$file = $info['fileName'];
		$ownerDir = base64_encode($info['fileOwner']);
		$filePath = "data/files/$ownerDir/$file";

		/* delete from file system */
		unlink($filePath);
		break;

	case "updategroups" :
		$id = getRequest("id", TRUE);

		$info = $storage->readEntry($dbName, $id);

		if ($info['fileOwner'] !== $auth->getUserId())
			die("access denied");

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
		$storage->updateEntry($dbName, $id, $info);
		break;

	case "emailshare" :
		if (!$config['email_share'])
			die("sharing through email not enabled");

		$id = getRequest("id", TRUE);
		$info = $storage->readEntry($dbName, $id);

		if ($info['fileOwner'] !== $auth->getUserId())
			die("access denied");

		$address = getRequest('address', TRUE);
		/* FIXME: validate address */

		/* add token */
		$token = generateToken();

		$url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?action=download&id=$id&token=$token";

		$message = "Hello,\n\n" . $auth->getUserDisplayName() . " invites you to download the file '" . $info['fileName'] . "'. You can click on the link below to start the download. You may be asked to login to the service first.\n\nLink: $url";

		$message = wordwrap($message, 70);

		/* send email */
		$status = mail($address, 'A file has been shared with you!', $message);

		if ($status !== TRUE)
			logHandler("Sending mail to $address failed!");
		else
			logHandler("User '" . $auth->getUserID() . "' is sharing file '" . $info['fileName'] . "' with '" . $address . "'");

		/* add token to data store */
		$info['downloadTokens'][$token] = $address;
		$storage->updateEntry($dbName, $id, $info);
		break;

	case "myfiles" :
		foreach ($files as $k => $v) {
			if ($v['fileOwner'] !== $auth->getUserId()) {
				unset ($files[$k]);
			} else {
				$files[$k]['fileSize'] = bytesToHuman($v['fileSize']);
			}
		}
		$smarty->assign('files', $files);
		$smarty->assign('type', $action);
		$smarty->assign('groups', $auth->getUserGroups());
		$smarty->display('files.tpl');
		break;

	case "uploadfiles" :
		$smarty->display('upload.tpl');
		break;

	case "groupfiles" :
		foreach ($files as $k => $v) {
			if ($v['fileOwner'] === $auth->getUserId()) {
				unset ($files[$k]);
			} else {
				if (!$auth->memberOfGroups($v['shareGroups'])) {
					unset ($files[$k]);
				} else {
					$files[$k]['fileSize'] = bytesToHuman($v['fileSize']);
				}
			}
		}
		$smarty->assign('files', $files);
		$smarty->assign('type', $action);
		$smarty->display('files.tpl');
		break;

	case "index" :
		if ($auth->isLoggedIn()) {
			$userId = $auth->getUserId();
			$userDisplayName = $auth->getUserDisplayName();
			$userGroups = $auth->getUserGroups();
		} else {
			$userId = NULL;
			$userDisplayName = NULL;
			$userGroups = NULL;
		}
		$smarty->assign('userId', $userId);
		$smarty->assign('userDisplayName', $userDisplayName);
		$smarty->assign('userGroups', $userGroups);
		$smarty->display('index.tpl');
		break;

	case "logout" :
		$auth->logout();
		header("Location: " . $_SERVER['SCRIPT_NAME']);
		break;

	case "upload" :
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
		$ownerDir = base64_encode($auth->getUserId());
		$targetDir = "data/files/$ownerDir";

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
		$fileName = getRequest('name', FALSE, '');

		// Clean the fileName for security reasons
		// FIXME: make this better, don't remove so much!!!		
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

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
			$metaData['fileOwner'] = $auth->getUserId();
			$storage->createEntry($dbName, $metaData);
			logHandler("User '" . $auth->getUserID() . "' uploaded file '" . $metaData['fileName'] . "'");
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		break;

	default :
		throw new Exception("unknown action");
}
?>
