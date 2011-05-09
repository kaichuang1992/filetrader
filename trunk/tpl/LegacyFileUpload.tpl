<p>
	<strong>Maximum Upload Size: 1MB</strong>
</p>

<form enctype="multipart/form-data" method="POST">
	<input type="hidden" name="action" value="handleLegacyUpload">
	<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
	<input name="userfile" type="file" />
	<input type="submit" value="Send File" />
</form>
