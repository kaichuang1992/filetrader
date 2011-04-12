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
<title>File Upload</title>
<style type="text/css">
	table { 
		width: 100%;
		border: 1px solid #000; 
	}
	
	tfoot td {
		border-top: 1px dashed #000;
		padding-top: 5px;
	}

	th { 
		text-align: left;
	}
	
	td.noFiles {
		text-align: center;
		font-size: 85%;
		font-style: italic;
	}
	span.done {
		color: green;
	}
	span.cancelled {
		color: blue;
	}
</style>
</head>
<body>
<h1>File Upload</h1>
<table>
	<thead>
		<tr><th>File Name</th><th>File Size</th><th>Progress</th></tr>
	</thead>
	<tbody id="fileList">
		<tr><td class="noFiles" colspan="3">No files selected yet...</td></tr>
	</tbody>
	<tfoot>
		<tr><td colspan="2">
			<input id="inputFiles" type="file" onchange="handleFiles(this.files)" multiple>
			<button id="startButton" onclick="startUpload()" disabled>Start Upload</button>
			<button id="stopButton" onclick="stopUpload()" disabled>Stop Upload</button>
		</td>
		<td><span id="result"></span></td>
		</tr>
	</tfoot>
</table>

<script>
	var files; /* keep track of the files to upload */
	var xhrs; /* keep track of all xhrs to be able to stop them */
	var done; /* keep track of the number of files done uploading */
	var startTime;

	/* add the files to the upload list */
        function handleFiles(f) {
		files = f;
                var fileList = document.getElementById('fileList');
		document.getElementById('result').textContent = '';
		fileList.innerHTML = '';
		for (var i = 0; i < files.length; i++) {
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + files[i].name + '</td><td>' + files[i].size + '</td><td><span id="file_progress_' + i + '"></span></td>';
			fileList.appendChild(tr);
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
                document.getElementById('result').textContent = "Canceled";
                document.getElementById('result').setAttribute('class', 'cancelled');

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
			document.getElementById('file_progress_'+index).textContent = "Done";
			document.getElementById('file_progress_'+index).setAttribute('class', 'done');
			/* if this was the last file, disable stop button */
			if(++done == files.length) {
		                document.getElementById('stopButton').setAttribute('disabled','disabled');
				document.getElementById('result').textContent = "Done";
	                        document.getElementById('result').setAttribute('class', 'done');
			}
		}, false);

                xhr.open("POST", '<?php echo $_SERVER['SCRIPT_NAME']; ?>?action=upload&fileName='+file.name, true);
               	xhr.send(file);
	}
</script>
</body>
</html>
