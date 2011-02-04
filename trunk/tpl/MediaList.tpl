		{if empty($files)}
			<p>	
				You did not upload any media files yet. Please upload some files :)
			</p>
		{else}
			<table>
	                {foreach $files as $f}
			<tr>
				<td class="thumbnail"><a href="?action=downloadFile&amp;id={$f->value->_id}"><img src="?action=getCacheObject&amp;id={$f->value->_id}&type=thumbnail_90" /></a></td>
				<td>{$f->value->fileName}</td>
				<td>{$f->value->fileDescription}</td>
				<td>{$f->value->fileDate|date_format:"%d %b  %H:%M"}</td>
				<td><a href="?action=fileInfo&id={$f->value->_id}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
			</tr>
	                {/foreach}
			</table>
		{/if}
