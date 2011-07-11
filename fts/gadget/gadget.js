var prefs = new _IG_Prefs();
var apiEndPoint = prefs.getString("storageEngine");

function pingServer() {
    var params = {};
    var url = apiEndPoint + "?action=pingServer";
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    gadgets.io.makeRequest(url, function (response) {
        return response.data.ok;
    }, params);
};

// DEBUG


function serverInfo() {
    var params = {};
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var url = apiEndPoint + "?action=serverInfo";
    gadgets.io.makeRequest(url, function (response) {
        output = "";
        for (var i in response.data) {
            if (i === 'availableSpace') {
                output += i + ": " + toHumanSize(response.data[i]) + "\n";
            } else {
                output += i + ": " + response.data[i] + "\n";
            }
        }
        alert(output);
    }, params);
};

function parentDirectory(relativePath) {
    var lastSlash = relativePath.lastIndexOf('/');
    if (lastSlash > 0) {
        relativePath = relativePath.substring(0, lastSlash);
    }
    getDirList(relativePath);
}

function getDirList(relativePath) {
    var params = {};
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    var url = apiEndPoint + "?action=getDirList&relativePath=" + relativePath;

    gadgets.io.makeRequest(url, function (response) {
        if (!response.data.ok) {
            alert(response.data.errorMessage);
        } else {
            document.getElementById('upButton').setAttribute('onclick', 'javascript:parentDirectory("' + relativePath + '")');
            //        document.getElementById('inputFiles').setAttribute('onchange', 'javascript:getUploadToken("' + relativePath + '",this.files[0])');
            document.getElementById('createDirButton').setAttribute('onclick', 'javascript:handleCreateDir("' + relativePath + '",this.form)');
            document.getElementById('status').innerHTML = 'Path: ' + sliceName(relativePath.replace("//", "/"), 25);

            var output = '<table><tr><th>Name</th><th>Size</th><th>Action</th></tr>';
            for (var i in response.data) {
                if (response.data[i] && !response.data[i].fileName) {} else if (response.data[i].isDirectory) {
                    output += "<tr><td><a class=\"dir\" href=\"javascript:getDirList('" + relativePath + '/' + response.data[i].fileName + "')\">" + sliceName(response.data[i].fileName, 50) + '</a></td><td>&nbsp;</td><td>' + "<a class=\"dir\" href=\"javascript:deleteDirectory('" + relativePath + "','" + response.data[i].fileName + "')\">del</a>" + '</td></tr>';
                } else {
                    output += "<tr><td><a class=\"file\" href=\"javascript:getDownloadToken('" + relativePath + '/' + response.data[i].fileName + "')\">" + sliceName(response.data[i].fileName, 50) + '</a></td><td>' + toHumanSize(response.data[i].fileSize) + '</td><td>' + "<a class=\"dir\" href=\"javascript:deleteFile('" + relativePath + "','" + response.data[i].fileName + "')\">del</a>" + '</td></tr>';
                }
            }
            output += "</table>";
            updateOutput(output);
        }
    }, params);
};

function sliceName(name, len) {
    if (name.length > len) {
        return name.slice(0, len / 2 - 2) + "..." + name.slice(0 - (len / 2 - 2), name.length);
    } else {
        return name;
    }
}


function getUploadToken(relativePath, file) {
    var postdata = {
        relativePath: relativePath + '/' + file.name,
        fileSize: file.size
    };
    var params = {};
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(postdata);
    var url = apiEndPoint + "?action=getUploadToken";
    gadgets.io.makeRequest(url, function (response) {
        if (!response.data.ok) {
            alert(response.data.errorMessage);
        } else {
            var uploadUrl = response.data.uploadLocation;
            //alert(uploadUrl);
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
        }
    }, params);

};

function getDownloadToken(relativePath) {
    var postdata = {
        relativePath: relativePath
    };
    var params = {};
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(postdata);
    var url = apiEndPoint + "?action=getDownloadToken";
    gadgets.io.makeRequest(url, function (response) {
        if (!response.data.ok) {
            alert(response.data.errorMessage);
        } else {
            window.location.href = response.data.downloadLocation;
        }
    }, params);
};

function deleteDirectory(relativePath, fileName) {
    if (confirm("Are you sure you want to delete " + fileName + "?")) {
        var postdata = {
            relativePath: relativePath + '/' + fileName
        };
        var params = {};
        params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
        params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
        params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
        params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(postdata);
        var url = apiEndPoint + "?action=deleteDirectory";
        gadgets.io.makeRequest(url, function (response) {
            if (!response.data.ok) {
                alert(response.data.errorMessage);
            }
            getDirList(relativePath);
        }, params);
    }
};

function deleteFile(relativePath, fileName) {
    if (confirm("Are you sure you want to delete " + fileName + "?")) {
        var postdata = {
            relativePath: relativePath + '/' + fileName
        };
        var params = {};
        params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
        params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
        params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
        params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(postdata);
        var url = apiEndPoint + "?action=deleteFile";
        gadgets.io.makeRequest(url, function (response) {
            if (!response.data.ok) {
                alert(response.data.errorMessage);
            }
            getDirList(relativePath);
        }, params);
    }
};

function createDirectory(relativePath, dirName) {
    var postdata = {
        relativePath: relativePath + '/' + dirName
    };
    var params = {};
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(postdata);
    var url = apiEndPoint + "?action=createDirectory";
    gadgets.io.makeRequest(url, function (response) {
        if (!response.data.ok) {
            alert(response.data.errorMessage);
        }
        closeButtonWindow();
        getDirList(relativePath);
    }, params);
};

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
}

function dragenter(e) {
    e.stopPropagation();
    e.preventDefault();
}

function dragover(e) {
    e.stopPropagation();
    e.preventDefault();
}

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

function closeUploadWindow() {
    document.getElementById('upload_overlay').removeAttribute('class', 'visible');
}

getDirList('/');
