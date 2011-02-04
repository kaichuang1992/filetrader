                        {if !empty($fileInfo->video->transcode->{360}) && $fileInfo->video->transcodeStatus == 'DONE' && !empty($fileInfo->video->thumbnail->{360})}
				<h2>Video</h2>
                                <video controls preload poster="index.php?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360">
                                        <source src="index.php?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=transcode_360" type='video/webm; codecs="vp8, vorbis"'>
                                </video>
			{elseif !empty($fileInfo->video->thumbnail->{360})}
                                <h2>Still</h2>
                                <img src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360" alt="Video Still" title="Video Still">
                        {/if}

			<h2>Info</h2>
			<form method="post">
		
			<a id="debugButton" href="?action=rawFileInfo&amp;id={$fileInfo->_id}">Raw Info</a>

                        <input type="hidden" name="id" value="{$fileInfo->_id}" />
                        <input type="hidden" name="action" value="updateFileInfo">

			<table class="fileInfo">
			<tr><th>Name</th><td><input type="text" size="50" name="fileName" value="{$fileInfo->fileName}"/></td></tr>
			<tr><th>Size</th><td>{$fileInfo->fileSize}</td></tr>
			<tr><th>Tags</th><td><input type="text" size="50" name="fileTags" value="{', '|implode:$fileInfo->fileTags}" /></td></tr>
			<tr><th>Description</th><td><textarea name="fileDescription" rows="5" cols="55">{$fileInfo->fileDescription}</textarea></td></tr>
			<tr><th>Groups</th><td>{html_checkboxes name='fileGroups' options=$userGroups selected=$fileInfo->fileGroups}</td></tr>
			<tr><td colspan="2"><input type="submit" value="Update"></td></tr>
			</table>
			</form>
