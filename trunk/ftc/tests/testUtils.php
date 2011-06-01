<?php

function uploadFile($uploadUrl, $filePath, $blockSize = 1024) {
	$filePath = realpath($filePath);

	/* determing file mime-type */
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$contentType = $finfo->file($filePath);
	$fileSize = filesize($filePath);

	$fp = fopen($filePath, "rb");

	/* Perform chunked file uploading */
	for ($i = 0; $i * $blockSize < $fileSize; $i++) {
		if (($i + 1) * $blockSize > $fileSize) {
			$size = $fileSize - ($i * $blockSize);
		} else {
			$size = $blockSize;
		}
		$data = fread($fp, $size);
		$opts = array(
				'http' => array('method' => 'PUT',
						'header' => array('Content-type: ' . $contentType,
								"X-File-Chunk: $i"), 'content' => $data));
		$context = stream_context_create($opts);
		$uploadResponse = json_decode(
				file_get_contents($uploadUrl, false, $context));
		if (!$uploadResponse->ok) {
			return (object) array("ok" => FALSE);
		}
	}
	return (object) array("ok" => TRUE);
}

function downloadFile($downloadUrl, $orignalFile = NULL) {
	/* don't actually return the downloaded content, just ok! */
	$downloadResponse = file_get_contents($downloadUrl, false);
	/* compare original with downloaded content to see if that actually matches in 
	 * case you want to test downloading the same file as the one you uploaded... */
	if ($orignalFile != NULL) {
		$original = sha1_file($orignalFile);
		$download = sha1($downloadResponse);

		if ($original !== FALSE && $download !== FALSE
				&& $original === $download) {
			return (object) array("ok" => TRUE);
		} else {
			return (object) array("ok" => FALSE,
					"errorMessage" => "files are not equal");
		}
	}
	return (object) array("ok" => TRUE);
}

function handleResponse($testName, $response) {
	if (!$response->ok) {
		echo "[FAILED] $testName <<<< ERROR: $response->errorMessage >>>>\n";
	} else {
		echo "[    OK] $testName\n";
	}
}

function showDirectoryListing($data) {
	foreach ($data->files as $fileName => $fileMetaData) {
		echo $fileName . "\t"
				. (($fileMetaData->isDirectory) ? "[DIR]"
						: $fileMetaData->fileSize) . "\n";
	}
}
?>
