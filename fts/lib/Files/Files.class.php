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
	private $config;
	private $dbh;

	function __construct($config) {
		$this->config = $config;
		try {
			$this->dbh = new PDO(
					"sqlite:" . getConfig($this->config, 'token_file', TRUE),
					NULL, NULL, array(PDO::ATTR_PERSISTENT => TRUE));

			/* FIXME: maybe this should be placed somewhere else, inefficient?!... */
			$this->dbh
					->query(
							'CREATE TABLE IF NOT EXISTS downloadTokens (token TINYTEXT, filePath TEXT, PRIMARY KEY (token), UNIQUE(token))');
			$this->dbh
					->query(
							'CREATE TABLE IF NOT EXISTS   uploadTokens (token TINYTEXT, filePath TEXT, fileSize INT, PRIMARY KEY (token), UNIQUE(token))');
		} catch (Exception $e) {
			throw new Exception("database connection failed");
		}
	}

	function __destruct() {
		$this->dbh = NULL;
	}

	function getDownloadToken() {
		/* FIXME: token should expire, based on server request? */

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

		/* FIXME verify filePath */
		$relativeFilePath = getRequest('relativeFilePath', TRUE);

		/* file needs to exist before getting a token is allowed */
		$filePath = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . $relativeFilePath;
		if (!file_exists($filePath)) {
			throw new Exception("file does not exist");
		}

		$token = generateToken();

		try {
			$stmt = $this->dbh
					->prepare(
							"INSERT INTO downloadTokens (token, filePath) VALUES (:token, :filePath)");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':filePath', $filePath);
			$stmt->execute();

			$downloadLocation = getProtocol() . $_SERVER['SERVER_NAME']
					. $_SERVER['PHP_SELF']
					. "?action=downloadFile&token=$token";

			return array("downloadLocation" => $downloadLocation);
		} catch (Exception $e) {
			throw new Exception("database query failed");
		}
	}

	function getUploadToken() {
		/* FIXME: token should expire, based on server request? */
		/* FIXME: what if upload size is not known in time? transcode web service for example... */
		/* FIXME: maybe transparently fix the file name on duplicate upload */

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

                /* FIXME verify filePath */
                $relativeFilePath = getRequest('relativeFilePath', TRUE);

		/* verify fileSize
		 * 
		 * NOTE: fileSize *is* required, but 0 is a valid file size, but also 
		 * seen as "empty" by PHP, so we work around it like this...
		 */
		$fileSize = (int) getRequest('fileSize', FALSE, 0);
		if ($fileSize < 0) {
			throw new Exception("invalid filesize");
		}

		$filePath = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . $relativeFilePath;
		if (file_exists($filePath)) {
			throw new Exception("file already exists");
		}

		$token = generateToken();

		try {
			$stmt = $this->dbh
					->prepare(
							"INSERT INTO uploadTokens (token, filePath, fileSize) VALUES (:token, :filePath, :fileSize)");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':filePath', $filePath);
			$stmt->bindParam(':fileSize', $fileSize);
			$stmt->execute();

			$uploadLocation = getProtocol() . $_SERVER['SERVER_NAME']
					. $_SERVER['PHP_SELF'] . "?action=uploadFile&token=$token";
			return array("uploadLocation" => $uploadLocation);
		} catch (Exception $e) {
			throw new Exception("database query failed");
		}
	}

	function downloadFile() {
		/* FIXME: delete token after download, but still support range requests, pickle?! */
		/* FIXME: delete token immediately on first request? */

		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			throw new Exception("invalid request method, should be GET", 405);
		}

		if (!isset($_GET['token']) || empty($_GET['token'])
				|| !ctype_xdigit($_GET['token']))
			throw new Exception("invalid token", 400);
		$token = $_GET['token'];

		$stmt = $this->dbh
				->prepare(
						"SELECT token, filePath FROM downloadTokens WHERE token=:token");
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		$row = $stmt->fetch();
		if (empty($row)) {
			throw new Exception("token not found", 404);
		}

		$filePath = $row['filePath'];
		$fileName = basename($filePath);

