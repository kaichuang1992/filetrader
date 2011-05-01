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
<style type="text/css">
	body { 
		font-size: 85%;
	}

	table { 
		/* width: 100%; */
		border: 1px solid #000; 
	}
	
	tfoot td {
		border-top: 1px dashed #000;
		padding-top: 5px;
	}

	th { 
		text-align: left;
	}
	
	td {
		font-size: 85%;
	}

	span.done {
		color: green;
	}

	span.aborted {
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
		<tr><td colspan="3">No files selected yet...</td></tr>
	</tbody>
	<tfoot>
		<tr><td colspan="2">
			<input id="inputFiles" type="file" onchange="listFiles(this.files)" multiple>
			<button id="startButton" onclick="startUpload()" disabled>Start Upload</button>
			<button id="cancelButton" onclick="cancelUpload()" disabled>Cancel Upload</button>
		</td>
		<td><span id="uploadStatus"></span></td>
		</tr>
	</tfoot>
</table>

<script>
        var files; /* keep track of the files to upload */
        var xhrs; /* keep track of all xhrs to be able to stop them */
        var done; /* keep track of the number of files done uploading */
	var blockSize = 1024*1024;

        /* add the files to the upload list */
        function listFiles(f) {
                files = f;
                document.getElementById('uploadStatus').textContent = '';
                var fileList = document.getElementById('fileList');
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
                xhrs = new Array();
                done = 0;
                if(files != null) {
                        document.getElementById('startButton').setAttribute('disabled','disabled');
                        document.getElementById('cancelButton').removeAttribute('disabled');
                        for (var i = 0; i < files.length; i++) {
                                uploadFile(i,files[i]);
                        }
                }
        }

        function cancelUpload() {
                for(var i = 0; i < xhrs.length; i++) {
                        xhrs[i].abort();
                }
                document.getElementById('cancelButton').setAttribute('disabled','disabled');
                document.getElementById('uploadStatus').textContent = "Canceled";
                document.getElementById('uploadStatus').setAttribute('class', 'canceled');
        }

        function uploadFile(index,file) {
		var bytesLeft = file.size;
		var currentChunk = 0;
		var transferLength;
		var blob;

                if(bytesLeft > 0) {
			transferLength = (blockSize > bytesLeft) ? bytesLeft : blockSize;
			var reader = new FileReader();
			reader.onload = function(evt) {
				var xhr = new XMLHttpRequest();
				xhrs.push(xhr);

				/* progress information during upload */
				xhr.upload.addEventListener("progress", function(evt) { 
				        if (evt.lengthComputable) {
				                document.getElementById('file_progress_'+index).textContent = Math.round(((blockSize*currentChunk+evt.loaded) * 100) / file.size) + "%";
				        }
				}, false);

				/* when upload of a file is complete */
				xhr.upload.addEventListener("load", function(evt) {
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
						/* all done */
					        document.getElementById('file_progress_'+index).textContent = "Done";
					        document.getElementById('file_progress_'+index).setAttribute('class', 'done');
						/* if this was the last file, disable cancel button */
						if(++done == files.length && bytesLeft == 0) {
						        document.getElementById('cancelButton').setAttribute('disabled','disabled');
						        document.getElementById('uploadStatus').textContent = "Done";
						        document.getElementById('uploadStatus').setAttribute('class', 'done');
						}
					}
				}, false);

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
		}
        }
</script>
</body>
</html>
