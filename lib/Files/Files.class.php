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

class Files {

	var $licenses = array (
		'none'         => 'All Rights Reserved',
		'cc3-by'       => 'Creative Commons 3.0 BY (Attribution)',
		'cc3-by-sa'    => 'Creative Commons 3.0 BY-SA (Attribution-ShareAlike)',
		'cc3-by-nd'    => 'Creative Commons 3.0 BY-ND (Attribution-NoDerivs)',
		'cc3-by-nc'    => 'Creative Commons 3.0 BY-NC (Attribution-NonCommercial)',
		'cc3-by-nc-nd' => 'Creative Commons 3.0 BY-NC-ND (Attribution-NonCommercial-NoDerivs)',
		'cc3-by-nc-sa' => 'Creative Commons 3.0 BY-ND-SA (Attribution-NonCommercial-ShareAlike)',
                'pd'           => 'Public Domain',
	);

	var $config;
	var $storage;
	var $auth;
	var $group;
	var $smarty;
	var $useRest;

	function __construct($config, $storage, $auth, $groups, $smarty) {
		$this->config = $config;
		$this->storage = $storage;
		$this->auth = $auth;
		$this->groups = $groups;
		$this->smarty = $smarty;
	}

	function setRest($useRest = FALSE) {
		$this->useRest = $useRest;
	}

	function showFiles() {
		$userId = $this->auth->getUserId();

		$groupShare = getConfig($this->config, 'group_share', FALSE, FALSE);

		$tag = getRequest("tag", FALSE, FALSE);
		$group = getRequest("group", FALSE, 0);

		if (empty ($tag))
			$tag = FALSE;

		if (is_numeric($group))
			$group = (int) $group;

		$skip = getRequest("skip", FALSE, 0);

		$limit = getConfig($this->config, 'objects_per_page', FALSE, 100);

		/*
		 * FIXME: this is a bit ugly, group identifiers cannot be numeric, but
		 *        this was a requirement anyway for broken CouchDB? 
		 *
		 * $group === 0		--> Private Files
		 */
		if ($tag !== FALSE && $group === 0) {
			$query = "_design/files/_view/get_files_tag?descending=true&startkey=[\"$userId\",\"" . strtoupper($tag) . "\",{}]&endkey=[\"$userId\",\"" . strtolower($tag) . "\"]";
		} elseif ($tag === FALSE && $group === 0) {
			$query = "_design/files/_view/get_files?descending=true&startkey=[\"$userId\",{}]&endkey=[\"$userId\"]";
		} elseif ($tag !== FALSE) {
			$query = "_design/files/_view/get_group_files_tag?descending=true&startkey=[\"$group\",\"" . strtoupper($tag) . "\",{}]&endkey=[\"$group\",\"" . strtolower($tag) . "\"]";
		} elseif ($tag === FALSE) {
			$query = "_design/files/_view/get_group_files?descending=true&startkey=[\"$group\",{}]&endkey=[\"$group\"]";
		} else {
			throw new Exception("tag, group confusion, does not match a pattern?");
		}

		$files = $this->storage->get($query . "&limit=$limit&skip=$skip&reduce=false")->body->rows;
		if (sizeof($files) > 0) {
			$noOfFiles = $this->storage->get($query)->body->rows[0]->value;
		} else {
			$noOfFiles = 0;
		}

		if($this->useRest) {
			return json_encode($files);
		} else {
			$this->smarty->assign('files', $files);
			$this->smarty->assign('skip', $skip);
			$this->smarty->assign('limit', $limit);
			$this->smarty->assign('no_of_files', $noOfFiles);
			$this->smarty->assign('tag', $tag);
			$this->smarty->assign('group', $group);

                	if($groupShare) {       
                		$this->smarty->assign('groups', array (0 => $this->auth->getUserDisplayName(), 'Groups' => $this->groups->getUserGroups()));
                	} else {
                		$this->smarty->assign('groups', array (0 => $this->auth->getUserDisplayName()));
			}

			$this->smarty->assign('myGroups', $this->groups->getUserGroups());
			$content = $this->smarty->fetch('FileList.tpl');
			return $content;
		}
	}

