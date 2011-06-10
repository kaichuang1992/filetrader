var curDir = "/";

$(document).ready(function() {
	$("button").click(function(event) {
		var actionType = $(this).attr('id');
		$.post('?action=' + actionType, {
			relativePath : curDir + "/" + $("#dirName").val() }, function(data) {
			var resp = jQuery.parseJSON(data);
			if (!resp.ok) {
				alert(resp.errorMessage); // .errorMessage);
			} else {
				redrawPage('getDirList');
			}
		});
		event.preventDefault();
	});

	$("a.dirdel").live('click', function(event) {
		if (confirm('Are you sure you want to delete this directory?')) {

			var dirName = $(this).attr('id');
			$.post('?action=deleteDirectory', {
				relativePath : curDir + "/" + dirName }, function(data) {
				var resp = jQuery.parseJSON(data);
				if (!resp.ok) {
					alert(resp.errorMessage); // .errorMessage);
				} else {
					redrawPage('getDirList');
				}
			});
		}
		event.preventDefault();

	});

	$("a.filedel").live('click', function(event) {
		if (confirm('Are you sure you want to delete this file?')) {
			var dirName = $(this).attr('id');
			$.post('?action=deleteFile', {
				relativePath : curDir + "/" + dirName }, function(data) {
				var resp = jQuery.parseJSON(data);
				if (!resp.ok) {
					alert(resp.errorMessage); // .errorMessage);
				} else {
					redrawPage('getDirList');
				}
			});
		}
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

	$("a.up").live("click", function(event) {
		var lastSlash = curDir.lastIndexOf('/');
		if (lastSlash > 0) {
			curDir = curDir.substring(0, lastSlash);
			redrawPage('getDirList');
		}
		event.preventDefault();
	});

	$("a.dir").live("click", function(event) {
		var dirName = $(this).text();
		curDir = curDir + "/" + dirName;
		redrawPage('getDirList');
		event.preventDefault();
	});

	$("a.menu").live('click', function(event) {
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
			if (!resp.ok) {
				alert(resp.errorMessage);
			} else {
				var uploadUrl = resp.uploadLocation;

				var xhr = new XMLHttpRequest();
				xhr.open("PUT", uploadUrl, true);
				xhr.send(f);
				redrawPage('getDirList');
			}
		});
		event.preventDefault();
	});
	redrawPage('getDirList');
});

function redrawPage(actionType) {
	$
	    .getJSON(
	        '?action=' + actionType,
	        {
		        relativePath : curDir },
	        function(data) {
		        var items = [];

		        if (actionType === 'getDirList') {
			        items
			            .push('<tr><td class="header" colspan="3"><a href="#" class="up">Up</a> ('
			                + curDir + ')</td></tr>');
			        $
			            .each(
			                data,
			                function(key, val) {
				                if (key != "ok") {

					                if (val.isDirectory) {
						                items
						                    .push('<tr><td><a href="#" class="dir">'
						                        + val.fileName
						                        + '</a></td><td>[DIR]</td><td><a href="#" class="dirdel" id="'
						                        + val.fileName + '">delete</a></td></tr>');
					                } else {
						                items.push('<tr><td><a href="#" class="file">'
						                    + val.fileName + '</a></td><td>'
						                    + toHumanSize(val.fileSize)
						                    + '</td><td><a href="#" class="filedel" id="'
						                    + val.fileName + '">delete</a></td></tr>');
					                }
				                }
			                });
		        } else {
			        $.each(data,
			            function(key, val) {
				            if (key === 'availableSpace') {
					            val = toHumanSize(val);
				            }
				            items.push('<tr><td>' + key + '</td><td>' + val
				                + '</td></tr>>');
			            });
		        }
		        $("#output").html($('<table/>', {
		          'class' : 'my-new-list',
		          html : items.join('') }));

	        });
}

function toHumanSize(bytes) {
	var kilobyte = 1024;
	var megabyte = kilobyte * kilobyte;
	var gigabyte = megabyte * kilobyte;

	if (bytes >= gigabyte)
		return Math.round((bytes / gigabyte)) + "GB";
	if (bytes >= megabyte)
		return Math.round((bytes / megabyte)) + "MB";
	if (bytes >= kilobyte)
		return Math.round((bytes / kilobyte)) + "kB";
	return bytes;
}
