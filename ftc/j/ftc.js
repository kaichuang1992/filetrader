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
            $("tbody").html($("#directoryList").tmpl(response));
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

    function fancyDate(ts) {
        var currentTs = new Date().getTime();
        var date = new Date(ts * 1000);
        var humanDate;
        if (ts * 1000 > (currentTs - 60 * 60 * 24 * 1000)) {
            // last 24h, show just time
            var hours = (date.getHours() < 10) ? "0" + date.getHours() : date.getHours();
            var minutes = (date.getMinutes() < 10) ? "0" + date.getMinutes() : date.getMinutes();
            humanDate = hours + ":" + minutes;
        } else if (ts * 1000 > (currentTs - 60 * 60 * 24 * 7 * 1000)) {
            // last week, show just day and time
            var hours = (date.getHours() < 10) ? "0" + date.getHours() : date.getHours();
            var minutes = (date.getMinutes() < 10) ? "0" + date.getMinutes() : date.getMinutes();
            humanDate = dayToText(date.getDay()) + " " + hours + ":" + minutes;
        } else if (ts * 1000 > (currentTs - 60 * 60 * 24 * 365 * 1000)) {
            // longer ago, show just month+day
            humanDate = monthToText(date.getMonth()) + " " + date.getDate();
        } else {
            // show year 
            humanDate = date.getFullYear();
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

    function toHumanSize(bytes) {
        var kilobyte = 1024;
        var megabyte = kilobyte * kilobyte;
        var gigabyte = megabyte * kilobyte;
        if (bytes >= gigabyte) {
            return Math.round((bytes / gigabyte)) + "GB"
        };
        if (bytes >= megabyte) {
            return Math.round((bytes / megabyte)) + "MB"
        };
        if (bytes >= kilobyte) {
            return Math.round((bytes / kilobyte)) + "kB"
        };
        return bytes;
    }
    getDirList();
});