//		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
//				. DIRECTORY_SEPARATOR . base64_encode($userName);
//		$filePath = $fileDir . DIRECTORY_SEPARATOR . $fileName;
		if (!is_file($filePath)) {
			throw new Exception("file does not exist", 500);
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		header("X-Sendfile: " . $filePath);
		header("Content-Type: " . $finfo->file($filePath));
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		exit(0);
	}

	function uploadFile() {
		/* FIXME: limit content-length to something reasonable, max file upload size from PHP? */

		if ($_SERVER['REQUEST_METHOD'] != 'PUT'
				&& $_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
			throw new Exception("invalid request method, should be PUT", 405);
		}

		/* This is to allow "preflight" XMLHttpRequests, 
		 * see https://developer.mozilla.org/En/HTTP_access_control */
		/* FIXME: maybe move this to after the token verification? */
		/* FIXME: maybe be more restrictive in where to allow requests from? */
		if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: PUT, OPTIONS');
			header('Access-Control-Allow-Headers: X-File-Chunk, Content-Type');
			header('Access-Control-Max-Age: 1728000');
			header("Content-Length: 0");
			header("Content-Type: text/plain");
			exit(0);
			break;
		} else {
			header('Access-Control-Allow-Origin: *');
		}

		if (!isset($_GET['token']) || empty($_GET['token'])
				|| !ctype_xdigit($_GET['token']))
			throw new Exception("invalid token", 400);
		$token = $_GET['token'];

		$stmt = $this->dbh
				->prepare(
						"SELECT token, filePath, fileSize FROM uploadTokens WHERE token=:token");
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		$row = $stmt->fetch();
		if (empty($row)) {
			throw new Exception("token not found", 404);
		}

		$filePath = $row['filePath'];
		$fileSize = $row['fileSize'];

		/* Directory should already exist! FIXME: also catch in getUploadToken! */
		if(!file_exists(dirname($filePath)) || !is_dir(dirname($filePath))) {
			throw new Exception("directory to upload file does not exist", 500);
		}

		/* chunk number has to be >=0 */
		$fileChunk = (int) getHeader('X-File-Chunk', FALSE, 0);
		if ($fileChunk < 0) {
			throw new Exception("invalid x-file-chunk header", 400);
		}

		/* chunk can be >0 only if file already exists */
		if ($fileChunk > 0 && !file_exists($filePath)) {
			throw new Exception(
					"file does not exist, cannot send chunk number >0", 400);
		}

		/* append to existing file if chunk >0, else create new file */
		$out = fopen($filePath, ($fileChunk == 0) ? "wb" : "ab");
		if ($out) {
			$in = fopen("php://input", "rb");
			if ($in) {
				while ($buffer = fread($in, 4096))
					fwrite($out, $buffer);
			} else {
				throw new Exception("unable to open upload stream", 500);
			}
			fclose($in);
			fclose($out);
			flush();
		} else {
			throw new Exception("unable to open output file", 500);
		}

		/* if upload complete, i.e.: final size as specified in token reached, delete token */
		if (filesize($filePath) >= $fileSize) {
			$stmt = $this->dbh
					->prepare("DELETE FROM uploadTokens WHERE token=:token");
			$stmt->bindParam(':token', $token);
			$stmt->execute();
		}
		return array("chunk" => $fileChunk);
	}

	function getFileList() {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			throw new Exception("invalid request method, should be GET");
		}

                /* FIXME verify dirPath */
                $relativeDirPath = getRequest('relativeDirPath', TRUE);

		$dirPath = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . $relativeDirPath;

		/* is chdir only valid for this call? May break some other stuff? */
		/* FIXME: check before if dir exists? */
		if (chdir($dirPath) === FALSE) {
			throw new Exception("directory does not exist");
		}

		$fileList = array();
		foreach (glob("*") as $fileName) {
			$fileList['files'][$fileName] = array(
					"fileSize" => filesize($fileName),
					"isDirectory" => is_dir($fileName));
		}
		return $fileList;
	}

	function deleteFile() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

                /* FIXME verify filePath */
                $relativeFilePath = getRequest('relativeFilePath', TRUE);

		$filePath = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . $relativeFilePath;

		if (!is_file($filePath)) {
			throw new Exception("file does not exist");
		}

		if (unlink($filePath) === FALSE) {
			throw new Exception("unable to delete file");
		}

		return array();
	}

	function deleteDirectory() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

                /* FIXME verify dirPath */
                $relativeDirPath = getRequest('relativeDirPath', TRUE);

                $dirPath = getConfig($this->config, 'file_storage_dir', TRUE)
                                . DIRECTORY_SEPARATOR . $relativeDirPath;

		if (!file_exists($dirPath) || !is_dir($dirPath)) {
			throw new Exception("directory does not exist, or is not a directory");
		}

		if (rmdir($dirPath) === FALSE) {
			throw new Exception("unable to delete directory");
		}

		return array();
	}

	function createDirectory() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

                /* FIXME verify dirPath */
                $relativeDirPath = getRequest('relativeDirPath', TRUE);

                $dirPath = getConfig($this->config, 'file_storage_dir', TRUE)
                                . DIRECTORY_SEPARATOR . $relativeDirPath;

		if (!file_exists(dirname($dirPath)) || !is_dir(dirname($dirPath))) {
                        throw new Exception("parent of directory does not exist, or is not a directory");
		}

		if (file_exists($dirPath)) {
			throw new Exception(
					"directory (or file) with that name already exists");
		}

		if (mkdir($dirPath, 0775) === FALSE) {
			throw new Exception("unable to create directory");
		}

		return array();
	}

	function serverInfo() {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			throw new Exception("invalid request method, should be GET");
		}

		/* determine available disk space on server */
		$df = disk_free_space(getConfig($this->config, 'storage_dir', TRUE));

		/* determine IP version (IPv4, IPv6) */
		if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP,
				FILTER_FLAG_IPV6) === FALSE) {
			$ipVersion = 4;
		} else {
			$ipVersion = 6;
		}

		return array("availableSpace" => $df, 'ipVersion' => $ipVersion,
				'remoteAddr' => $_SERVER['REMOTE_ADDR'],
				'systemDate' => date("c", time()));
	}

	function pingServer() {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			throw new Exception("invalid request method, should be GET");
		}
		return array('message' => 'FileTrader REST API');
	}
}
?>