	function fileInfo() {
		$id = getRequest("id", TRUE);
                $info = $this->storage->get($id)->body;

                if ($info->fileOwner !== $this->auth->getUserId() && $this->groups->memberOfGroups($info->fileGroups) === FALSE && ($token == NULL || !array_key_exists($token, $info->fileTokens)))
                        throw new Exception("access denied");

		$groupShare = getConfig($this->config, 'group_share', FALSE, FALSE);
                $emailShare = getConfig($this->config, 'email_share', FALSE, FALSE);

		$hasVideo = (isset($info->video) && $info->transcodeStatus == 'DONE') ? TRUE : FALSE;
		$hasAudio = (isset($info->audio) && $info->transcodeStatus == 'DONE') ? TRUE : FALSE;
		$hasStill = (isset($info->video)) ? TRUE : FALSE;
		$hasThumb = (isset($info->image)) ? TRUE : FALSE;

		$this->smarty->assign('fileInfo', $info);
		$this->smarty->assign('groupShare', $groupShare);
                $this->smarty->assign('emailShare', $emailShare);
		$this->smarty->assign('isOwner', $info->fileOwner === $this->auth->getUserId());
		if($groupShare) {
			$this->smarty->assign('userGroups', $this->groups->getUserGroups());
		}
		$this->smarty->assign('hasVideo', $hasVideo);
                $this->smarty->assign('hasAudio', $hasAudio);
		$this->smarty->assign('hasStill', $hasStill);
		$this->smarty->assign('hasThumb', $hasThumb);

		$this->smarty->assign('allLicenses', $this->licenses);
		return $this->smarty->fetch('FileInfo.tpl');
	}

	function reExamineFile() {
                $id = getRequest("id", TRUE);
                $info = $this->storage->get($id)->body;

                if ($info->fileOwner !== $this->auth->getUserId())
 	               throw new Exception("access denied");

                $ownerDir = base64_encode($this->auth->getUserId());
                $targetDir = getConfig($this->config, 'file_storage_dir', TRUE) . "/$ownerDir";
                $cachePath = getConfig($this->config, 'cache_dir', TRUE);

		/* remove thumbnails and transcodes of this file if they exist */
		$this->deleteCacheObjects($info);

		analyzeFile($info, $targetDir, $cachePath);
                $this->storage->put($id, $info);
		return $this->fileInfo();
	}

	function rawFileInfo() {
		$id = getRequest("id", TRUE);

		$group = getRequest("group", FALSE, 0);

		$info = $this->storage->get($id)->body;

		if ($info->fileOwner !== $this->auth->getUserId() && $this->groups->memberOfGroups($info->fileGroups) === FALSE)
			throw new Exception("access denied");

		$this->smarty->assign('fileInfo', var_export($info, TRUE));
		$this->smarty->assign('group', $group);

		return $this->smarty->fetch('RawFileInfo.tpl');
	}

	function deleteFiles($filesToDelete = NULL) {
		if (!is_array($filesToDelete))
			throw new Exception("should be array");

		foreach ($filesToDelete as $id) {
			$info = $this->storage->get($id)->body;
			if ($info->fileOwner !== $this->auth->getUserId())
				throw new Exception("access denied");
			logHandler("User '" . $this->auth->getUserId() . "' is deleting file '" . $info->fileName . "'");

			/* delete the file from the file system */
			$filePath = getConfig($this->config, 'file_storage_dir', TRUE) . DIRECTORY_SEPARATOR . base64_encode($info->fileOwner) . DIRECTORY_SEPARATOR . $info->fileName;
			if (is_file($filePath))
				unlink($filePath);
			$this->deleteCacheObjects($info);
			$this->storage->delete($id, $info->_rev);
		}
		return $this->showFiles();
	}

	function deleteCacheObjects($info) {
		/* delete the cache objects belonging to this file from the file system */
		$cachePath = getConfig($this->config, 'cache_dir', TRUE);
		if (isset ($info->video->transcode)) {
        		foreach ($info->video->transcode as $k => $v) {
				if (is_file($cachePath . DIRECTORY_SEPARATOR . $v->file))
                        		unlink($cachePath . DIRECTORY_SEPARATOR . $v->file);
                	}
		}
		if (isset ($info->video->thumbnail)) {
 	        	foreach ($info->video->thumbnail as $k => $v) {
                        	if (is_file($cachePath . DIRECTORY_SEPARATOR . $v->file))
               	                	unlink($cachePath . DIRECTORY_SEPARATOR . $v->file);
                	}
		}
	}

