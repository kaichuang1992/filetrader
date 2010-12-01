$(document).ready(function() {
	var uploader = new plupload.Uploader({
		runtimes : 'html5',
		browse_button : 'uploadPickFiles',
		container : 'upload',
		max_file_size : '4096mb',
		url : 'index.php?action=handleUpload',
		drop_element :'upload',
	});

	uploader.bind('Init', function(up, params) {
		// $('#uploadFileList').html("<div>Current runtime: " + params.runtime + "</div>");
		$('#uploadFileList').html("<div>Drag files here...</div>");
	});

	$('#uploadFiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	$('#stopUpload').click(function(e) {
		uploader.stop();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#fileList').append(
				'<label id="' + file.id + '"><input type="checkbox" name="id[]" value=' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ')' +
			'</label>');
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id).html(file.name + ' ' + file.percent + "%");
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
		$('#' + file.id).addClass('finished');
	});

	uploader.bind('StateChanged', function(up) {
		if(up.state == plupload.STARTED) {
			$('#stopUpload').removeAttr('disabled');
		}

		if(up.state == plupload.STOPPED) {
			$('#stopUpload').attr('disabled', 'disabled');
		}
	});
});

