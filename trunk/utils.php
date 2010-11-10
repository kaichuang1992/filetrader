<?php
	function getRequest($variable = NULL, $required = FALSE, $default = NULL) {
		if(!isset($_REQUEST))
			throw new Exception("no request available, not called using browser?");
		if($variable === NULL || empty($variable))
			throw new Exception("no variable specified or empty");
		if($required) {
			if(!isset($_REQUEST[$variable]))
				throw new Exception("$variable not available while required");
			return $_REQUEST[$variable];
		}
		if(isset($_REQUEST[$variable]))
			return $_REQUEST[$variable];
		return $default;
	}

	function logHandler($message) {
		if(isset($_SERVER['REMOTE_ADDR']))
			$caller = $_SERVER['REMOTE_ADDR'];
		else
			$caller = 'php-cli';
		file_put_contents('data/app.log', "---[".$caller." @ ".date("c",time())."]---\n".$message."\n", FILE_APPEND);
		return $message;
	}

	function analyzeFile($fileName) {
		if(empty($fileName) || !is_string($fileName) || !file_exists($fileName))
			throw new Exception("file does not exist");
                $metaData = array();
		$metaData['fileName'] = basename($fileName);
		$metaData['fileSize'] = filesize($fileName);
		$metaData['fileDate'] = filemtime($fileName);
		$metaData['downloadTokens'] = array();
                $metaData['shareGroups'] = array();

		/* MIME-Type */
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$metaData['fileType'] = finfo_file($finfo, $fileName);
		return $metaData;
	}

        function return_bytes($val) {
                $val = trim($val);
                $last = strtolower($val[strlen($val)-1]);
                switch($last) {
                        // The 'G' modifier is available since PHP 5.1.0
                        case 'g':
                                $val *= 1024;
                        case 'm':
                                $val *= 1024;
                        case 'k':
                                $val *= 1024;
                }
                return $val;
        }

	function bytesToHuman($bytes) {
		$kilobyte = 1024;
		$megabyte = $kilobyte*$kilobyte;
		$gigabyte = $megabyte*$megabyte;

		if($bytes > $gigabyte)
			return (int)($bytes/$gigabyte) . "GB";
		if($bytes > $megabyte)
			return (int)($bytes/$megabyte) . "MB";
		if($bytes > $kilobyte)
			return (int)($bytes/$kilobyte) . "kB";
		return $bytes . " bytes";
	}

	function generateToken() {
		return bin2hex(openssl_random_pseudo_bytes(8));
	}
?>
