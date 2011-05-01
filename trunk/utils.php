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

function getConfig($config = array (), $variable = NULL, $required = FALSE, $default = NULL) {
	if (!is_array($config))
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
	if(!isset($_SERVER['SERVER_NAME']))
		return FALSE;
	return (isset ($_SERVER['HTTPS']) && !empty ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
}

function scaleVideo($width_orig = 640, $height_orig = 480, $max_size = 360) {
        $width = $max_size;
        $height = $max_size;

        $ratio_orig = $width_orig/$height_orig;
        if ($width/$height > $ratio_orig) {
                $width = (int)($height*$ratio_orig);
        } else {
                $height = (int)($width/$ratio_orig);
        }

	/* make width and height an even number */
        if ($width % 2 == 1)
                $width++;
        if ($height % 2 == 1)
                $height++;

	return array($width, $height);
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
	if ($logFile == NULL)
		$logFile = tempnam(sys_get_temp_dir(), "php");

	$logData = "**************\n";
	$logData .= "*** $subject\n";
	$logData .= "**************\n";
	$logData .= "--- COMMAND: $command\n";
	$logData .= "--- OUTPUT:\n";
	file_put_contents($logFile, $logData, FILE_APPEND);

	/* redirect all output to tmp file */
	$command .= " >>$logFile 2>>$logFile";
	$op = array ();
	$rv = -1;
	exec($command, $op, $rv);

	$logData = "--- RETURN VALUE: $rv\n";
	$logData .= "\n\n";
	file_put_contents($logFile, $logData, FILE_APPEND);
}

function isMediaFile(& $metaData, $filePath) {
	if (!isset($metaData->fileName))
		throw new Exception("meta data invalid");
	$file = $filePath . DIRECTORY_SEPARATOR . $metaData->fileName;

	if (!is_file($file) || !is_readable($file))
		throw new Exception("unable to open file");
	if (class_exists("ffmpeg_movie")) {
		$mediaFile = @ new ffmpeg_movie($file);
		return !($mediaFile === FALSE);
	} else {
		return FALSE;
	}
}

function analyzeMediaFile(& $metaData, $filePath = NULL, $cachePath = NULL) {
	if (!isset($metaData->fileName) || !isset($metaData->fileType))
		throw new Exception("meta data invalid");
	$file = $filePath . DIRECTORY_SEPARATOR . $metaData->fileName;

	if (!is_file($file) || !is_readable($file))
		throw new Exception("file does not exist");

	if (!is_dir($cachePath))
		throw new Exception("cache dir does not exist");

	switch ($metaData->fileType) {
		case "video/quicktime" :
		case "application/ogg" :
		case "video/mp4" :
		case "video/x-msvideo" :
		case "video/ogg" :
		case "video/webm" :
		case "video/x-ms-asf" :
		case "video/x-flv" :
		case "application/octet-stream" :
			if (isMediaFile($metaData, $filePath)) {
				$media = new ffmpeg_movie($file, FALSE);

				/****************************************************
				 * VIDEO
				 ****************************************************/
				if ($media->hasVideo()) {
					$metaData->video->codec = $media->getVideoCodec();
					$metaData->video->width = $media->getFrameWidth();
					$metaData->video->height = $media->getFrameHeight();
					$metaData->video->bitrate = $media->getVideoBitRate();
					$metaData->video->framerate = $media->getFrameRate();
					$metaData->video->duration = $media->getDuration();

                                        /* Video Still */
                                        $stillFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".png";

                                        $fc = (int) ($media->getFrameCount() / 32);
                                        /* frame count is not necessarily reliable! */
                                        foreach (array ( $fc, 100, 10, 1) as $fno) {
                                                if ($fno == 0)
                                                        continue;
                                                $f = $media->getFrame($fno);
                                                if($f !== FALSE) {
                                                        imagepng($f->toGDImage(), $stillFile);
                                                        $metaData->video->still = basename($stillFile);
                                                        break;
                                                }
                                        }

                                        /* Video Thumbnails */
                                        $thumbSizes = array(90, 180, 360);
                                        foreach($thumbSizes as $tS) {
                                                $thumbFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".png";
                                                list($thumb_width, $thumb_height) = generateThumbnail($stillFile, $thumbFile, $tS);
                                                $metaData->video->thumbnail->$tS->file = basename($thumbFile);
                                                $metaData->video->thumbnail->$tS->width = $thumb_width;
                                                $metaData->video->thumbnail->$tS->height = $thumb_height;
                                        }

					/* Schedule for transcoding */
	                                $transcodeSizes = array (360);
                                        $metaData->transcodeStatus = 'WAITING';
                                        $metaData->transcodeProgress = 0;
	
	                                foreach ($transcodeSizes as $tS) {
	                                	$transcodeFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".webm";
	                                        list($width, $height) = scaleVideo($media->getFrameWidth(), $media->getFrameHeight(), $tS);
	                                        $metaData->video->transcode->$tS->file = basename($transcodeFile);
	                                        $metaData->video->transcode->$tS->width = $width;
                                                $metaData->video->transcode->$tS->height = $height;
	                                }
				}

                                /****************************************************
                                 * AUDIO
                                 ****************************************************/
				if ($media->hasAudio()) {
					$metaData->audio->codec = $media->getAudioCodec();
					$metaData->audio->bitrate = $media->getAudioBitRate();
					$metaData->audio->samplerate = $media->getAudioSampleRate();
					$metaData->audio->duration = $media->getDuration();

                                        $metaData->transcodeStatus = 'WAITING';
                                        $metaData->transcodeProgress = 0;

                                        $transcodeFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".ogg";
                                        $metaData->audio->transcode->file = basename($transcodeFile);
				}
			} else {
				/* No media? */
			}
			break;

		case "image/jpeg":
		case "image/png":
		case "image/gif":
		case "image/bmp":
                        list($width, $height) = getimagesize($file);
                        $metaData->image->width = $width;
                        $metaData->image->height = $height;
			$thumbFile = $cachePath . DIRECTORY_SEPARATOR . uniqid("ft_") . ".png";
			list($thumb_width, $thumb_height) = generateThumbnail($file, $thumbFile, 360);
			$metaData->image->thumbnail->{360}->file = basename($thumbFile);
			$metaData->image->thumbnail->{360}->width = $thumb_width;
                        $metaData->image->thumbnail->{360}->height = $thumb_height;
			break;

		default :
			/* no idea about this file, let it go... */
	}
}

function analyzeFile(& $metaData, $filePath = NULL, $cachePath = NULL) {
	if(!isset($metaData->fileName))
		throw new Exception("meta data invalid");

	$file = $filePath . DIRECTORY_SEPARATOR . $metaData->fileName;
	if (!is_file($file) || !is_readable($file))
		throw new Exception("file does not exist");

	$metaData->type = "file";
	$metaData->fileSize = filesize($file);
	$metaData->fileDate = filemtime($file);
	$metaData->fileSHA1 = sha1_file($file);

	/* MIME-Type */
	$finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
	$metaData->fileType = $finfo->file($file);

	if (isMediaFile($metaData, $filePath)) {
		analyzeMediaFile($metaData, $filePath, $cachePath);
	}
}

function generateToken() {
	return bin2hex(openssl_random_pseudo_bytes(8));
}

function generateThumbnail($in_file = NULL, $out_file = NULL, $max_size = 360) {
	list($width_orig, $height_orig) = getimagesize($in_file);

	$width = $max_size;
	$height = $max_size;

	$ratio_orig = $width_orig/$height_orig;
	if ($width/$height > $ratio_orig) {
		$width = (int)($height*$ratio_orig);
	} else {
		$height = (int)($width/$ratio_orig);
	}
	$image_p = imagecreatetruecolor($width, $height);
	$image = imagecreatefromstring(file_get_contents($in_file));
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	imagepng($image_p, $out_file);
	return array($width, $height);
}

?>
