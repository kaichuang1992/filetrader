<?php
$httpHeaders = getallheaders();
if(array_key_exists('X-Request-With', $httpHeaders) && 
   $httpHeaders['X-Requested-With'] === "XMLHttpRequest" &&
   array_key_exists('X-File-Name', $httpHeaders)) {
	$fileName = basename($httpHeaders['X-File-Name']);
        $path = sys_get_temp_dir();
        $out = fopen($path . DIRECTORY_SEPARATOR . $fileName, "wb");
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
<h1>File Upload</h1>
<input id="inputFiles" type="file" onchange="setFiles(this.files)" multiple="multiple">
<button onclick="startUpload()">Upload Files</button>
<span id="progress"></span>
<script>
	var files;

	function setFiles(f) {
		files = f;
	}

        function startUpload() {
                for (var i = 0; i < files.length; i++) {
                        performUpload(files[i]);
                }
        }

        function performUpload(file) {
		var xhr = new XMLHttpRequest();
		xhr.upload.addEventListener("progress", function(evt) { 
	                if (evt.lengthComputable) {
	                        document.getElementById("progress").textContent = Math.round(evt.loaded * 100 / evt.total) + "%";
	                }
		}, false);
                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>', true);
                xhr.setRequestHeader("X-File-Name", file.name);
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
               	xhr.send(file);
	}
</script>
</body>
</html>
