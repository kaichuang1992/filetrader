var displayName;

function pingServer() {
    serverCall("pingServer", {}, "GET", function (response) {
	displayName = response.data.displayName;
        return response.data.ok;
    });
}

function serverInfo() {
    serverCall("serverInfo", {}, "GET", function (response) {
        output = "";
        for (var i in response.data) {
            if (i === 'availableSpace') {
                output += i + ": " + toHumanSize(response.data[i]) + "\n";
            } else {
                output += i + ": " + response.data[i] + "\n";
            }
        }
        alert(output);
    });
}

/*
function getDirList() {
	serverCall("getDirList", {'relativePath' : relativePath}, "GET", function() {
		var output = '<table><tr><th>Name</th><th>Size</th><th>Action</th></tr>';
		for (var i in response.data) {
			if (response.data[i].isDirectory) {
			output += '<tr><td><a class="listDir" href="' + relativePath + "/" + response.data[i].fileName + '">' + sliceName(response.data[i].fileName, 50) + '</a></td><td>&nbsp;</td><td><a class="deleteDir" href="' + relativePath + '/' + response.data[i].fileName + '">del</a></td></tr>';
			} else {

			}
	});
}
*/

function getDirList(relativePath) { /* FIXME serverCall("getDirList, {'relativePath' : relativePath}, "GET", function() { */
    serverCall("getDirList&relativePath=" + relativePath, {}, "GET", function (response) {
//        document.getElementById('upButton').setAttribute('onclick', 'javascript:parentDirectory("' + relativePath + '")');
//        document.getElementById('fileElem').setAttribute('onchange', 'handleFiles("' + relativePath + '",this.files)');

//        document.getElementById('createDirButton').setAttribute('onclick', 'javascript:handleCreateDir("' + relativePath + '",this.form)');
//        document.getElementById('status').innerHTML = 'Path: <strong>' + sliceName(relativePath.replace("//", "/"), 25) + '</strong> @ ' + displayName;
	var output = '';

	output += '<div id="header"><span class="breadcrumb"><a href="#">Home</a>';
	var parts = relativePath.replace("//", "/").split("/");
	for(var i in parts) {
	   if(parts[i] !== '') {
	       output += ' &raquo; <a href="#">' + parts[i] + '</a>';
	   }
	}
	output += '<span class="icons"><a href="#"><img src="i/add_bl.png" width="32" height="32"></a> <a href="#"><img src="i/setting_bl.png" width="32" height="32"></a></span></div>';

    output += '<div id="content"><table class="filelist"><tr><th>Action</th><th>Name</th><th>Size</th><th>Created</th><th>Delete</th></tr>';
	var counter=0;
        for (var i in response.data) {
	    if(counter%2===0) {
		output += '<tr class="even">';
	    } else {
		output += '<tr class="odd">';
	    }
            if (response.data[i] && !response.data[i].fileName) {} else if (response.data[i].isDirectory) {
                output += '<td><td><a href="' + relativePath + '/' + response.data[i].fileName + '"><img src="i/dir_bl.png" width="20" height="20"></a></td><td class="dir">' + sliceName(response.data[i].fileName, 50) + '</td><td>-</td><td>' + fancyDate(response.data[i].fileDate) + '</td><td><a href="' + relativePath + '/' + response.data[i].fileName + '"><img src="i/bin_bl.png" width="20" height="20"></a></td>';
            } else {
		output += '<td><a href="' + relativePath + '/' + response.data[i].fileName + '"><img src="i/download_bl.png" width="20" height="20"></a></td><td class="file">' + sliceName(response.data[i].fileName, 50) + '</td><td>' + toHumanSize(response.data[i].fileSize) + '</td><td>' + fancyDate(response.data[i].fileDate) + '</td><td><a href="' + relativePath + '/' + response.data[i].fileName + '"><img src="i/bin_bl.png" width="20" height="20"></a></td>';
           }
		output += "</tr>";
		counter+=1;
        }
        output += "</table>";
        updateOutput(output);
    });
}

function getUploadToken(relativePath, file) {
    var params = {
        relativePath: relativePath + '/' + file.name,
        fileSize: file.size
    };
    serverCall("getUploadToken", params, "POST", function (response) {
        var uploadUrl = response.data.uploadLocation;
        var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                var percentComplete = Math.round(evt.loaded / evt.total * 100);
                var fn;
                if (file.name.length > 23) {
                    fn = file.name.slice(0, 10) + "..." + file.name.slice(-10, file.name.length);
                } else {
                    fn = file.name;
                }
                document.getElementById('status').innerHTML = "Progress (" + fn + "): " + percentComplete + "%";
            }
        }, false);
        xhr.upload.addEventListener("load", function (evt) {
            document.getElementById('status').innerHTML = 'Path: ' + relativePath.replace("//", "/");
            getDirList(relativePath);
        }, false);
        xhr.open("PUT", uploadUrl, true);
        xhr.send(file);
    });
}

function getDownloadToken(relativePath) {
    var params = {
        relativePath: relativePath
    };
    serverCall("getDownloadToken", params, "POST", function (response) {
        window.location.href = response.data.downloadLocation;
    });
}

