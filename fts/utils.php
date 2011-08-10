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

/**
 * This function extracts the specified HTTP header as used by the client to contact this service.
 *
 * @param string $name the name of the HTTP header
 * @param boolean $required boolean indicating whether the header is required, if the value is empty (that could mean 0!) the default value will be used
 * @param mixed $default if the header is not required, specify the default value it will get if it does not exist
 *
 * @throws Exception in case the header is required but does not exist or no headers are available
 */
function getHeader($name = NULL, $required = FALSE, $default = NULL) {
    $hdrs = getallheaders();
    if ($hdrs === FALSE) {
        throw new Exception("unable to get headers");
    }
    if ($name == NULL || empty($name)) {
        throw new Exception("no variable specified or empty");
    }
    if ($required) {
        if (!isset($hdrs[$name]) || empty($hdrs[$name])) {
            throw new Exception("$name not available while required");
        }
        return $hdrs[$name];
    } else {
        if (isset($hdrs[$name]) && !empty($hdrs[$name])) {
            return $hdrs[$name];
        } else {
            return $default;
        }
    }
}

function getRequest($name = NULL, $required = FALSE, $default = NULL) {
    if (!isset($_REQUEST)) {
        throw new Exception("unable to get parameters");
    }
    if ($name == NULL || empty($name)) {
        throw new Exception("no variable specified or empty");
    }
    if ($required) {
        if (!isset($_REQUEST[$name]) || empty($_REQUEST[$name])) {
            throw new Exception("$name not available while required");
        }
        return $_REQUEST[$name];
    } else {
        if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) {
            return $_REQUEST[$name];
        } else {
            return $default;
        }
    }
}

function getConfig($config = array(), $variable = NULL, $required = FALSE, $default = NULL) {
    if (!is_array($config)) {
        throw new Exception(
                "no usable configuration array, broken or missing config file?");
    }
    if ($variable === NULL || empty($variable)) {
        throw new Exception("no variable specified or empty");
    }
    if ($required) {
        if (!isset($config[$variable])) {
            throw new Exception("$variable not available while required");
        }
        return $config[$variable];
    }
    if (isset($config[$variable])) {
        return $config[$variable];
    }
    return $default;
}

function getProtocol() {
    if (!isset($_SERVER['SERVER_NAME'])) {
        throw new Exception('not called through web server');
    }
    return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
}

function generateToken($length = 16) {
    return bin2hex(openssl_random_pseudo_bytes($length));
}

/**
 * Return the server name and add brackets in case an IPv6 address is used, so
 * ::1 becomes [::1] for example
 */
function getServerName() {
    if (!isset($_SERVER['SERVER_NAME'])) {
        throw new Exception('not called through web server');
    }
    $serverName = $_SERVER['SERVER_NAME'];

    if (filter_var($serverName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
        return $serverName;
    } else {
        return '[' . $serverName . ']';
    }
}

function requireRequestMethod($method = "GET", $errorCode = 0) {
	if(!is_array($method)) {
		$method = array($method);
	}

	$validMethods = array_intersect($method, array("GET","POST","PUT","DELETE","OPTIONS"));
	if(empty($validMethods)) {
 		throw new Exception("invalid request method(s) specified", $errorCode);
	}
	if(!in_array($_SERVER['REQUEST_METHOD'], $validMethods)) {
		throw new Exception("invalid request method used, require [" . implode(" or ", $validMethods) . "]");
	}
}

?>
