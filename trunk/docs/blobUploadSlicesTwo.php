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
                        upload(files[i]);
                }
        }

        function upload(f) {
		fileUpload(f, 0, 1024*1024, 0);
	}

	function fileUpload(f, start, length, chunk) {
		var bytesLeft = f.size - start;
                if(bytesLeft > 0) {
			if(length > bytesLeft)
				length = bytesLeft;
			var reader = new FileReader();
			reader.onloadend = function(evt) {
				if (evt.target.readyState == FileReader.DONE) {
					uploadSlice(evt.target.result, f, start, length, chunk);
				}
			};
			var blob = f.slice(start, length);
			reader.readAsBinaryString(blob);
		}
	}

	function uploadSlice(dataSlice, f, start, length, chunk) {
		var xhr = new XMLHttpRequest();
                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>?action=upload&fileName='+f.name+'&chunk='+chunk, false);
		xhr.setRequestHeader("Content-Length", length);
		xhr.setRequestHeader('Content-Type', 'application/octet-stream');
               	xhr.sendAsBinary(dataSlice);
		fileUpload(f, start+length, length, chunk+1);
	}
</script>
</body>
</html>
