$(document).ready(function() {
	$("#upload").pluploadQueue({
		runtimes : 'html5',
		url : 'index.php?action=handleUpload',
		max_file_size : '4096mb',
		chunk_size : '1mb',
		dragdrop : true,
	});
});