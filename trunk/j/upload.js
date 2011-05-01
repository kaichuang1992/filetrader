        var files; /* keep track of the files to upload */
        var xhrs; /* keep track of all xhrs to be able to stop them */
        var done; /* keep track of the number of files done uploading */

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
                var xhr = new XMLHttpRequest();
                xhrs.push(xhr);

                /* progress information during upload */
                xhr.upload.addEventListener("progress", function(evt) { 
                        if (evt.lengthComputable) {
                                document.getElementById('file_progress_'+index).textContent = Math.round((evt.loaded * 100) / evt.total) + "%";
                        }
                }, false);

                /* when upload of a file is complete */
                xhr.upload.addEventListener("load", function(evt) {
                        document.getElementById('file_progress_'+index).textContent = "Done";
                        document.getElementById('file_progress_'+index).setAttribute('class', 'done');
                        /* if this was the last file, disable cancel button */
                        if(++done == files.length) {
                                document.getElementById('cancelButton').setAttribute('disabled','disabled');
                                document.getElementById('uploadStatus').textContent = "Done";
                                document.getElementById('uploadStatus').setAttribute('class', 'done');
                        }
                }, false);

                xhr.open("POST", 'index.php?action=handleUpload', true);
                xhr.setRequestHeader("X-File-Name", file.name);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.setRequestHeader("X-File-Size", file.size);
                xhr.send(file);
        }

