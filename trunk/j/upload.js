$(document).ready(function() {
	var uploader = new plupload.Uploader({
		runtimes : 'html5',
		browse_button : 'uploadPickFiles',
		container : 'upload',
		max_file_size : '4096mb',
		url : 'index.php?action=handleUpload',
		drop_element :'uploadFileList',
	});

	uploader.bind('Init', function(up, params) {
		// $('#uploadFileList').html("<div>Current runtime: " + params.runtime + "</div>");
		$('#uploadFileList').html("<div>Drag files here...</div>");
	});

	$('#uploadFiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#uploadFileList').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ')' +
			'</div>');
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file) {
		$('#' + file.id + " b").html("100%");
	});

});

