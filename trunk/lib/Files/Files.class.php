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
	var $auth;
	var $smarty;

	function __construct($config, $storage, $auth, $smarty) {
		$this->config = $config;
		$this->storage = $storage;
		$this->auth = $auth;
		$this->smarty = $smarty;
	}

        function myFiles() {
                $files = $this->storage->get("_design/files/_view/by_date")->body->rows;
                /* fix the view to only display from specified user! */
                /* fix the view to also return total file size! */
                $this->smarty->assign('files', $files);
                $this->smarty->assign('type', 'myFiles');

                $tagInfo = $this->storage->get("_design/files/_view/tag_count?group=true")->body->rows;
                $this->smarty->assign('tagInfo', $tagInfo);

                $content = $this->smarty->fetch('FileList.tpl');
                return $content;
        }

	function fileInfo() {
		$id = getRequest("id", TRUE);
		$info = $this->storage->get($id)->body;
		if ($info->fileOwner !== $this->auth->getUserId())
			throw new Exception("access denied");
		$this->smarty->assign('fileInfo', $info);
		$this->smarty->assign('userGroups', $this->auth->getUserGroups());
		return $this->smarty->fetch('FileInfo.tpl');
	}

	function rawFileInfo() {
                $id = getRequest("id", TRUE);
                $info = $this->storage->get($id)->body;
                if ($info->fileOwner !== $this->auth->getUserId())
                        throw new Exception("access denied");
                $this->smarty->assign('fileInfo', var_export($info, TRUE));
                return $this->smarty->fetch('RawFileInfo.tpl');
	}

	function deleteFile() {
		$id = getRequest("id", TRUE);
                $info = $this->storage->get($id)->body;
                if ($info->fileOwner !== $this->auth->getUserId())
                        throw new Exception("access denied");

		logHandler("User '" . $this->auth->getUserId() . "' is deleting file '" . $info->fileName . "'");
		$this->storage->delete($id, $info->_rev);
		$filePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;
		unlink($filePath);	/* delete file from file system */
		return $this->myFiles();
	}

        function updateFileInfo() {
                $id = getRequest("id", TRUE);
                $info = $this->storage->get($id)->body;
                if ($info->fileOwner !== $this->auth->getUserId())
                        throw new Exception("access denied");

		$fileName = getRequest('fileName', FALSE, $info->fileName); 
                $fileDescription = getRequest('fileDescription', FALSE, $info->fileDescription);
                $fileTags = getRequest('fileTags', FALSE, $info->fileTags);
		$fileShareGroups = getRequest('fileShareGroups', FALSE, array()); /* not set means everything deselected! */

		/* Name */
		if($fileName != $info->fileName) {
			/* file name changed, update entry and file system */
	                $filePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;
			$newFilePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $fileName;
			rename($filePath, $newFilePath);
			$info->fileName = $fileName;
		}

		/* Tags */
		$tags = explode(",", $fileTags);
		$info->fileTags = array();
		foreach($tags as $t) {
			$t = trim(htmlspecialchars($t));
			if(!empty($t))
				array_push($info->fileTags, $t);
		}

		/* Description */
		$info->fileDescription = trim(htmlspecialchars($fileDescription));

		/* ShareGroups */
		$info->fileShareGroups = $this->auth->memberOfGroups($fileShareGroups);

                $this->storage->put($id, $info);
		return $this->fileInfo();
        }

	function fileUpload() {
                $content = $this->smarty->fetch('FileUpload.tpl');
		return $content;
	}

        function downloadFile() {
                $id = getRequest("id", TRUE);
                $token = getRequest("token", FALSE, 0);
                $info = $this->storage->get($id)->body;

                /* FIXME: memberOfGroups and token only if said support is enabled! */
                if ($info->fileOwner === $this->auth->getUserId()) {
                        $filePath = getConfig($this->config, 'file_storage_dir', TRUE) . "/" . base64_encode($info->fileOwner) . "/" . $info->fileName;

                        if (!file_exists($filePath))
                                throw new Exception("file does not exist on file system");

                        logHandler("User '" . $this->auth->getUserID() . "' is downloading file '" . $info->fileName . "'");

                        set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($this->config, 'pear_path', TRUE));
                        require_once ('HTTP/Download.php');
                        $dl = new HTTP_Download();
                        $dl->setFile($filePath);
                        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $info->fileName);
                        $dl->guessContentType();
                        $dl->send();
                        exit (0);
                } else {
                        throw new Exception("access denied");
                }
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
			$this->storage->createEntry($metaData);
			logHandler("User '" . $this->auth->getUserID() . "' uploaded file '" . $metaData['fileName'] . "'");
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
}
?>
