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
            // this is ugly do not want!
            $("tbody").html($("#directoryEntryHeader").tmpl());
            $("#directoryEntry").tmpl(response.data).appendTo("tbody");
            $('tr:odd').each(function () {
                // TODO: set style class instead of CSS right here
                $(this).css('background-color', '#ccc');
            });
            $('a.download').click(function (event) {
                params.action = 'getDownloadToken';
                params.relativePath += "/" + $(this).parent().next().text();
                $.post(proxy_endpoint, params, function (response) {
                    window.location.href = response.data.downloadLocation;
                }, 'json');
                // TODO: restore relativePath
                getDirList();
            });
            $('a.open').click(function (event) {
                params.relativePath += "/" + $(this).parent().next().text();
                getDirList();
            });
            $('a.delete').click(function (event) {
                var fileName = $(this).parent().prev().prev().prev().text();
                if (confirm("Are you sure you want to delete '" + fileName + "'?")) {
                    params.action = 'deleteFile';
                    params.relativePath += "/" + fileName;
                    $.post(proxy_endpoint, params, function (response) {}, 'json');
                    // TODO: restore relativePath
                    getDirList();
                }
            });
        }, 'json');
    }
    getDirList();
});