	function confirmDelete($filesToDelete = NULL) {
		if (!is_array($filesToDelete))
			throw new Exception("should be array");

		$deleteList = array ();
		foreach ($filesToDelete as $id) {
			$info = $this->storage->get($id)->body;
			if ($info->fileOwner !== $this->auth->getUserId())
				throw new Exception("access denied");
			$deleteList[$info->_id] = $info->fileName;
		}

		$this->smarty->assign('deleteList', $deleteList);
		$this->smarty->assign('markedFiles', $filesToDelete);
		$content = $this->smarty->fetch('ConfirmDelete.tpl');
		return $content;
	}

	function updateFileInfo() {
		$button = getRequest("buttonPressed", TRUE);

		switch ($button) {
			/* called from the {File,Media}List page */
/*			case "Download" :
				return $this->downloadFile(); */

			case "Delete Files" :
				$markedFiles = getRequest("markedFiles", FALSE, array ());
				return $this->confirmDelete($markedFiles);

				/* called from the FileInfo page */
			case "Delete" :
				$id = getRequest("id", TRUE);
				return $this->confirmDelete(array (
					$id
				));

				/* called from the ConfirmDelete page */
			case "Confirm Delete" :
				$markedFiles = getRequest("markedFiles", FALSE, array ());
				return $this->deleteFiles($markedFiles);

				/* called from the FileInfo page */
			case "Update" :
				/* continue with the rest of the function */
				break;

			case "Reexamine" :
				$id = getRequest("id", TRUE);
				return $this->reExamineFile($id);

			default :
				throw new Exception("invalid button type");
		}

		$id = getRequest("id", TRUE);
		$info = $this->storage->get($id)->body;
		if ($info->fileOwner !== $this->auth->getUserId())
			throw new Exception("access denied");

		$fileName = getRequest('fileName', FALSE, $info->fileName);
		$fileDescription = getRequest('fileDescription', FALSE, $info->fileDescription);
		$fileTags = getRequest('fileTags', FALSE, implode(",", $info->fileTags));
		$fileLicense = getRequest('fileLicense', FALSE, $info->fileLicense);

		$fileTokens = getRequest('fileTokens', FALSE, implode(",", array_values((array) $info->fileTokens)));
		$fileGroups = getRequest('fileGroups', FALSE, array ()); /* not set means everything deselected! */

		/* Name */
		if ($fileName != $info->fileName) {
			/* file name changed, update entry and file system */
			$filePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;
			$newFilePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $fileName;
			rename($filePath, $newFilePath);
			$info->fileName = $fileName;
		}

		/* Tags */
		$tags = explode(",", $fileTags);
		$info->fileTags = array ();
		foreach ($tags as $t) {
			$t = trim(htmlspecialchars($t));
			if (!empty ($t) && !in_array($t, $info->fileTags, TRUE))
				array_push($info->fileTags, $t);
		}

		/* Tokens */
		$tokens = explode(",", $fileTokens);

		/*
		   - We may have some tokens already stored ($info->fileTokens)
		   - The token consists of the email address with the key being the token
		   - Now we get only email addresses from the form submit
		   - We only keep the email addresses still in the form submit (intersect)
		   - For all the new addresses we generate tokens
		   - We find out now which ones are actually new (diff)
		   - Send an email invite to all the new addresses
		   - We add the new ones with their new tokens to the tokens in $info->fileTokens,
		     while keeping the old tokens if existing
		*/

		/* The new addresses, clean them all first! */
		$newAddresses = array ();
		foreach ($tokens as $t) {
			$t = trim($t);
			if (!empty ($t)) {
				$address = filter_var($t, FILTER_VALIDATE_EMAIL);
				if ($address === FALSE)
					throw new Exception("invalid email address specified");
				if (!empty ($address) && !in_array($address, $newAddresses, TRUE))
					$newAddresses[generateToken()] = $address;
			}
		}

		/* Woah, can this really not be made simpler? */
		$isec = array_intersect((array) $info->fileTokens, $newAddresses);
		$diff = array_diff($newAddresses, (array) $info->fileTokens);
		$info->fileTokens = array_merge($isec, $diff);

		/* Send an email to all new addresses (from diff) */
		$this->smarty->assign('sender', $this->auth->getUserDisplayName());
		$this->smarty->assign('fileName', $info->fileName);

		foreach ($diff as $token => $address) {
			$url = getProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?action=fileInfo&id=$info->_id&token=$token";
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
				logHandler("User '" . $this->auth->getUserID() . "' is sharing file '" . $info->fileName . "' with '" . $address . "'");
		}

		/* Description */
		$info->fileDescription = trim(htmlspecialchars($fileDescription));

		/* Groups */
		$info->fileGroups = $this->groups->memberOfGroups($fileGroups);

		/* License */
		if (!array_key_exists($fileLicense, $this->licenses))
			throw new Exception("invalid license specified");
		$info->fileLicense = $fileLicense;

		$this->storage->put($id, $info);
		return $this->fileInfo();
	}

