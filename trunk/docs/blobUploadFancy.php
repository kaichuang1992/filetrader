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
<meta charset="utf-8">
<title>FileAPI</title>
</head>
<body>
<h1>FileAPI File Uploader</h1>
<input id="inputFiles" type="file" onchange="handleFiles(this.files)" multiple>
<button id="startButton" onclick="startUpload()" disabled>Start Upload</button>
<button id="stopButton" onclick="stopUpload()" disabled>Stop Upload</button>
<ul id="progress">
</ul>
<span id="result"></span>
<script>
	var files; /* keep track of the files to upload */
	var xhrs; /* keep track of all xhrs to be able to stop them */
	var done; /* keep track of the number of files done uploading */
	var startTime;

	/* add the files to the upload list */
        function handleFiles(f) {
		files = f;
                var progress = document.getElementById('progress');
		progress.innerHTML = '';
		for (var i = 0; i < files.length; i++) {
			var li = document.createElement('li');
			li.innerHTML = files[i].name + ' (<strong><span style="color: blue" id="file_progress_' + i + '">0%</span></strong>)';
			progress.appendChild(li);
		}
		if(files.length != 0) {
			document.getElementById('startButton').removeAttribute('disabled');
		}
	}

	function startUpload() {
		startTime = new Date().getTime();
		xhrs = new Array();
		done = 0;
		if(files != null) {
                        document.getElementById('startButton').setAttribute('disabled','disabled');
                        document.getElementById('stopButton').removeAttribute('disabled');

	                for (var i = 0; i < files.length; i++) {
	                        upload(i,files[i]);
	                }
		}
        }

	function stopUpload() {
		for(var i = 0; i < xhrs.length; i++) {
			xhrs[i].abort();
		}
                document.getElementById('stopButton').setAttribute('disabled','disabled');
	}

        function upload(index,file) {
		var xhr = new XMLHttpRequest();
		xhrs.push(xhr);

		/* during upload */
		xhr.upload.addEventListener("progress", function(evt) { 
	                if (evt.lengthComputable) {
	                        var percentComplete = evt.loaded / evt.total;
	                        document.getElementById('file_progress_'+index).textContent = Math.round((evt.loaded * 100) / evt.total) + "%";
	                }
		}, false);

		/* when upload is complete */
		xhr.upload.addEventListener("load", function(evt) {
			document.getElementById('file_progress_'+index).innerHTML = '<span style="color: green">Done</span>';
			/* if this was the last file, disable stop button */
			if(++done == files.length) {
		                document.getElementById('stopButton').setAttribute('disabled','disabled');
				var endTime = new Date().getTime();
				var elapsedTime = (endTime - startTime) / 1000;
				var totalSize = 0;
				for(var i=0;i<files.length;i++)
					totalSize+=files[i].size;
				document.getElementById('result').textContent = "All done! Transfered " + totalSize + " bytes in " + elapsedTime + " seconds (" + Math.round((totalSize / elapsedTime) / 1024) + "kB/s)";
			}
		}, false);

                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>?action=upload&fileName='+file.name, true);
               	xhr.send(file);
	}
</script>
</body>
</html>
