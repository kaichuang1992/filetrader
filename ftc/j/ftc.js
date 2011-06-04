var curDir = ".";

$(document).ready(
    function() {
	    $("button").click(function(event) {
		    var actionType = $(this).attr('id');
		    $.post('?action=' + actionType, {
			    relativePath : $("#dirName").val() });
		    event.preventDefault();
	    });

	    $("a.file").live('click', function(event) {
		    var fileName = $(this).text();
		    $.post('?action=getDownloadToken', {
			    relativePath : curDir + "/" + fileName }, function(data) {
			    var resp = jQuery.parseJSON(data);
			    window.location.href = resp.downloadLocation;
		    });
		    event.preventDefault();
	    });

	    $("a.dir").live("click", function(event) {
		    var dirName = $(this).text();
		    curDir = curDir + "/" + dirName;
		    redrawPage('getDirList');
		    event.preventDefault();
	    });

	    $("a.menu").live(
	        'click',
	        function(event) {
		        var actionType = $(this).attr('id');
			redrawPage(actionType);
		        event.preventDefault();
	        });

	    $("input").change(function(event) {
		    var f = this.files[0];

		    var fileName = $(this).text();
		    $.post('?action=getUploadToken', {
		      relativePath : curDir + "/" + f.name,
		      fileSize : f.size }, function(data) {
			    var resp = jQuery.parseJSON(data);
			    var uploadUrl = resp.uploadLocation;

			    var xhr = new XMLHttpRequest();
			    xhr.open("PUT", uploadUrl, true);
			    xhr.send(f);

		    });
		    event.preventDefault();
	    });
    });

function redrawPage(actionType) {
                        $.getJSON('?action=' + actionType, {
                                relativePath : curDir }, function(data) {
                                var items = [];

                                if (actionType === 'getDirList') {
					items.push('<tr><th colspan="2">' + curDir + '</th></tr>');
                                        $.each(data, function(key, val) {
                                                if (val.isDirectory) {
                                                        items.push('<tr><td><a href="#" class="dir">' + val.fileName
                                                            + '</a></td><td>[DIR]</td></tr>>');
                                                } else {
                                                        items.push('<tr><td><a href="#" class="file">' + val.fileName
                                                            + '</a></td><td>' + val.fileSize + '</td></tr>>');
                                                }
                                        });
                                } else {
                                        $.each(data, function(key, val) {
                                                items.push('<tr><th>' + key + '</th><td>' + val
                                                    + '</td></tr>>');
                                        });
                                }
                                $("#output").html($('<table/>', {
                                  'class' : 'my-new-list',
                                  html : items.join('') }));

                        });
}