	function fileUpload() {
		$content = $this->smarty->fetch('FileUpload.tpl');
		return $content;
	}

	function downloadFile() {
		$id = getRequest("id", TRUE);
		$token = getRequest("token", FALSE, NULL);

		$info = $this->storage->get($id)->body;

		if ($info->fileOwner !== $this->auth->getUserId() && $this->groups->memberOfGroups($info->fileGroups) === FALSE && ($token == NULL || !array_key_exists($token, $info->fileTokens)))
			throw new Exception("access denied");

		$filePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;

		if (!is_file($filePath))
			throw new Exception("file does not exist on file system");

		logHandler("User '" . $this->auth->getUserID() . "' is downloading file '" . $info->fileName . "'");

		set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($this->config, 'pear_path', TRUE));
		require_once ('HTTP/Download.php');
		$dl = new HTTP_Download();
		$dl->setFile($filePath);
		$dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $info->fileName);
		$finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
		$dl->setContentType($finfo->file($filePath));
		$dl->send();
		exit (0);
	}

	function getCacheObject() {
		$id = getRequest("id", TRUE);
		$type = getRequest("type", TRUE);

		$validTypes = array (
			'video_thumbnail_90',
			'video_thumbnail_180',
			'video_thumbnail_360',
			'video_transcode_360',
			'audio_transcode',
			'image_thumbnail_360',
		);

		$info = $this->storage->get($id)->body;

		if ($info->fileOwner !== $this->auth->getUserId() && $this->groups->memberOfGroups($info->fileGroups) === FALSE)
			throw new Exception("access denied");

		if (!in_array($type, $validTypes))
			throw new Exception("invalid cache type");

		list ($mediaType, $t, $subT) = explode("_", $type);

		$cachePath = getConfig($this->config, 'cache_dir', TRUE);
		if($mediaType == 'video')
			$file = $cachePath . DIRECTORY_SEPARATOR . $info->video->$t-> $subT->file;
		elseif($mediaType == 'audio')
                        $file = $cachePath . DIRECTORY_SEPARATOR . $info->audio->$t->file;
		elseif($mediaType == 'image')
			$file = $cachePath . DIRECTORY_SEPARATOR . $info->image->$t-> $subT->file;

		if (!is_file($file))
			throw new Exception("file does not exist on file system");

		set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($this->config, 'pear_path', TRUE));
		require_once ('HTTP/Download.php');
		$dl = new HTTP_Download();
		$dl->setFile($file);
		$dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, basename($file));
		$finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
		$dl->setContentType($finfo->file($file));
		$dl->send();
		exit (0);
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
		$targetDir = getConfig($this->config, 'file_storage_dir', TRUE) . "/$ownerDir";
		$cachePath = getConfig($this->config, 'cache_dir', TRUE);

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
			$metaData = new stdClass();
			$metaData->fileName = $fileName;
			analyzeFile($metaData, $targetDir, $cachePath);
                        $metaData->fileOwner = $this->auth->getUserId();
			$metaData->fileTags = array (
                        	'Demo Tag',
	                        "Length" . strlen(basename($userFile))
	                );
	                $metaData->fileDescription = 'Uploaded on ' . strftime("%c", time());
	                $metaData->fileGroups = array ();
	                $metaData->fileTokens = array ();
	                $metaData->fileLicense = 'none';
	                $metaData->fileTags = array ();
			$this->storage->post($metaData);
			logHandler("User '" . $this->auth->getUserID() . "' uploaded file '" . $metaData->fileName . "'");
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
}
?>
