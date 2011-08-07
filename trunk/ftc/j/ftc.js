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
            $("tbody").html($("#directoryList").tmpl(response, {
                fDate: function (timestamp) {
                    return fancyDateTime(timestamp);
                },
                fSize: function (bytes) {
                    return fancyBytes(bytes);
                }
            }));
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
            $('a#ftc_add').click(function (event) {
                $('#dropbox').show();
            });
            $('a#ftc_cancel_upload').click(function (event) {
                $('#dropbox').hide();
            });
            $('a.download').click(function (event) {
                var tmp_rp = params.relativePath;
                params.action = 'getDownloadToken';
                params.relativePath += "/" + $(this).parent().next().text();
                $.post(proxy_endpoint, params, function (response) {
                    window.location.href = response.data.downloadLocation;
                }, 'json');
                params.relativePath = tmp_rp;
                getDirList();
            });
            $('a.open').click(function (event) {
                params.relativePath += "/" + $(this).parent().next().text();
                getDirList();
            });
            $('a.delete').click(function (event) {
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
        }, 'json');
    }

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
        var kilobyte = 1024;
        var megabyte = kilobyte * kilobyte;
        var gigabyte = megabyte * kilobyte;
        var terabyte = gigabyte * kilobyte;
        if (bytes >= terabyte) {
            return Math.round((bytes / terabyte)) + "TB"
        }
        if (bytes >= gigabyte) {
            return Math.round((bytes / gigabyte)) + "GB"
        }
        if (bytes >= megabyte) {
            return Math.round((bytes / megabyte)) + "MB"
        }
        if (bytes >= kilobyte) {
            return Math.round((bytes / kilobyte)) + "kB"
        }
        return bytes;
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
    getDirList();
});
