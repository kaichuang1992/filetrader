{if empty($files)}
	<p>	
		<em>No files in this collection</em>
	</p>
{else}
        {if $skip - $limit >= 0}
                <span class="left"><a href="?action=showFiles&amp;tag={$tag}&amp;group={$group}&amp;skip={$skip-$limit}"><img src="i/resultset_previous.png" alt="Previous Page"></a></span>
        {/if}
        
        {if $skip + $limit < $no_of_files}
                <span class="right"><a href="?action=showFiles&amp;tag={$tag}&amp;group={$group}&amp;skip={$skip+$limit}"><img src="i/resultset_next.png" alt="Next Page"></a></span>
        {/if}

	<form method="post">
		<input type="hidden" name="action" value="updateFileInfo">
		<table>
	        {foreach $files as $f}
			<tr>
				<td><input type="checkbox" name="markedFiles[]" value="{$f->id}" /></td>
                                <td><a href="?action=fileInfo&amp;id={$f->id}&amp;group={$group}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
				<td><a href="?action=downloadFile&amp;id={$f->id}" title="{$f->value->fileName}">{$f->value->fileName|truncate:40:'...':true:true}</a></td>
				<td>{$f->value->fileSize|to_human_size}</td>
				<td>{$f->value->fileDate|date_format:"%d %b  %H:%M"}</td>
			</tr>
		{/foreach}
		</table>
		<input type="submit" value="Delete Files" name="buttonPressed">
	</form>
{/if}
