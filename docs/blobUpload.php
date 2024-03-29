<?php
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'upload') {
	$path = sys_get_temp_dir();
	$fileName = (isset($_REQUEST['fileName'])) ? $_REQUEST['fileName'] : 'file.dat';
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
<p>Files to upload: <input id="inputFiles" type="file" onchange="handleFiles(this.files)" multiple></p>
<p>Progress: <span id="progress">0</span>%</p>
<script>
        function handleFiles(files) {
                for (var i = 0; i < files.length; i++) {
                        upload(files[i]);
                }
        }

        function upload(file) {
		var xhr = new XMLHttpRequest();
		xhr.upload.addEventListener("progress", function(evt) { 
	                if (evt.lengthComputable) {
	                        var percentComplete = evt.loaded / evt.total;
	                        document.getElementById("progress").textContent = "(" + file.name + "):" + Math.round((evt.loaded * 100) / evt.total);
	                }
		}, false);
                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>?action=upload&fileName='+file.name, true);
               	xhr.send(file);
	}
</script>
</body>
</html>
