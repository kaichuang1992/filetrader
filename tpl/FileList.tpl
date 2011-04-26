<div class="filelist">
{if empty($files)}
	<div class="filelist_nofiles">No files in this collection</div>
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
				<td>
				{if isset($f->value->fileTags)}
				{foreach $f->value->fileTags as $t} 
					<a class="filelist_tag" href="?action=showFiles&amp;tag={$t}" title="Tag: {$t}">{$t}</a>
				{/foreach}
				{/if}

                                {if isset($f->value->fileGroups)}
                                {foreach $f->value->fileGroups as $g}
                                        <a class="filelist_group" href="?action=showFiles&amp;group={$g}" title="Group: {$myGroups[$g]}">{$myGroups[$g]}</a>
                                {/foreach}
                                {/if}
				<a class="filelist_file" href="?action=fileInfo&amp;id={$f->id}" title="{$f->value->fileName}">{$f->value->fileName|truncate:40:'...':true:true}</a> <span class="filelist_size">({$f->value->fileSize|to_human_size}, {$f->value->fileDate|date_format:"%d %b %Y  %H:%M"})</span></td>
			</tr>
		{/foreach}
		</table>
		<div class="filelist_controls">
			<input type="submit" value="Delete Files" name="buttonPressed">
		</div> <!-- /filelist_controls -->
	</form>
{/if}
</div>

<div class="filelist_filter">
	<form class="change_group">
	<label>List {html_options name=group class=change_group options=$groups selected=$group}</label>
	<label>Search Tag <input type="text" name="tag" size="10" value="{$tag}" /></label>
	</form>
</div> 
