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
            return (object) array("ok" => FALSE,
                "errorMessage" => $uploadResponse->errorMessage);
        }
    }
    return (object) array("ok" => TRUE,
        "numberOfChunks" => ($fileSize / $blockSize) + 1);
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

function handleNegativeResponse($testName = "test", $debug = FALSE, $response) {
    return handleResponse($testName, $debug, $response, TRUE);
}

function handleResponse($testName = "test", $debug = FALSE, $response, $negative = FALSE) {
    if(file_exists("response_cache.txt")) {
	    $responses = json_decode(file_get_contents("response_cache.txt"));
    } else {
	    $responses = json_decode('{}');
    }
    if(isset($responses->$testName)) {
	/* compare */
        if(json_encode($responses->$testName) !== json_encode($response)) {
		echo "**** MISMATCH $testName ****\n";
	        echo json_encode($responses->$testName) . "\n";
	        echo json_encode($response) . "\n";
		echo "**** END OF MISMATCH $testName ****\n";
	}
    } else {
	$responses->$testName = $response;
    }
    file_put_contents("response_cache.txt", json_encode($responses));

    if ((!$negative && !$response->ok) || ($negative && $response->ok)) {
        echo "[FAILED] $testName";
        if (!$negative) {
            echo " <<<< ERROR: $response->errorMessage >>>>";
        }
        echo "\n";
    } else {
        echo "[    OK] $testName";
        if ($negative) {
            echo " <<<< EXPECTED ERROR: $response->errorMessage >>>>";
        }
        echo "\n";
    }
    
    if ($debug) {
        echo "----     OUTPUT [$testName]    -----\n";
        var_dump($response);
        echo "---- END OF OUTPUT [$testName] -----\n";
    }
    return $response;
}

function showDirectoryListing($data) {
    foreach ($data->files as $fileName => $fileMetaData) {
        echo $fileName . "\t"
        . (($fileMetaData->isDirectory) ? "[DIR]" : $fileMetaData->fileSize) . "\n";
    }
}

?>
