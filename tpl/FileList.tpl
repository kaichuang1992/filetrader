	<h2>File List</h2>
		{if empty($files)}
			<p>	
				You did not upload any files yet. Please upload some files :)
			</p>

		{else}
			<p>
		                Below a list of your uploaded files is shown.
			</p>

			<form method="post">
			<input type="hidden" name="action" value="deleteFile">

			
			<table>
	                {foreach key=k item=v from=$files}
			<tr><td>
                                                <input type="checkbox" name="id[]" title="Mark this file for deletion" value="{$k}">
			</td>
			<td>
						<a title="{$v.fileSize}, uploaded on {$v.fileDate|date_format:"%c"}" href="?action=downloadFile&amp;id={$k}">
							{$v.fileName}
						</a>
			</td><td>	
	                                {if $type == "myFiles"}
						{if $group_share}
							<a href="?action=groupShare&id={$k}"><img src="i/group_add.png" alt="Group Share" title="Group Share"></a>
						{/if}

						{if $email_share}
							<a href="?action=emailShare&id={$k}"><img src="i/email_add.png" alt="Email Share" title="Email Share"></a>
						{/if}
	                                {/if}
	                </td></tr>
	                {/foreach}
			</table>

			<input type="submit" title="Delete the selected files" value="Delete Files">
			</form>
		{/if}

	<h2>Upload</h2>
        <div id="upload">
        	<div id="uploadFileList">Your browser doesn't have HTML5 support.</div>
                <br />
                <button id="uploadPickFiles">Select files...</button>
                <button id="uploadFiles">Upload</button>
        </div>
