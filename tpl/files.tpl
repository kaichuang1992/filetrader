        <ul class="filelist">
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

	                {foreach key=k item=v from=$files}
	                <li>
						<a title="{$v.fileSize}, uploaded on {$v.fileDate|date_format:"%c"}" href="?action=downloadFile&id={$k}">
							{$v.fileName}
						</a>
	
	                                {if $type == "myFiles"}
						{if $group_share}
                                                <a id="share_{$k}" class="share" href="?action=groupShare&id={$k}">
                                                      <img src="i/group_share.png" width="16" height="16" alt="groupShare" title="Share with group" />
                                                </a>
						{/if}

						{if $email_share}
	                                        <a id="share_{$k}" class="share" href="?action=emailShare&id={$k}">
	  	                                      <img src="i/email_share.png" width="16" height="16" alt="emailShare" title="Share through email" />
	                                        </a>
						{/if}

						<a id="delete_{$k}" class="delete" href="?action=deleteFile&id={$k}">
							<img src="i/delete_file.png" width="16" height="16" alt="deleteFile" title="Delete file" />
						</a>
	                                {/if}
	                </li>
	                {/foreach}
	
		{/if}
        </ul>
