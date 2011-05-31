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
							'CREATE TABLE IF NOT EXISTS downloadTokens (token TINYTEXT, userName TINYTEXT, fileName TINYTEXT, PRIMARY KEY (token), UNIQUE(token))');
			$this->dbh
					->query(
							'CREATE TABLE IF NOT EXISTS   uploadTokens (token TINYTEXT, userName TINYTEXT, fileName TINYTEXT, fileSize INT, PRIMARY KEY (token), UNIQUE(token))');
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

		/* verify userName */
		$userName = filter_var(getRequest('userName', TRUE),
				FILTER_SANITIZE_SPECIAL_CHARS);
		if ($userName === FALSE)
			throw new Exception("invalid username");

		/* verify fileName */
		$fileName = filter_var(
				basename(getRequest('fileName', TRUE),
						FILTER_SANITIZE_SPECIAL_CHARS));
		if ($fileName === FALSE)
			throw new Exception("invalid filename");

		/* file needs to exist before getting a token is allowed */
		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . base64_encode($userName);
		$filePath = $fileDir . DIRECTORY_SEPARATOR . $fileName;
		if (!file_exists($filePath)) {
			throw new Exception("file does not exist");
		}

		$token = generateToken();

		try {
			$stmt = $this->dbh
					->prepare(
							"INSERT INTO downloadTokens (token, userName, fileName) VALUES (:token, :userName, :fileName)");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':userName', $userName);
			$stmt->bindParam(':fileName', $fileName);
			$stmt->execute();
			return array("downloadToken" => $token);
		} catch (Exception $e) {
			throw new Exception("database query failed");
		}
	}

	function getUploadToken() {
		/* FIXME: token should expire, based on server request? */
		/* FIXME: deal with existing files? */
		/* FIXME: what if upload size is not known in time? transcode web service for example... */

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

		/* verify userName */
		$userName = filter_var(getRequest('userName', TRUE),
				FILTER_SANITIZE_SPECIAL_CHARS);
		if ($userName === FALSE)
			throw new Exception("invalid username");

		/* verify fileName */
		$fileName = filter_var(
				basename(getRequest('fileName', TRUE),
						FILTER_SANITIZE_SPECIAL_CHARS));
		if ($fileName === FALSE)
			throw new Exception("invalid filename");

		/* verify fileSize
		 * 
		 * NOTE: fileSize *is* required, but 0 is a valid file size, but also 
		 * seen as "empty" by PHP, so we work around it like this...
		 */
		$fileSize = (int) getRequest('fileSize', FALSE, 0);
		if ($fileSize < 0) {
			throw new Exception("invalid filesize");
		}

		$token = generateToken();

		try {
			$stmt = $this->dbh
					->prepare(
							"INSERT INTO uploadTokens (token, userName, fileName, fileSize) VALUES (:token, :userName, :fileName, :fileSize)");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':userName', $userName);
			$stmt->bindParam(':fileName', $fileName);
			$stmt->bindParam(':fileSize', $fileSize);
			$stmt->execute();
			return array("uploadToken" => $token);
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
						"SELECT token, userName, fileName FROM downloadTokens WHERE token=:token");
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		$row = $stmt->fetch();
		if (empty($row)) {
			throw new Exception("token not found", 404);
		}

		$userName = $row['userName'];
		$fileName = $row['fileName'];

		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . base64_encode($userName);
		$filePath = $fileDir . DIRECTORY_SEPARATOR . $fileName;
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

		if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
			throw new Exception("invalid request method, should be PUT", 405);
		}

		if (!isset($_GET['token']) || empty($_GET['token'])
				|| !ctype_xdigit($_GET['token']))
			throw new Exception("invalid token", 400);
		$token = $_GET['token'];

		$stmt = $this->dbh
				->prepare(
						"SELECT token, userName, fileName, fileSize FROM uploadTokens WHERE token=:token");
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		$row = $stmt->fetch();
		if (empty($row)) {
			throw new Exception("token not found", 404);
		}

		$userName = $row['userName'];
		$fileName = $row['fileName'];
		$fileSize = $row['fileSize'];

		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . base64_encode($userName);
		if (!file_exists($fileDir)) {
			if (mkdir($fileDir) === FALSE) {
				throw new Exception("unable to create directory", 500);
			}
		}
		$filePath = $fileDir . DIRECTORY_SEPARATOR . $fileName;

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

	function deleteFile() {
		/* FIXME: implement */
	}

	function getFileList() {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			throw new Exception("invalid request method, should be GET");
		}

		/* verify userName */
		$userName = filter_var(getRequest('userName', TRUE),
				FILTER_SANITIZE_SPECIAL_CHARS);
		if ($userName === FALSE)
			throw new Exception("invalid username");

		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . base64_encode($userName);

		/* is chdir only valid for this call? May break some other stuff? */
		if (chdir($fileDir) === FALSE) {
			throw new Exception("user does not have files in this store");
		}

		$fileList = array();
		foreach (glob("*") as $fileName) {
			$fileList['files'][$fileName] = array(
					"fileSize" => filesize($fileName),
					"isDirectory" => is_dir($fileName));
		}
		return $fileList;
	}

	function createDirectory() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new Exception("invalid request method, should be POST");
		}

		/* verify userName */
		$userName = filter_var(getRequest('userName', TRUE),
				FILTER_SANITIZE_SPECIAL_CHARS);
		if ($userName === FALSE)
			throw new Exception("invalid username");

		$fileDir = getConfig($this->config, 'file_storage_dir', TRUE)
				. DIRECTORY_SEPARATOR . base64_encode($userName);

		/* verify dirName */
		$dirName = filter_var(
				basename(getRequest('dirName', TRUE),
						FILTER_SANITIZE_SPECIAL_CHARS));
		if ($dirName === FALSE)
			throw new Exception("invalid dirname");

		/* validate existence of parent of specified directory */
		$baseDir = realpath($fileDir . DIRECTORY_SEPARATOR . dirname($dirName));
		if ($baseDir === FALSE) {
			throw new Exception(
					"parent of specified directory is invalid or does not exist");
		}
		if (strpos($baseDir, $fileDir) === FALSE
				|| strpos($baseDir, $fileDir) !== 0) {
			throw new Exception("trying to escape directory structure?");
		}
		if (!is_dir($baseDir)) {
			throw new Exception(
					"parent of specified directory is not a directory?");
		}

		/* create the directory */
                $newDir = $baseDir . DIRECTORY_SEPARATOR . basename($dirName);
		if(file_exists($newDir)) {
			throw new Exception("directory (or file) with that name already exists");
		}

		if (mkdir($newDir, 0775) === FALSE) {
			throw new Exception("unable to create directory");
		}

		return array("directoryPath" => $newDir);
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
}
?>
