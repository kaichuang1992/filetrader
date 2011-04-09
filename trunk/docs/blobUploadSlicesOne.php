<?php
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'upload') {
	$path = sys_get_temp_dir();
	$fileName = (isset($_REQUEST['fileName'])) ? $_REQUEST['fileName'] : 'file.dat';
	$chunk = (isset($_REQUEST['chunk'])) ? $_REQUEST['chunk'] : 0;
        $out = fopen($path . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");

        if ($out) {
                $in = fopen("php://input", "rb");
                if ($in) {
                        while ($buffer = fread($in, 4096))
                        fwrite($out, $buffer);
                } else {
                       //  failed to open input stream
                }
                fclose($in);
                fclose($out);

        } else {
                // failed to open output stream
        }
	exit(0);
}
?>
<!DOCTYPE html>
<head>
<meta charset="utf8">
<title>FileAPI</title>
</head>
<body>
Files to upload: <input id="inputFiles" type="file" onchange="handleFiles(this.files)" multiple>
<script>
        function handleFiles(files) {
                for (var i = 0; i < files.length; i++) {
                        fileUpload(files[i]);
                }
        }

	function fileUpload(f) {
		var blockSize = 1024;
		var bytesLeft = f.size;
		var currentChunk = 0;
		var transferLength;
                while(bytesLeft > 0) {
			alert(bytesLeft);
			if(blockSize > bytesLeft) {
				transferLength = bytesLeft;
			} else {
				transferLength = blockSize;
			}
			var reader = new FileReader();
			reader.onloadend = function(evt) {
				if (evt.target.readyState == FileReader.DONE) {
			                var xhr = new XMLHttpRequest();
			                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>?action=upload&fileName='+f.name+'&chunk='+currentChunk, false);
					xhr.setRequestHeader("Content-Length", length);
					//xhr.setRequestHeader('Content-Type', 'application/octet-stream');

			                xhr.sendAsBinary(evt.target.result);
					//bytesLeft -= transferLength;
					//currentChunk++;
				};
			}
			var blob = f.slice(currentChunk*blockSize, transferLength);
			reader.readAsBinaryString(blob);
                        bytesLeft -= transferLength;
                        currentChunk++;
		}
	}
</script>
</body>
</html>
