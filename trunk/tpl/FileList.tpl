		{if empty($files)}
			<p>	
				You did not upload any files yet. Please upload some files :)
			</p>
		{else}
			<table>
	                {foreach key=k item=v from=$files}
			<tr>
				<td><a href="?action=downloadFile&amp;id={$k}">{$v.fileName}</a></td>
				<td>{$v.fileSize}</td>
				<td>{$v.fileDate|date_format:"%d %b  %H:%M"}</td>
				<td><a href="?action=fileInfo&id={$k}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
			</tr>
	                {/foreach}
			</table>
		{/if}
		<p class="usage">You are using {$totalSize} of storage :-)</p>
