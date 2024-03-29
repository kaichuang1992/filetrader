$(document).ready(function () {
    var proxy_endpoint = "proxy.php";
    var params = {
        proxy_to: "http://192.87.110.161/fts/index.php",
        proxy_consumer_key: "demo",
        proxy_consumer_secret: "a1bf8348016c52f81498cd576d55a932",
        relativePath: "/",
    };

    function getDirList() {
        params.action = 'getDirList';
        $.get(proxy_endpoint, params, function (response) {
            $("#filelist").html($("#directoryList").tmpl(response, {
                fDate: function (timestamp) {
                    return fancyDateTime(timestamp);
                },
                fSize: function (bytes) {
                    return fancyBytes(bytes);
                }
            }));
        }, 'json');
        // FIXME: optimize this
        $("#breadcrumb").html($("#directoryPathViewer").tmpl(splitPath(params.relativePath)));
        $("#breadcrumb li").click(function (event) {
            var relPath;
            if ($(this).hasClass("root")) {
                relPath = "/";
            } else {
                relPath = $(this).text();
                $.each($(this).prevUntil("li.root"), function (i, val) {
                    relPath = $(this).text() + "/" + relPath;
                });
            }
            params.relativePath = relPath;
            getDirList();
        });
    }
    $('a#ftc_add').click(function (event) {
        $('#dropbox').show();
    });
    $('a#ftc_cancel_upload').click(function (event) {
        $('#dropbox').hide();
    });
    $('a.download').live('click', function (event) {
        var tmp_rp = params.relativePath;
        params.action = 'getDownloadToken';
        params.relativePath += "/" + $(this).parent().next().text();
        $.post(proxy_endpoint, params, function (response) {
            window.location.href = response.data.downloadLocation;
        }, 'json');
        params.relativePath = tmp_rp;
    });
    $('a.open').live('click', function (event) {
        params.relativePath += "/" + $(this).parent().next().text();
        getDirList();
    });
    $('a.delete').live('click', function (event) {
        var fileName = $(this).parent().prev().prev().prev().text();
        if (confirm("Are you sure you want to delete '" + fileName + "'?")) {
            var tmp_rp = params.relativePath;
            params.action = 'deleteFile';
            params.relativePath += "/" + fileName;
            $.post(proxy_endpoint, params, function (response) {
                params.relativePath = tmp_rp;
                getDirList();
            }, 'json');
        }
    });
    $('#inputFiles').bind('change', function (e) {
        handleFiles(this.files);
        e.stopPropagation();
        e.preventDefault();
    });
    $('#dropzone').bind('dragenter dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });
    $('#dropzone').bind('drop', function (e) {
        e.stopPropagation();
        e.preventDefault();
        handleFiles(e.originalEvent.dataTransfer.files);
    });
    $('#startUpload').click(function (e) {
        startUpload();
        e.stopPropagation();
        e.preventDefault();
    });
    $('#cancelUpload').click(function (e) {
        cancelUpload();
        e.stopPropagation();
        e.preventDefault();
    });

    function fancyDateTime(ts) {
        var now = new Date();
        var dayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var fileDateTime = new Date(ts * 1000);
        var now_ts = Math.round(now.getTime() / 1000);
        var dayStart_ts = Math.round(dayStart.getTime() / 1000);
        var hours = (fileDateTime.getHours() < 10) ? "0" + fileDateTime.getHours() : fileDateTime.getHours();
        var minutes = (fileDateTime.getMinutes() < 10) ? "0" + fileDateTime.getMinutes() : fileDateTime.getMinutes();
        var humanDate;
        if (ts > dayStart_ts) {
            // today, show just time
            humanDate = hours + ":" + minutes;
        } else if (ts > (now_ts - 60 * 60 * 24 * 7)) {
            // last week, show just day and time
            humanDate = dayToText(fileDateTime.getDay()) + " " + hours + ":" + minutes;
        } else if ((ts > now_ts - 60 * 60 * 24 * 365)) {
            // longer than a week ago, show just month+day
            humanDate = monthToText(fileDateTime.getMonth()) + " " + fileDateTime.getDate();
        } else {
            // longer than a year ago, show just year 
            humanDate = fileDateTime.getFullYear();
        }
        return humanDate;
    }

    function dayToText(day) {
        var days = {
            0: "Sun",
            1: "Mon",
            2: "Tue",
            3: "Wed",
            4: "Thu",
            5: "Fri",
            6: "Sat"
        };
        return days[day];
    }

    function monthToText(month) {
        var months = {
            0: "Jan",
            1: "Feb",
            2: "Mar",
            3: "Apr",
            4: "May",
            5: "Jun",
            6: "Jul",
            7: "Aug",
            8: "Sep",
            9: "Oct",
            10: "Nov",
            11: "Dec"
        };
        return months[month];
    }

    function fancyBytes(bytes) {
        // Thanks to http://codeaid.net/javascript/convert-size-in-bytes-to-human-readable-format-%28javascript%29
        var i, sizes = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        if (bytes !== 0 && bytes > 1024) {
            i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
        } else {
            i = 0;
            return bytes + ' ' + sizes[i];
        }
    }

    function splitPath(fullPath) {
        var e = {
            pathEntries: []
        };
        parts = fullPath.split("/");
        $.each(parts, function (i, v) {
            if (v !== '') {
                e.pathEntries.push(v);
            }
        });
        return e;
    }
    var uploader_total_size;
    var uploader_uploaded;
    var uploader_files;
    var uploader_xhrs;
    var uploader_rdrs;
    var uploader_done;
    var uploader_block_size = 1024 * 1024;

    function handleFiles(files) {
        uploader_files = files;
        uploader_total_size = 0;
        uploader_uploaded = [];
        if (uploader_files.length !== 0) {
            $('#startUpload').removeAttr('disabled');
            for (var i = 0; i < uploader_files.length; i++) {
                uploader_total_size += uploader_files[i].size;
                uploader_uploaded[i] = 0;
            }
            $('#statusBar').html("Selected " + uploader_files.length + " file(s) for upload, a total of " + fancyBytes(uploader_total_size) + ".");
            $('#progressBar').text("0%");
        }
    }

    function startUpload() {
        uploader_start_time = Date.now();
        uploader_xhrs = new Array();
        uploader_rdrs = new Array();
        uploader_done = 0;
        if (uploader_files !== null && uploader_files.length !== 0) {
            $('#startUpload').attr('disabled', 'disabled');
            $('#cancelUpload').removeAttr('disabled');
            for (var i = 0; i < uploader_files.length; i++) {
                // get token
                var tmp_rp = params.relativePath;
                params.action = 'getUploadToken';
                params.relativePath += "/" + uploader_files[i].name;
                params.fileSize = uploader_files[i].size;
                $.post(proxy_endpoint, params, function (response) {
                    var upload_url = response.data.uploadLocation;
                    uploadFile(i, uploader_files[i], upload_url);
                }, 'json');
                params.relativePath = tmp_rp;
            }
        }
    }

    function cancelUpload() {
        for (var i = 0; i < uploader_xhrs.length; i++) {
            uploader_xhrs[i].abort();
        }
        for (var i = 0; i < uploader_rdrs.length; i++) {
            uploader_rdrs[i].abort();
        }
        $('#cancelUpload').attr('disabled', 'disabled');
    }

    function sliceWrapper(file, start, end) {
        var blob = false;
        if (file.slice) {
            alert("WARNING: using Blob.slice()");
            blob = file.slice(start, end - start);
        } else if (file.mozSlice) {
            blob = file.mozSlice(start, end);
        } else if (file.webkitSlice) {
            blob = file.webkitSlice(start, end);
        } else {
            alert('ERROR: FileAPI Blob.slice() not supported by browser');
        }
        return blob;
    }

    function uploadFile(index, file, upload_url) {
        var bytesLeft = file.size;
        var currentChunk = 0;
        var transferLength;
        var blob;
        var slice_function;
        var xhr;
        if (bytesLeft > 0) {
            transferLength = (uploader_block_size > bytesLeft) ? bytesLeft : uploader_block_size;
            var reader = new FileReader();
            uploader_rdrs.push(reader);
            reader.onload = function (evt) {
                xhr = new XMLHttpRequest();
                uploader_xhrs.push(xhr);
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        uploader_uploaded[index] = Math.round(uploader_block_size * currentChunk + evt.loaded);
                        var total_uploaded = 0;
                        for (var i = 0; i < uploader_files.length; i++) {
                            total_uploaded += uploader_uploaded[i];
                        }
                        var val = Math.round(total_uploaded * 100 / uploader_total_size);
                        $('#progressBar').val(val).text(val + "%");
                        var elapsed_time = Math.round((Date.now() - uploader_start_time) / 1000);
                        if (!isNaN(total_uploaded) && !isNaN(elapsed_time) && elapsed_time > 0) {
                            $('#progressInfo').text(fancyBytes(Math.round(total_uploaded / elapsed_time)) + "/s");
                        }
                    }
                }, false);
                // when upload of a chunk is complete
                xhr.upload.addEventListener("load", function (evt) {
                    bytesLeft -= transferLength;
                    if (bytesLeft > 0) {
                        transferLength = (uploader_block_size > bytesLeft) ? bytesLeft : uploader_block_size;
                        currentChunk++;
                        blob = sliceWrapper(file, currentChunk * uploader_block_size, currentChunk * uploader_block_size + transferLength);
                        // read the next blob
                        reader.readAsBinaryString(blob);
                    } else {
                        // file done
                        if (++uploader_done == uploader_files.length && bytesLeft == 0) {
                            // also last file done
                            $('#progressBar').val(100).text(100 + "%");
                            $('#cancelUpload').attr('disabled', 'disabled');
                            $('#progressInfo').prepend('Average: ');
                            getDirList();
                        }
                    }
                }, false);
                xhr.open("PUT", upload_url, true);
                xhr.setRequestHeader("X-File-Chunk", currentChunk);
                xhr.send(blob);
            }
            blob = sliceWrapper(file, 0, currentChunk * uploader_block_size + transferLength);
            reader.readAsBinaryString(blob);
        }
    }
    $.ajaxSetup({
        async: false
    });
    getDirList();
});
