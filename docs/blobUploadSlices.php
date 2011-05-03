<?php
$targetDir = sys_get_temp_dir();
$httpHeaders = getallheaders();
if(array_key_exists('X-Requested-With', $httpHeaders) && $httpHeaders['X-Requested-With'] === "XMLHttpRequest" && array_key_exists('X-File-Name', $httpHeaders) && array_key_exists('X-File-Size', $httpHeaders)) {
	$fileName = basename($httpHeaders['X-File-Name']);
	$fileSize = $httpHeaders['X-File-Size'];

        $fileChunk = 0;
        if(array_key_exists('X-File-Chunk', $httpHeaders))
                $fileChunk = $httpHeaders['X-File-Chunk'];

        $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $fileChunk == 0 ? "wb" : "ab");
        if ($out) {
                $in = fopen("php://input", "rb");
                if ($in) {
                        while ($buffer = fread($in, 4096))
                        fwrite($out, $buffer);
                } else {
                       // FIXME: failed to open input stream
                }
                fclose($in);
                fclose($out);
		flush();

		/* only check file when upload is complete */
		if($fileSize == filesize($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			// upload completed!
		}
	} else {
        	// FIXME: failed to open output stream
	}
	exit(0);	
}
?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>File Upload</title>
</head>
<body>
<h1>File Upload</h1>
<input id="inputFiles" type="file" onchange="uploadFile(this.files[0])">
<script>
var blockSize = 1024*1024;

function uploadFile(file) {
	var bytesLeft = file.size;
	var currentChunk = 0;
	var transferLength;
	var blob;
	var xhr;
	var reader;

	if(bytesLeft > 0) {
		transferLength = (blockSize > bytesLeft) ? bytesLeft : blockSize;
		reader = new FileReader();
		reader.onload = function(evt) {
			xhr = new XMLHttpRequest();
			xhr.upload.onload = function(evt) {
				bytesLeft -= transferLength;
				if(bytesLeft > 0) {
					transferLength = (blockSize > bytesLeft) ? bytesLeft : blockSize;
					currentChunk++;
					if(file.slice)
						blob = file.slice(currentChunk*blockSize, transferLength);
					if(file.mozSlice)
						blob = file.mozSlice(currentChunk*blockSize, currentChunk*blockSize+transferLength);
					if(file.webkitSlice)
						blob = file.webkitSlice(currentChunk*blockSize, currentChunk*blockSize+transferLength);
					reader.readAsBinaryString(blob);
				} else {
					alert("File Upload Done!");
				}
			}
			xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME'];?>', true);
			xhr.setRequestHeader("X-File-Name", file.name);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("X-File-Size", file.size);
			xhr.setRequestHeader("X-File-Chunk", currentChunk);
			xhr.send(blob);
		}
		if(file.slice)
			blob = file.slice(0, transferLength);
		if(file.mozSlice)
			blob = file.mozSlice(0, currentChunk*blockSize+transferLength);
		if(file.webkitSlice)
			blob = file.webkitSlice(0, currentChunk*blockSize+transferLength);		
		reader.readAsBinaryString(blob);
	} else {
		alert("File Empty!");
	}		
}
</script>
</body>
</html>
