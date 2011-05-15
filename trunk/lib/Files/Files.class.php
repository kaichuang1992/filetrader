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
		$groupShare = getConfig($this->config, 'group_share', FALSE, FALSE);

                $userId = $this->auth->getUserId();
		$tag = getRequest("tag", FALSE, FALSE);
		$group = getRequest("group", FALSE, 0);

		if (empty ($tag))
			$tag = FALSE;

		if (is_numeric($group))
			$group = (int) $group;

                if($group !== 0 && !$this->groups->memberOfGroups(array($group)))
                        throw new Exception("access denied");

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
	
		$token = getRequest("token", FALSE, NULL);

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
		$this->smarty->assign('token', $token);
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

		/* FIXME: also remove image, video, audio elements from metadata array before analyzing! */
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
		/* FIXME: delete image thumbnails */
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

	function legacyFileUpload() {
		$pms = return_bytes(ini_get('post_max_size'));
		$umf = return_bytes(ini_get('upload_max_filesize'));

                $this->smarty->assign('max_upload_size', min($pms,$umf));
		$content = $this->smarty->fetch('LegacyFileUpload.tpl');
		return $content;
	}

	function downloadFile() {
		$id = getRequest("id", TRUE);
		$token = getRequest("token", FALSE, NULL);

		$info = $this->storage->get($id)->body;

		if ($info->fileOwner !== $this->auth->getUserId() && $this->groups->memberOfGroups($info->fileGroups) === FALSE && ($token == NULL || !array_key_exists($token, $info->fileTokens)))
			throw new Exception("access denied");

		$filePath = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;

		if (!is_file($filePath))
			throw new Exception("file does not exist on file system");

		logHandler("User '" . $this->auth->getUserID() . "' is downloading file '" . $info->fileName . "'");

                $finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
		header("X-Sendfile: " . $filePath);
		header("Content-Type: " . $finfo->file($filePath));
		header('Content-Disposition: attachment; filename="' . $info->fileName . '"');
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

		$cachePath = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . getConfig($this->config, 'cache_dir', TRUE);
		if($mediaType == 'video')
			$filePath = $cachePath . DIRECTORY_SEPARATOR . $info->video->$t-> $subT->file;
		elseif($mediaType == 'audio')
                        $filePath = $cachePath . DIRECTORY_SEPARATOR . $info->audio->$t->file;
		elseif($mediaType == 'image')
			$filePath = $cachePath . DIRECTORY_SEPARATOR . $info->image->$t-> $subT->file;

		if (!is_file($filePath))
			throw new Exception("file does not exist on file system");

                $finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
                header("X-Sendfile: " . $filePath);
                header("Content-Type: " . $finfo->file($filePath));
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                exit (0);
	}

	function handleLegacyUpload() {
                $ownerDir = base64_encode($this->auth->getUserId());
                $targetDir = getConfig($this->config, 'file_storage_dir', TRUE) . "/$ownerDir";
                $cachePath = getConfig($this->config, 'cache_dir', TRUE);

                if (!file_exists($targetDir))
                        @mkdir($targetDir);

		/* fixme, better checking?! */
                $fileName = basename($_FILES['userfile']['name']);
                $fileName = filter_var($fileName, FILTER_SANITIZE_SPECIAL_CHARS);
                if ($fileName === FALSE) {
	                logHandler("Invalid File-Name '" . $fN . "' by user '" . $this->auth->getUserId() . "'");
                        die();
                }
		$targetFile = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $targetFile)) {
			logHandler("Error moving uploaded file to final destination by user '" . $this->auth->getUserId() . "'");
			die();
		}

                $metaData = new stdClass();
                $metaData->fileName = $fileName;
                analyzeFile($metaData, $targetDir, $cachePath);
                $metaData->fileOwner = $this->auth->getUserId();
                $metaData->fileDescription = 'Uploaded on ' . strftime("%c", time());
                $metaData->fileGroups = array ();
                $metaData->fileTokens = array ();
                $metaData->fileLicense = 'none';
                $metaData->fileTags = array ();
                $this->storage->post($metaData);
                logHandler("User '" . $this->auth->getUserID() . "' uploaded file '" . $metaData->fileName . "'");

		header("Location: index.php?action=showFiles");
		exit(0);
	}

	/* FIXME: deal with duplicate file names */
	function handleUpload() {
                $ownerDir = base64_encode($this->auth->getUserId());
                $targetDir = getConfig($this->config, 'file_storage_dir', TRUE) . "/$ownerDir";
                $cachePath = getConfig($this->config, 'cache_dir', TRUE);

                if (!file_exists($targetDir))
                        @mkdir($targetDir);

		$httpHeaders = getallheaders();
		if(array_key_exists('X-Requested-With', $httpHeaders) && $httpHeaders['X-Requested-With'] === "XMLHttpRequest" && array_key_exists('X-File-Name', $httpHeaders) && array_key_exists('X-File-Size', $httpHeaders)) {
			$fileName = basename($httpHeaders['X-File-Name']);
			$fileSize = $httpHeaders['X-File-Size'];
                        $fileChunk = 0;
                        if(array_key_exists('X-File-Chunk', $httpHeaders))
                                $fileChunk = $httpHeaders['X-File-Chunk'];

	                $fileName = filter_var($fileName, FILTER_SANITIZE_SPECIAL_CHARS);
	                if ($fileName === FALSE) {
	                        logHandler("Invalid X-File-Name '" . $fN . "' by user '" . $this->auth->getUserId() . "'");
				die();
			}

			if(!is_numeric($fileSize) || $fileSize < 0) {
				logHandler("Invalid X-File-Size '" . $fileSize . "' by user '" . $this->auth->getUserId() . "'");
				die();
			}
                        $fileSize = (int)$fileSize;

                        if(!is_numeric($fileChunk) || $fileChunk < 0) {
                                logHandler("Invalid X-File-Chunk '" . $fileChunk . "' by user '" . $this->auth->getUserId() . "'");
                                die();
                        }
                       	$fileChunk = (int)$fileChunk;

		        $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, ($fileChunk == 0) ? "wb" : "ab");
		        if ($out) {
		                $in = fopen("php://input", "rb");
		                if ($in) {
		                        while ($buffer = fread($in, 4096))
		                        fwrite($out, $buffer);
		                } else {
		                       // FIXME: failed to open input stream
		                }
		                fclose($in);
		                fclose($out);
				flush();

				/* only check file when upload is complete */
				if($fileSize == filesize($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
		                        $metaData = new stdClass();
		                        $metaData->fileName = $fileName;
		                        analyzeFile($metaData, $targetDir, $cachePath);
		                        $metaData->fileOwner = $this->auth->getUserId();
		                        $metaData->fileDescription = 'Uploaded on ' . strftime("%c", time());
	        	                $metaData->fileGroups = array ();
		                        $metaData->fileTokens = array ();
		                        $metaData->fileLicense = 'none';
		                        $metaData->fileTags = array ();
		                        $this->storage->post($metaData);
		                        logHandler("User '" . $this->auth->getUserID() . "' uploaded file '" . $metaData->fileName . "'");		
				}
			} else {
		        	// FIXME: failed to open output stream
			}		
		}
		exit(0);
	}
}
?>
