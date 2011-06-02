$(document).ready(
    function() {
	    $("button").click(function(event) {
		    var actionType = $(this).attr('id');
		    $.post('?action=' + actionType, {
			    dirName : $("#dirName").val() });
		    event.preventDefault();
	    });

	    $("a.file").live('click', function(event) {
		    var fileName = $(this).text();
		    $.post('?action=getDownloadToken', {
			    fileName : fileName }, function(data) {
			    var resp = jQuery.parseJSON(data);
			    window.location.href = resp.downloadLocation;
		    });
		    event.preventDefault();
	    });

	    $("a.menu").live(
	        'click',
	        function(event) {
		        var actionType = $(this).attr('id');

		        $.getJSON('?action=' + actionType, function(data) {
			        var items = [];

			        if (actionType === 'getFileList') {
				        $.each(data.files, function(key, val) {
					        if (val.isDirectory) {
						        items.push('<tr><th>' + key + '</th><td>[DIR]</td></tr>>');
					        } else {
						        items.push('<tr><th><a href="#" class="file">' + key
						            + '</a></th><td>' + val.fileSize + '</td></tr>>');
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
		        event.preventDefault();
	        });

	    $("input").change(function(event) {
		    var f = this.files[0];

		    var fileName = $(this).text();
		    $.post('?action=getUploadToken', {
		      fileName : f.name,
		      fileSize : f.size }, function(data) {
			    var resp = jQuery.parseJSON(data);
			    var uploadUrl = resp.uploadLocation;
			    alert(uploadUrl);
				
				var xhr = new XMLHttpRequest();
				xhr.open("PUT", uploadUrl, true);
				xhr.send(f);

		    });
		    event.preventDefault();
	    });
    });
