                        {if !empty($fileInfo->video->transcode->{360}) && $fileInfo->video->transcodeStatus == 'DONE' && !empty($fileInfo->video->thumbnail->{360})}
				<h2>Video</h2>

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
				    <video id="video_1" class="video-js" width="640" height="360" controls="controls" preload="auto" poster="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360">
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
                                <h2>Still</h2>
                                <img width="{$fileInfo->video->thumbnail->{360}->width}" height="360" src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=thumbnail_360" alt="Video Still" title="Video Still">
                        {/if}

			<h2>Info</h2>

			<form method="post">

                        <input type="hidden" name="id" value="{$fileInfo->_id}" />
                        <input type="hidden" name="action" value="updateFileInfo">

			<table class="fileInfo">
			<tr><th>Name</th><td><input type="text" size="50" name="fileName" value="{$fileInfo->fileName}"/></td></tr>
			<tr><th>Size</th><td>{$fileInfo->fileSize|to_human_size}</td></tr>
			<tr><th>Tags</th><td><input type="text" size="50" name="fileTags" value="{', '|implode:$fileInfo->fileTags}" /></td></tr>
			<tr><th>Description</th><td><textarea name="fileDescription" rows="5" cols="55">{$fileInfo->fileDescription}</textarea></td></tr>
			<tr><th>Groups</th><td>{html_checkboxes name='fileGroups' options=$userGroups selected=$fileInfo->fileGroups}</td></tr>
			<tr><td colspan="2"><input type="submit" value="Update"></td></tr>
			</table>
			</form>

                        <p><small>[DEBUG] <a href="?action=rawFileInfo&amp;id={$fileInfo->_id}">Raw File Info</a></small></p>

