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
	return $bytes;
}

function getProtocol() {
	return (   isset($_SERVER['HTTPS']) && 
		   !empty($_SERVER['HTTPS']) && 
		   $_SERVER['HTTPS'] !== 'off'
		) ? "https://" : "http://";
}

function generateToken() {
	return bin2hex(openssl_random_pseudo_bytes(16));
}


	/**
	 * Returns the required scaling depending on the input width/height ratio.
	 */
        function scaleVideo($size, $newHeight = 360) {
		if(!is_array($size) && !(sizeof($size) == 2))
			throw new Exception("size should be array containing width,height");

		$width = $size[0];
		$height = $size[1];

		if($height > $newHeight) { 
			$factor = $height / $newHeight;
			$newWidth = round($width / $factor);
			return array('width' => $newWidth, 'height' => $newHeight);
		}else {
			return array('width' => $width, 'height' => $height);
		}
        }

        /**
         * @param command       The command to run
         * @param logFile       The file to write to
         * @param subject       Some extra subject information for in the
         *                      log entry
         */
        function execCommand($command, $logFile = NULL, $subject = '') {
                /* executes a command and returns the output when
                   done executing */
                if($logFile == NULL)
                        $logFile = tempnam(sys_get_temp_dir(), "php");

                $logData  = "**************\n";
                $logData .= "*** $subject\n";
                $logData .= "**************\n";
                $logData .= "--- COMMAND: $command\n";
                $logData .= "--- OUTPUT:\n";
                file_put_contents($logFile, $logData, FILE_APPEND);

                /* redirect all output to tmp file */
                $command .= " >>$logFile 2>>$logFile";
                $op = array();
                $rv = -1;
                exec($command, $op, $rv);

                $logData = "--- RETURN VALUE: $rv\n";
                $logData .= "\n\n";
                file_put_contents($logFile, $logData, FILE_APPEND);
        }

        function isMediaFile($file) {
                if(!file_exists($file) || !is_readable($file))
                        throw new Exception("unable to open file '$file'");
		if(class_exists("ffmpeg_movie")) {
	                $mediaFile = @new ffmpeg_movie($file);
	                return !($mediaFile === FALSE);
		}else {
			return FALSE;
		}
        }

        function analyzeMediaFile($fileName, &$metaData, $still = NULL, $thumbnail = NULL) {
                if(empty($fileName))
                        throw new Exception("file does not exist, cannot be analyzed");

		if(!is_array($metaData) || !array_key_exists('fileType', $metaData))
			throw new Exception("metadata invalid");

                switch($metaData['fileType']) {

                        case "video/quicktime":
                        case "application/ogg":
                        case "video/mp4":
                        case "video/ogg":
                        case "video/webm":
                        case "video/x-ms-asf":
			case "application/octet-stream":
                                /* determine width, height, codecs */
                                if(isMediaFile($fileName)) {
                                        $media = new ffmpeg_movie($fileName, FALSE);
                                        if($media->hasVideo()) {
                                                $metaData['video']['codec'] = $media->getVideoCodec();
                                                $metaData['video']['width'] = $media->getFrameWidth();
                                                $metaData['video']['height'] = $media->getFrameHeight();
                                                $metaData['video']['bitrate'] = $media->getVideoBitRate();
                                                $metaData['video']['framerate'] = $media->getFrameRate();
						$metaData['video']['duration'] = $media->getDuration();
                                        }
                                        if($media->hasAudio()) {
                                                $metaData['audio']['codec'] = $media->getAudioCodec();
                                                $metaData['audio']['bitrate'] = $media->getAudioBitRate();
                                                $metaData['audio']['samplerate'] = $media->getAudioSampleRate();
                                                $metaData['audio']['duration'] = $media->getDuration();
                                        }

					// FIXME: only for video!

					// Create Still
					if($media->hasVideo() && $still != NULL) {
						$fc = $media->getFrameCount();
						$f = $media->getFrame($fc/8);
						//$f->resize($media->getFrameWidth(), $media->getFrameHeight());
						imagepng($f->toGDImage(), $still);
					}

					// FIXME: only for video!

					// Create Thumbnail
					if($media->hasVideo() && $thumbnail != NULL) {
                                                $fc = $media->getFrameCount();
-	                                        $f = $media->getFrame($fc/8);
						$sV = scaleVideo(array($media->getFrameWidth(), $media->getFrameHeight());
-	                                        $f->resize($sV['width'], $sV['height']);
	                                        imagepng($f->toGDImage(), $thumbnail);
					}

					// FIXME: also for audio!

					// Schedule file for transcoding
					$transcode = NULL;
					if(($media->hasVideo() || $media->hasAudio()) && $transcode != NULL) {
						// Video: ffmpeg -f webm -acodec libvorbis -vcodec libvpx -s 640x360 -b 1000000 -i $fileName $transcode[.webm]
						// Audio: ffmpeg -b 96k -f ogg -ac libvorbis -i $fileName $transcode[.ogg]
					}

                                }else {
                                        // not a media file?!
                                }
				break;

                        default:
                                /* no idea about this file, let it go... */
                }
	}

function analyzeFile($fileName) {
        if (empty ($fileName) || !is_string($fileName) || !file_exists($fileName))
                throw new Exception("file does not exist");

        $metaData = array ();
	$metaData['type'] = "file";
        $metaData['fileName'] = basename($fileName);
        $metaData['fileSize'] = filesize($fileName);
        $metaData['fileDate'] = filemtime($fileName);
        $metaData['fileShareGroups'] = array ();
	$metaData['fileDescription'] = '';
	$metaData['fileTags'] = array();

        /* MIME-Type */
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $metaData['fileType'] = finfo_file($finfo, $fileName);

        if(isMediaFile($fileName)) {
		analyzeMediaFile($fileName, $metaData, NULL, NULL);
	}
	return $metaData;
}
?>
