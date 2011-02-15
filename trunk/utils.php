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

function getProtocol() {
	return (   isset($_SERVER['HTTPS']) && 
		   !empty($_SERVER['HTTPS']) && 
		   $_SERVER['HTTPS'] !== 'off'
		) ? "https://" : "http://";
}


	/**
	 * Returns the required scaling depending on the input width/height ratio.
	 */
        function scaleVideo($size, $newHeight = 360) {
		if(!is_array($size) && !(sizeof($size) == 2))
			throw new Exception("size should be array containing width,height");

		$width = $size[0];
		$height = $size[1];

		$factor = $height / $newHeight;
		$newWidth = $width / $factor;
		if($newWidth %2 == 1) $newWidth++;
		return array('width' => (int)$newWidth, 'height' => (int)$newHeight);
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

        function isMediaFile(&$metaData, $filePath) {
	        if (!is_array($metaData) || !array_key_exists('fileName', $metaData))
        	        throw new Exception("meta data invalid");
	        $file = $filePath . DIRECTORY_SEPARATOR . $metaData['fileName'];

                if(!is_file($file) || !is_readable($file))
                        throw new Exception("unable to open file");
		if(class_exists("ffmpeg_movie")) {
	                $mediaFile = @new ffmpeg_movie($file);
	                return !($mediaFile === FALSE);
		}else {
			return FALSE;
		}
        }

        function analyzeMediaFile(&$metaData, $filePath = NULL, $cachePath = NULL) {
		if (!is_array($metaData) || !array_key_exists('fileName', $metaData) || !array_key_exists('fileType', $metaData))
                	throw new Exception("meta data invalid");
	        $file = $filePath . DIRECTORY_SEPARATOR . $metaData['fileName'];

	        if(!is_file($file) || !is_readable($file))
        	        throw new Exception("file does not exist");

		if(!is_dir($cachePath))
			throw new Exception("cache dir does not exist");

                switch($metaData['fileType']) {
                        case "video/quicktime":
                        case "application/ogg":
                        case "video/mp4":
                        case "video/ogg":
                        case "video/webm":
                        case "video/x-ms-asf":
			case "application/octet-stream":
                                /* determine width, height, codecs */
                                if(isMediaFile($metaData, $filePath)) {
                                        $media = new ffmpeg_movie($file, FALSE);
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

					// Thumbnails

					// FIXME: this should be fixed by generating a still and resizing that to
					// wanted sizes instead of accessing the thrice! BUG in php-ffmpeg
					// because you can't call $f->resize twice on the same object as the
					// image corrupts...
					$thumbSizes = array(90, 180, 360);
					if($media->hasVideo()) {
						foreach($thumbSizes as $tS) {
		                                	$fc = (int) ($media->getFrameCount() / 32);
							/* frame count is not necessarily reliable! */
							foreach (array($fc, 100, 10, 1) as $fno) {
				                                $f = $media->getFrame($fno);
								if($f !== FALSE) {
	                                                                $sV = scaleVideo(array($media->getFrameWidth(), $media->getFrameHeight()), $tS);
	                                                                $f->resize($sV['width'], $sV['height']);
	                                                                $thumbFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".png";
	                                                                $metaData['video']['thumbnail'][$tS] = array ('file' => basename($thumbFile), 'width' => $sV['width']);
	                                                                imagepng($f->toGDImage(), $thumbFile);
									break;
								}
							}
						}
					}

					$transcodeSizes = array(360);
					// Schedule for transcoding to WebM
					if($media->hasVideo()) {
                                                $metaData['video']['transcodeStatus'] = 'WAITING';
                                                foreach($transcodeSizes as $tS) {
							$transcodeFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".webm";
                                                        $sV = scaleVideo(array($media->getFrameWidth(), $media->getFrameHeight()), $tS);
							$metaData['video']['transcode'][$tS] = array ( 'file' => basename($transcodeFile), 'width' => $sV['width']);
						}
					}
                                }else {
                                        // not a media file?!
                                }
				break;

                        default:
                                /* no idea about this file, let it go... */
                }
	}

function analyzeFile(&$metaData, $filePath = NULL, $cachePath = NULL) {
	if (!is_array($metaData) || !array_key_exists('fileName', $metaData))
		throw new Exception("meta data invalid");
	$file = $filePath . DIRECTORY_SEPARATOR . $metaData['fileName'];

	if(!is_file($file) || !is_readable($file))
                throw new Exception("file does not exist");

	$metaData['type'] = "file";
        $metaData['fileSize'] = filesize($file);
        $metaData['fileDate'] = filemtime($file);
        $metaData['fileGroups'] = array ();
	$metaData['fileDescription'] = '';
	$metaData['fileLicense'] = 'none';
	$metaData['filePublic'] = FALSE;
	$metaData['fileTags'] = array();

        /* MIME-Type */
	$finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
        $metaData['fileType'] = $finfo->file($file);

        if(isMediaFile($metaData, $filePath)) {
		analyzeMediaFile($metaData, $filePath, $cachePath);
	}
}

?>
