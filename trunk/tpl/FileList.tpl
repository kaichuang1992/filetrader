	<h2>File List</h2>
		{if empty($files)}
			<p>	
			{if $type == "myFiles"}
				You did not upload any files yet. Click on the <strong>Upload</strong> tab to upload some.
			{else}
				No files were shared with the group(s) you are a member of.
			{/if}	
			</p>

		{else}
			<p>
		        {if $type == "myFiles"}
		                These are the files you uploaded.
		        {else}
		                These are the files others have shared with the group(s) you are a member of.
			{/if}
			</p>

			<table>
			<tr><th>Name</th><th>Action</th></tr>
	                {foreach key=k item=v from=$files}
			<tr><td>
						<a title="{$v.fileSize}, uploaded on {$v.fileDate|date_format:"%c"}" href="?action=downloadFile&amp;id={$k}">
							{$v.fileName}
						</a>
			</td><td>	
	                                {if $type == "myFiles"}
						{if $group_share}
						<form method="post">
						<input type="hidden" name="action" value="groupShare">
						<input type="hidden" name="id" value="{$k}">
						<input type="submit" value="Group Share">
						</form>
						{/if}

						{if $email_share}
                                                <form method="post">
                                                <input type="hidden" name="action" value="emailShare">
                                                <input type="hidden" name="id" value="{$k}">
                                                <input type="submit" value="Email Share">
                                                </form>
						{/if}

                                                <form method="post">
                                                <input type="hidden" name="action" value="deleteFile">
                                                <input type="hidden" name="id" value="{$k}">
                                                <input type="submit" value="Delete">
                                                </form>
	                                {/if}
	                </td></tr>
	                {/foreach}
			</table>
	
		{/if}

	<h2>Upload</h2>
        <div id="upload">
        	<div id="uploadFileList">Your browser doesn't have HTML5 support.</div>
                <br />
                <a id="uploadPickFiles" href="#">[Select files]</a>
                <a id="uploadFiles" href="#">[Upload files]</a>
        </div>

