	<script type="text/javascript" src="j/files.js"></script>

        <ul class="filelist">
		{if empty($files)}
			<p>	
			{if $type == "myfiles"}
				You did not upload any files yet. Click on the <strong>Upload</strong> tab to upload some.
			{else}
				No files were shared with the group(s) you are a member of.
			{/if}	
			</p>

		{else}
			<p>
		        {if $type == "myfiles"}
		                These are the files you uploaded.
		        {else}
		                These are the files others have shared with the group(s) you are a member of.
			{/if}
			</p>

	                {foreach key=k item=v from=$files}
	                <li>
						<a title="{$v.fileSize}, uploaded on {$v.fileDate|date_format:"%c"}" href="?action=download&id={$k}">
							{$v.fileName}
						</a>
	
	                                {if $type == "myfiles"}
	                                        <a id="share_{$k}" class="share" href="#">
	  	                                      <img src="i/share.png" width="16" height="16" alt="Share" title="Share" />
	                                        </a>
						<a id="delete_{$k}" class="delete" href="#">
							<img src="i/delete.png" width="16" height="16" alt="Delete" title="Delete" />
						</a>
	                                {/if}
	
	
						<ul class="groups"></ul>
	                </li>
	                {/foreach}
	
		{/if}
        </ul>
