		{if empty($files)}
			<p>	
				You did not upload any files yet. Please upload some files :)
			</p>
		{else}
			<table>
	                {foreach $files as $f}
			<tr>
				<td><a href="?action=downloadFile&amp;id={$f->value->_id}">{$f->value->fileName}</a></td>
				<td>{$f->value->fileSize}</td>
				<td>{$f->value->fileDate|date_format:"%d %b  %H:%M"}</td>
				<td><a href="?action=fileInfo&id={$f->value->_id}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
			</tr>
	                {/foreach}
			</table>
			<h2>Tag Frequencies</h2>
			{foreach $tagInfo as $tag}
				{$tag->key[1]} ({$tag->value}) <br />
			{/foreach}
		{/if}