function deleteDirectory(relativePath, fileName) {
    if (confirm("Are you sure you want to delete " + fileName + "?")) {
        var params = {
            relativePath: relativePath + '/' + fileName
        };
        serverCall("deleteDirectory", params, "POST", function (response) {
            getDirList(relativePath);
        });
    }
}

function deleteFile(relativePath, fileName) {
    if (confirm("Are you sure you want to delete " + fileName + "?")) {
        var params = {
            relativePath: relativePath + '/' + fileName
        };
        serverCall("deleteFile", params, "POST", function (response) {
            getDirList(relativePath);
        });
    }
}

function createDirectory(relativePath, dirName) {
    var params = {
        relativePath: relativePath + '/' + dirName
    };
    serverCall("createDirectory", params, "POST", function (response) {
        closeButtonWindow();
        getDirList(relativePath);
    });
}

/* Below are the helper functions, much can be optimized, it is one big mess */

function updateOutput(output) {
    document.getElementById('ft_output').innerHTML = output;
    gadgets.window.adjustHeight();
}

function toHumanSize(bytes) {
    var kilobyte = 1024;
    var megabyte = kilobyte * kilobyte;
    var gigabyte = megabyte * kilobyte;

    if (bytes >= gigabyte) return Math.round((bytes / gigabyte)) + "GB";
    if (bytes >= megabyte) return Math.round((bytes / megabyte)) + "MB";
    if (bytes >= kilobyte) return Math.round((bytes / kilobyte)) + "kB";
    return bytes;
}

function handleCreateDir(relativePath, form) {
    createDirectory(relativePath, form.dirName.value);
}

function createButtonWindow() {
    document.getElementById('overlay').setAttribute('class', 'visible');
    gadgets.window.adjustHeight();
}

function closeButtonWindow() {
    document.getElementById('overlay').removeAttribute('class', 'visible');
}

function createUploadWindow() {
    document.getElementById('upload_overlay').setAttribute('class', 'visible');
    var dropbox;
    dropbox = document.getElementById("upload_area");
    dropbox.addEventListener("dragenter", dragenter, false);
    dropbox.addEventListener("dragover", dragover, false);
    dropbox.addEventListener("drop", drop, false);
    gadgets.window.adjustHeight();

    var fileSelect = document.getElementById("fileSelect"),
        fileElem = document.getElementById("fileElem");
    fileSelect.addEventListener("click", function (e) {
        if (fileElem) {
            fileElem.click();
        }
        e.preventDefault(); // prevent navigation to "#"
    }, false);
}

function dragenter(e) {
    e.stopPropagation();
    e.preventDefault();
}

function dragover(e) {
    e.stopPropagation();
    e.preventDefault();
}

/* FIXME: need to work with relativePath! */

function drop(e) {
    e.stopPropagation();
    e.preventDefault();
    var dt = e.dataTransfer;
    var files = dt.files;
    var i;
    closeUploadWindow();
    for (i = 0; i < files.length; i = i + 1) {
        getUploadToken('/', files[i]);
    }
}

function handleFiles(relativePath, files) {
    closeUploadWindow();
    for (var i = 0; i < files.length; i++) {
        getUploadToken(relativePath, files[i]);
    }
}

function closeUploadWindow() {
    document.getElementById('upload_overlay').removeAttribute('class', 'visible');
}

/* FIXME */

function parentDirectory(relativePath) {
    var lastSlash = relativePath.lastIndexOf('/');
    if (lastSlash > 0) {
        relativePath = relativePath.substring(0, lastSlash);
    }
    getDirList(relativePath);
}

/* FIXME */

function sliceName(name, len) {
    if (name.length > len) {
        return name.slice(0, len / 2 - 2) + "..." + name.slice(0 - (len / 2 - 2), name.length);
    } else {
        return name;
    }
}

function fancyDate(ts) {
	var currentTs = new Date().getTime();
	var date = new Date(ts*1000);
	var humanDate;

	if(ts*1000 > (currentTs - 60*60*24*1000)) {
		/* last 24h, show just time */
		var hours = (date.getHours() < 10) ? "0" + date.getHours() : date.getHours();
		var minutes = (date.getMinutes() < 10) ? "0" + date.getMinutes() : date.getMinutes();
		humanDate = hours + ":" + minutes;
	} else if ( ts*1000 > (currentTs - 60*60*24*7*1000) ) {
		/* last week, show just day */
		humanDate = dayToText(date.getDay());
	} else if (ts*1000 > (currentTs - 60*60*24*365*1000 )) {
		/* longer ago, show just month+day */
		humanDate = date.getMonth() + " " + date.getDate();
	} else { 
		/* show year */
		humanDate = date.getFullYear();
	}
	return humanDate;
}

function dayToText(day) {
	var days = { 0: "Sun", 1: "Mon", 2: "Tue", 3: "Wed", 4: "Thu", 5: "Fri", 6: "Sat" };
	return days[day];
}

pingServer();
getDirList('/');
