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

function getRequest($variable = NULL, $required = FALSE, $default = NULL) {
	if (!isset ($_REQUEST))
		throw new Exception("no request available, not called using browser?");
	if ($variable === NULL || empty ($variable))
		throw new Exception("no variable specified or empty");
	if ($required) {
		if (!isset ($_REQUEST[$variable]))
			throw new Exception("$variable not available while required");
		return $_REQUEST[$variable];
	}
	if (isset ($_REQUEST[$variable]))
		return $_REQUEST[$variable];
	return $default;
}

function getConfig($config = array(), $variable = NULL, $required = FALSE, $default = NULL) {
	if(!is_array($config))
		throw new Exception("no usable configuration array, broken or missing config file?");
        if ($variable === NULL || empty ($variable))
                throw new Exception("no variable specified or empty");
        if ($required) {
                if (!isset ($config[$variable]))
                        throw new Exception("$variable not available while required");
                return $config[$variable];
        }
        if (isset ($config[$variable]))
                return $config[$variable];
        return $default;
}

function logHandler($message) {
	if (isset ($_SERVER['REMOTE_ADDR']))
		$caller = $_SERVER['REMOTE_ADDR'];
	else
		$caller = 'php-cli';
	file_put_contents('data/app.log', date("c", time()) . " " . $caller . ": " . $message . "\n", FILE_APPEND);
	return $message;
}

function analyzeFile($fileName) {
	if (empty ($fileName) || !is_string($fileName) || !file_exists($fileName))
		throw new Exception("file does not exist");
	$metaData = array ();
	$metaData['fileName'] = basename($fileName);
	$metaData['fileSize'] = filesize($fileName);
	$metaData['fileDate'] = filemtime($fileName);
	$metaData['downloadTokens'] = array ();
	$metaData['shareGroups'] = array ();

	/* MIME-Type */
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$metaData['fileType'] = finfo_file($finfo, $fileName);
	return $metaData;
}

function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	switch ($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g' :
			$val *= 1024;
		case 'm' :
			$val *= 1024;
		case 'k' :
			$val *= 1024;
	}
	return $val;
}

function bytesToHuman($bytes) {
	$kilobyte = 1024;
	$megabyte = $kilobyte * $kilobyte;
	$gigabyte = $megabyte * $megabyte;

	if ($bytes > $gigabyte)
		return (int) ($bytes / $gigabyte) . "GB";
	if ($bytes > $megabyte)
		return (int) ($bytes / $megabyte) . "MB";
	if ($bytes > $kilobyte)
		return (int) ($bytes / $kilobyte) . "kB";
	return $bytes . " bytes";
}

function generateToken() {
	return bin2hex(openssl_random_pseudo_bytes(16));
}
?>
