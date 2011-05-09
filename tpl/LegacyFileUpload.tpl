<p>
	<strong>Maximum Upload Size: {$max_upload_size|to_human_size}</strong>
</p>

<p>
&lt;!--	This is limited by the PHP configuration options <tt>post_max_size</tt> and <tt>upload_max_filesize</tt> and should be modified in the server. Use the <em>non-legacy</em> uploader for bigger file uploads --&gt;
</p>

<form enctype="multipart/form-data" method="POST">
	<input type="hidden" name="action" value="handleLegacyUpload">
	<input type="hidden" name="MAX_FILE_SIZE" value="{$max_upload_size}" />
	<input name="userfile" type="file" />
	<input type="submit" value="Send File" />
</form>
