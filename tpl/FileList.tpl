		{if empty($files)}
			<p>	
				You did not upload any files yet. Please upload some files :)
			</p>
		{else}
			<table>
	                {foreach key=k item=v from=$files}
			<tr>
				<td><a href="?action=downloadFile&amp;id={$v->value->_id}">{$v->value->fileName}</a></td>
				<td>{$v->value->fileSize}</td>
				<td>{$v->value->fileDate|date_format:"%d %b  %H:%M"}</td>
				<td><a href="?action=fileInfo&id={$v->value->_id}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
			</tr>
	                {/foreach}
			</table>
			<h2>Debug</h2>
			{foreach key=k item=v from=$tagInfo}
				{$v->key} ({$v->value}) <br />
			{/foreach}
		{/if}
