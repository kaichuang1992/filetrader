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

define('API_VERSION', '0.2');

/* Validation constants */
define('FTS_DIR', 0);
define('FTS_FILE', 1);
define('FTS_PARENT', 2);

class Files {

    private $config;
    private $dbh;
    private $fsd; /* points to file storage directory from configuration
      with the consumer key appended to it in case of OAuth
      requests */

    function __construct($dbh, $config) {
        if ($dbh == NULL) {
            throw new Exception("no database provided");
        }
        $this->dbh = $dbh;

        $this->config = $config;
        $this->fsd = getConfig($config, 'fts_data', TRUE);

        /* FIXME: move to SETUP procedure? */
        $this->dbh->query('CREATE TABLE IF NOT EXISTS downloadTokens (token TEXT PRIMARY KEY UNIQUE, filePath TEXT)');
        $this->dbh->query('CREATE TABLE IF NOT EXISTS   uploadTokens (token TEXT PRIMARY KEY UNIQUE, filePath TEXT, fileSize INTEGER)');
        $this->dbh->query('CREATE TABLE IF NOT EXISTS       metaData (filePath TEXT PRIMARY KEY UNIQUE, fileDescription TEXT)');
    }

    function getDownloadToken() {
        requireRequestMethod("POST");

        /* FIXME: token should expire, based on server request? */
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw new Exception("invalid request method, should be POST");
        }

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_FILE);

        $token = generateToken();

        try {
            $stmt = $this->dbh
                    ->prepare(
                            "INSERT INTO downloadTokens (token, filePath) VALUES (:token, :filePath)");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':filePath', $absPath);
            $stmt->execute();

            $downloadLocation = getProtocol() . getServerName() . $_SERVER['PHP_SELF'] . "?action=downloadFile&token=$token";
            return array("downloadLocation" => $downloadLocation,
                "absPath" => $absPath);
        } catch (Exception $e) {
            throw new Exception("database query failed");
        }
    }

    function getUploadToken() {
        requireRequestMethod("POST");

        /* FIXME: token should expire, based on server request? */
        /* FIXME: what if upload size is not known in time? transcode web service for example... */
        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_PARENT);

        /* make sure the uploaded file name is unique */
        $absPath = $this->getUniqueName($absPath);

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
                            "INSERT INTO uploadTokens (token, filePath, fileSize) VALUES (:token, :filePath, :fileSize)");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':filePath', $absPath);
            $stmt->bindParam(':fileSize', $fileSize);
            $stmt->execute();

            $uploadLocation = getProtocol() . getServerName() . $_SERVER['PHP_SELF'] . "?action=uploadFile&token=$token";
            return array("uploadLocation" => $uploadLocation,
                "absPath" => $absPath);
        } catch (Exception $e) {
            throw new Exception("database query failed");
        }
    }

    function downloadFile() {
        requireRequestMethod("GET", 405);

        /* FIXME: delete token after download, but still support range requests, pickle?! */
        /* FIXME: delete token immediately on first request? */
        if (!isset($_GET['token']) || empty($_GET['token']) || !ctype_xdigit($_GET['token'])) {
            throw new Exception("invalid token", 400);
        }
        $token = $_GET['token'];

        /* FIXME: why no try/catch here? */
        $stmt = $this->dbh
                ->prepare(
                        "SELECT token, filePath FROM downloadTokens WHERE token=:token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch();
        if (empty($row)) {
            throw new Exception("token not found", 404);
        }

        $absPath = $row['filePath'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        header("Content-Type: " . $finfo->file($absPath));
        header("X-Sendfile: " . $absPath);
        if ($finfo->file($absPath) != "text/plain") {
            header(
                    'Content-Disposition: attachment; filename="'
                    . basename($absPath) . '"');
        }
        exit(0);
    }

    function uploadFile() {
        requireRequestMethod(array("PUT","OPTIONS"), 405);

        /* FIXME: limit content-length to something reasonable, max file upload size from PHP? */
        
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
                || !ctype_xdigit($_GET['token'])) {
            throw new Exception("invalid token", 400);
        }
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

        $absPath = $row['filePath'];
        $fileSize = $row['fileSize'];

        /* chunk number has to be >=0 */
        $fileChunk = (int) getHeader('X-File-Chunk', FALSE, 0);
        if ($fileChunk < 0) {
            throw new Exception("invalid x-file-chunk header", 400);
        }

        if ($fileChunk == 0 && file_exists($absPath)) {
            throw new Exception("file already exists", 500);
        }

        /* chunk can be >0 only if file already exists */
        if ($fileChunk > 0 && !file_exists($absPath)) {
            throw new Exception(
                    "file does not exist, cannot send chunk number >0", 400);
        }

        /* append to existing file if chunk >0, else create new file */
        $out = fopen($absPath, ($fileChunk == 0) ? "wb" : "ab");
        if ($out) {
            $in = fopen("php://input", "rb");
            if ($in) {
                while ($buffer = fread($in, 4096)) {
                    fwrite($out, $buffer);
                }
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
        if (filesize($absPath) >= $fileSize) {
            $stmt = $this->dbh
                    ->prepare("DELETE FROM uploadTokens WHERE token=:token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
        }
        return array("chunk" => $fileChunk);
    }

    function setDescription() {
	/* FIXME: there seems to be a different error for setting description
	   if file did not exist yet, or after it was removed? */
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw new Exception("invalid request method, should be POST");
        }

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_FILE);

        /* FIXME: filter_var */
        $description = getRequest('fileDescription', FALSE, '');

        $stmt = $this->dbh
                ->prepare(
                        "INSERT INTO metaData (filePath, fileDescription) VALUES (:filePath, :fileDescription)");
        $stmt->bindParam(':filePath', $absPath);
        $stmt->bindParam(':fileDescription', $description);
        $stmt->execute();

        return array();
    }

    function getDescription() {
	requireRequestMethod("POST");

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_FILE);

        $stmt = $this->dbh
                ->prepare(
                        "SELECT fileDescription FROM metaData WHERE filePath=:filePath");
        $stmt->bindParam(':filePath', $absPath);
        $stmt->execute();
        $row = $stmt->fetch();
        if (empty($row)) {
            throw new Exception("no description for this file");
        }
        return array('fileDescription' => $row['fileDescription']);
    }

    function getDirList() {
        requireRequestMethod("GET");

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_DIR);

        if (chdir($absPath) === FALSE) {
            throw new Exception("directory does not exist");
        }

        $dirList = array();
        foreach (glob("*") as $fileName) {
            array_push($dirList, array("fileName" => $fileName,
                "fileSize" => filesize($fileName),
		"fileDate" => filemtime($fileName), 
                "isDirectory" => is_dir($fileName)));
        }
	/* sort the list by fileDate, newest first */
        usort($dirList, function($a, $b) { 
        	return $b['fileDate'] - $a['fileDate'];
	});
        return $dirList;
    }

    function deleteFile() {
        requireRequestMethod("POST");

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_FILE);

        /* delete meta data */
        $stmt = $this->dbh->prepare("DELETE FROM metaData WHERE filePath=:filePath");
        $stmt->bindParam(':filePath', $absPath);
        $stmt->execute();

        /* delete download tokens */
        $stmt = $this->dbh->prepare("DELETE FROM downloadTokens WHERE filePath=:filePath");
        $stmt->bindParam(':filePath', $absPath);
        $stmt->execute();

        if (@unlink($absPath) === FALSE) {
            throw new Exception("unable to delete file");
        }

        return array();
    }

    function deleteDirectory() {
        requireRequestMethod("POST");

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_DIR);

        if (@rmdir($absPath) === FALSE) {
            throw new Exception("unable to delete directory");
        }
        return array();
    }

    function createDirectory() {
        requireRequestMethod("POST");

        $absPath = $this->validatePath(getRequest('relativePath', TRUE), FTS_PARENT);

        /* file or directory with this name should *not* already exist */
        if (file_exists($absPath)) {
            throw new Exception(
                    "directory (or file) with that name already exists");
        }

        if (@mkdir($absPath, 0775) === FALSE) {
            throw new Exception("unable to create directory");
        }

        return array();
    }

    function serverInfo() {
        requireRequestMethod("GET");

        /* determine available disk space on server */
        $df = disk_free_space($this->fsd);

        /* determine IP version (IPv4, IPv6) */
        if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
            $ipVersion = 4;
        } else {
            $ipVersion = 6;
        }

        return array("availableSpace" => $df, 'ipVersion' => $ipVersion,
            'remoteAddr' => $_SERVER['REMOTE_ADDR'],
            'systemDate' => date("c", time()));
    }

    function pingServer() {
        requireRequestMethod("GET");

        return array('message' => 'FTS reporting for duty', 'apiVersion' => API_VERSION, 'displayName' => getConfig($this->config, 'display_name', FALSE, $_SERVER['SERVER_NAME']));
    }

    /**
     * Validate the relative path specified with the request
     * @param string $relativePath the relative path to a file or directory
     * @param enum $validateOption can be either
     * FTS_FILE: validate that the absolute file location is inside the base file storage
     * directory and the file exists
     * FTS_DIR: validate that the absolute directory location is inside the base file storage
     * directory and that the directory exists
     * FTS_PARENT: validate that the absolute directory location of the parent is inside the
     * base file storage directory and that this parent directory exists
     * @return The absolute location of the file or directory when validated
     * @throws Exception on path/option failures
     */
    private function validatePath($relativePath, $validateOption) {
        $fsd = $this->fsd;
        if ($validateOption == FTS_FILE || $validateOption == FTS_DIR) {
            $absPath = realpath($fsd . DIRECTORY_SEPARATOR . $relativePath);
            $fsdPos = strpos($absPath, $fsd, 0);
            if ($fsdPos === FALSE || $fsdPos != 0) {
                throw new Exception("invalid path");
            }
            if (!file_exists($absPath)) {
                throw new Exception("path does not exist");
            }
            if ($validateOption == FTS_FILE && !is_file($absPath)) {
                throw new Exception("path is not a file");
            }
            if ($validateOption == FTS_DIR && !is_dir($absPath)) {
                throw new Exception("path is not a directory");
            }
            return $absPath;
        } else if ($validateOption == FTS_PARENT) {
            /* first validate the parent directory */
            $absPath = $this->validatePath(dirname($relativePath), FTS_DIR);
            /* now validate the file/directory itself */
            $baseName = basename($relativePath);
            if (empty($baseName)) {
                throw new Exception("no empty path allowed");
            }
            if (substr($baseName, 0, 1) === FALSE
                    || substr($baseName, 0, 1) === ".") {
                throw new Exception("invalid name, cannot start with '.'");
            }
            return $absPath . DIRECTORY_SEPARATOR . basename($relativePath);
        } else {
            throw new Exception("invalid validation option");
        }
    }

	/**
	 * Get a unique name when uploading a file if requested file name already
	 * exists.
	 *
	 * Suppose "text.txt" exists already, the suggestion becomes "test (1).txt"
	 * If "test (1).txt" already exists, make it "test (2).txt"
	 *
	 * NOTE: If you want to upload "test (1).txt" and it already exists, it will
	 * become "test (1) (1).txt" though!
	 */
	private function getUniqueName($absPath) {
	    $count = 1;
	    $uploadName = $absPath;
	    while (file_exists($uploadName)) {
	        $dirName = dirname($absPath);
	        $fileName = basename($absPath);
	        $lastDotPos = strrpos($fileName, ".");
	        if ($lastDotPos === FALSE) {
	            $fileBase = $fileName . " (" . $count . ")";
	            $fileExt = '';
	        } else {
	            $fileExt = substr($fileName, $lastDotPos);
	            $fileBase = substr($fileName, 0, $lastDotPos);
	            $fileBase = $fileBase . " (" . $count . ")";
	        }
	        $uploadName = $dirName . DIRECTORY_SEPARATOR . $fileBase . $fileExt;
	        $count++;
	    }
	    return $uploadName;
	}
}

?>
