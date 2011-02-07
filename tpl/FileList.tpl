		{if isset($user_groups)}
			{if !empty($user_groups)}
				Select a group for which you want to see the files. They will include the files you shared yourself as well!
		
				<ul>
					{foreach $user_groups as $k => $v}
					<li><a href="?action=groupFiles&selected_group={$k}">{$v}</a></li>
					{/foreach}
				</ul>
			{else}
				<em>You are not a member of any group. Nothing to see here.</em>
			{/if}
		{else}
			{if empty($files)}
				<p>	
					You did not upload any files yet. Please upload some files :)
				</p>
			{else}
				{if $skip - $limit >= 0}
					<span class="left"><a href="?action=myFiles&skip={$skip-$limit}"><img src="i/resultset_previous.png" alt="Previous Page"></a></span>
				{/if}
				{if $skip + $limit < $no_of_files}
					<span class="right"><a href="?action=myFiles&skip={$skip+$limit}"><img src="i/resultset_next.png" alt="Next Page"></a></span>
				{/if}
	
				<table>
		                {foreach $files as $f}
				<tr>
					<td><a href="?action=downloadFile&amp;id={$f->value->_id}">{$f->value->fileName}</a></td>
					<td>{$f->value->fileSize|to_human_size}</td>
					<td>{$f->value->fileDate|date_format:"%d %b  %H:%M"}</td>
					<td><a href="?action=fileInfo&id={$f->value->_id}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
				</tr>
		                {/foreach}
				</table>
			{/if}
		{/if}

