			{if empty($files)}
				<p>	
					No files in this collection.
				</p>
			{else}
				<form class="right change_view" method="get">
					<input type="hidden" name="action" value="showFiles">
					<input type="hidden" name="tag" value="{$tag}">
					<input type="hidden" name="group" value="{$group}">
                                	<label>View {html_options class="change_view" name=view options=$views selected=$view}</label>
				</form>

				{if $view == 'FileList'}	
					<table>
			                {foreach $files as $f}
					<tr>
						<td><a href="?action=downloadFile&amp;id={$f->value->_id}">{$f->value->fileName}</a></td>
						<td>{$f->value->fileSize|to_human_size}</td>
						<td>{$f->value->fileDate|date_format:"%d %b  %H:%M"}</td>
						<td><a href="?action=fileInfo&id={$f->value->_id}&amp;group={$group}"><img src="i/information.png" alt="File Info" title="File Info"></a></td>
					</tr>
			                {/foreach}
					</table>
				{elseif $view == 'MediaList'}
		                        <table>
		                        {foreach $files as $f}
		                                {if isset($f->value->video)}
		                                <tr>
                		                        <td class="thumbnail"><a href="?action=fileInfo&amp;id={$f->value->_id}"><img width="{$f->value->video->thumbnail->{90}->width}" height="90" src="?action=getCacheObject&amp;id={$f->value->_id}&type=thumbnail_90" /></a></td>
		                                        <td>
		                                                <strong>{$f->value->fileName}</strong>
								<br/>{$f->value->video->duration|to_duration}
		                                                <br/>{$f->value->fileDate|date_format:"%d %b  %H:%M"}
								<br/>
								{if $f->value->fileLicense != 'none'}
									<img src="i/{$f->value->fileLicense}.png" alt="{$f->value->fileLicense}" />
								{/if}
<!--		                                                <br/>Transcode Status: <strong>{$f->value->video->transcodeStatus}</strong> -->
		                                        </td>
		                                        <td>{$f->value->fileDescription}</td>
		                                </tr>
                		                {/if}
		                        {/foreach}
		                        </table>
				{/if}

                                {if $skip - $limit >= 0}
                                        <span class="left"><a href="?action=showFiles&view={$view}&tag={$tag}&group={$group}&skip={$skip-$limit}"><img src="i/resultset_previous.png" alt="Previous Page"></a></span>
                                {/if}
                                {if $skip + $limit < $no_of_files}
                                        <span class="right"><a href="?action=showFiles&view={$view}&tag={$tag}&group={$group}&skip={$skip+$limit}"><img src="i/resultset_next.png" alt="Next Page"></a></span>
                                {/if}

			{/if}
