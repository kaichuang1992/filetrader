                        {if !empty($fileInfo->video->transcode->{360}) && $fileInfo->video->transcodeStatus == 'DONE' && !empty($fileInfo->video->thumbnail->{360})}
				<script type="text/javascript" src="ext/video-js/video.js"></script>
                                <script type="text/javascript">
					$(document).ready(function() {
						$("video").VideoJS()
					});
				</script>
                                <style type="text/css">
                                        @import url("ext/video-js/video-js.css");
                                </style>
				

				  <!-- Begin VideoJS -->
				  <div class="video-js-box">
				    <!-- Using the Video for Everybody Embed Code http://camendesign.com/code/video_for_everybody -->
				    <video id="video_1" class="video-js" width="{$fileInfo->video->transcode->{360}->width}" height="360" controls="controls" preload="auto" poster="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360">
				      <source src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=transcode_360" type='video/webm; codecs="vp8, vorbis"' />
				    </video>
				    <!-- Download links provided for devices that can't play video in the browser. -->
				    <p class="vjs-no-video"><strong>Download Video:</strong>
				      <a href="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=transcode_360">WebM</a>,
				      <!-- Support VideoJS by keeping this link. -->
				      <a href="http://videojs.com">HTML5 Video Player</a> by VideoJS
				    </p>
				  </div>
				  <!-- End VideoJS -->
			{elseif !empty($fileInfo->video->thumbnail->{360})}
                                <img width="{$fileInfo->video->thumbnail->{360}->width}" height="360" src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360" alt="Video Still" title="Video Still">
                        {/if}

			<h2>Info</h2>

			<form method="post">

                        <input type="hidden" name="id" value="{$fileInfo->_id}" />
                        <input type="hidden" name="action" value="updateFileInfo">

			<table class="fileInfo">
			<tr><th>Name</th><td><input type="text" size="50" name="fileName" value="{$fileInfo->fileName}"/></td></tr>
			<tr><th>Size</th><td>{$fileInfo->fileSize|to_human_size}</td></tr>
			{if isset($fileInfo->video)}
				{if $fileInfo->video->transcodeStatus != 'DONE'}
					<tr><th>Transcoding</th>
					<td>{$fileInfo->video->transcodeStatus}
						{if $fileInfo->video->transcodeStatus == 'PROGRESS'}
							(<strong>{$fileInfo->video->transcodeProgress}%</strong>)
						{/if}</td></tr>
				{/if}
			{/if}
			<tr><th>Tags</th><td><input type="text" size="50" name="fileTags" value="{', '|implode:$fileInfo->fileTags}" /></td></tr>
			<tr><th>License</th><td>{html_options name='fileLicense' options=$licenses selected=$fileInfo->fileLicense}</td></tr>
			<tr><th>Description</th><td><textarea name="fileDescription" rows="5" cols="55">{$fileInfo->fileDescription}</textarea></td></tr>
			{if !empty($userGroups)}
			<tr><th>Sharing</th><td>
				<label><input class="share_public" type="checkbox" name="filePublic" {if $fileInfo->filePublic} checked="checked" {/if}><strong>Public</strong></label>
				{html_checkboxes class='share_group' name='fileGroups' options=$userGroups selected=$fileInfo->fileGroups}</td></tr>
			{/if}

                        <tr><th>Email Tokens</th><td>
                                <textarea rows="5" cols="55" name="fileTokens">{', '|implode:array_values((array)$fileInfo->fileTokens)}</textarea></td></tr>

			<tr><td colspan="2">
				<input type="submit" value="Update" name="buttonPressed">
                                <input type="submit" value="Delete" name="buttonPressed">
			</td></tr>
			</table>
			</form>

                        <p><small>[DEBUG] <a href="?action=rawFileInfo&amp;id={$fileInfo->_id}">Raw File Info</a></small></p>

