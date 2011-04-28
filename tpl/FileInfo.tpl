<div class="thumbnail">
{if $hasThumb}
	<img width="{$fileInfo->image->thumbnail->{360}->width}" height="{$fileInfo->image->thumbnail->{360}->height}" src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=image_thumbnail_360" alt="Thumbnail of {$fileInfo->fileName}"/>
{/if}

{if $hasVideo}
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
        <video id="video_1" class="video-js" width="{$fileInfo->video->transcode->{360}->width}" height="{$fileInfo->video->transcode->{360}->height}" controls="controls" preload="auto" poster="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=video_thumbnail_360">
        	<source src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=video_transcode_360" type='video/webm; codecs="vp8, vorbis"' />
        </video>
        <!-- Download links provided for devices that can't play video in the browser. -->
        <p class="vjs-no-video"><strong>Download Video:</strong>
        	<a href="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=video_transcode_360">WebM</a>,
                <!-- Support VideoJS by keeping this link. -->
                <a href="http://videojs.com">HTML5 Video Player</a> by VideoJS
        </p>
</div>
<!-- End VideoJS -->
{elseif $hasStill}
	<img width="{$fileInfo->video->thumbnail->{360}->width}" height="{$fileInfo->video->thumbnail->{360}->height}" src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=video_thumbnail_360" alt="Still of {$fileInfo->fileName}">
{elseif $hasAudio}
<audio controls="controls" preload="auto">
	<source src="?action=getCacheObject&amp;id={$fileInfo->_id}&amp;type=audio_transcode" type='audio/ogg; codecs="vorbis"' />
</audio>
{/if}

</div> <!-- /thumbnail -->

<div class="fileinfo">
	<form method="post">
		<input type="hidden" name="id" value="{$fileInfo->_id}" />
		<input type="hidden" name="action" value="updateFileInfo">

		<table>
			<tr>
				<th>Name {if $isOwner}<img src="i/pencil.png" alt="Edit" />{/if}</th>
				<td>
					<span class="showView">{$fileInfo->fileName}</span>
					<input class="editView" type="text" size="50" name="fileName" value="{$fileInfo->fileName}"/>
				</td>
			</tr>

                        {if isset($fileInfo->video)}
			<tr>
				<th>Duration</th>
				<td>{$fileInfo->video->duration|to_duration}</td>
			</tr>
			{elseif isset($fileInfo->audio)}
                        <tr>
                                <th>Duration</th>
                                <td>{$fileInfo->audio->duration|to_duration}</td>
                        </tr>
			{/if}

			<tr>
				<th>Size</th>
				<td>
					{$fileInfo->fileSize|to_human_size}
				</td>
			</tr>

                        <tr>
                                <th>Date</th>
                                <td>
                                        {$fileInfo->fileDate|date_format:"%d %b %Y  %H:%M"}
                                </td>
                        </tr>

			<tr>
				<th>Tags {if $isOwner}<img src="i/pencil.png" alt="Edit" />{/if}</th>
				<td>
					<span class="showView">
					{foreach $fileInfo->fileTags as $tag} 
						<a class="fileinfo_tag" href="?action=showFiles&tag={$tag}">{$tag}</a>
					{/foreach}
					</span>
					<input class="editView" type="text" size="50" name="fileTags" value="{', '|implode:$fileInfo->fileTags}" />
				</td>
			</tr>

			<tr>
				<th>License {if $isOwner}<img src="i/pencil.png" alt="Edit" />{/if}</th>
				<td>
					<span class="showView">
                                        		<img src="i/{$fileInfo->fileLicense}.png" title="{$allLicenses[$fileInfo->fileLicense]}" alt="{$allLicenses[$fileInfo->fileLicense]}" />
					</span>
					<span class="editView">
						{html_options name='fileLicense' options=$allLicenses selected=$fileInfo->fileLicense}
					</span>
				</td>
			</tr>

			<tr>
				<th>Description {if $isOwner}<img src="i/pencil.png" alt="Edit" />{/if}</th>
				<td>
					<span class="showView">{$fileInfo->fileDescription}</span>
					<textarea class="editView" name="fileDescription" rows="5" cols="55">{$fileInfo->fileDescription}</textarea>
				</td>
			</tr>

			{if (isset($fileInfo->video) || isset($fileInfo->audio)) && $fileInfo->transcodeStatus != 'DONE'}
		        <tr>
                    		<th>Transcode</th>
                        	<td>
                                        {if $fileInfo->transcodeStatus == 'PROGRESS'}
                                                <em>{$fileInfo->transcodeProgress}%</em>
					{elseif $fileInfo->transcodeStatus == 'WAITING'}
						<em>Waiting</em>
                                        {/if}
	                        </td>
        	        </tr>
			{/if}   

			{if $isOwner && $groupShare && !empty($userGroups)}
                        <tr>
				<th>Group Sharing</th>
				<td>
        	                	{html_checkboxes name='fileGroups' options=$userGroups selected=$fileInfo->fileGroups}
				</td>
			</tr>
			{/if}

			{if $isOwner && $emailShare}
                        <tr>
				<th>Email Invites {if $isOwner}<img src="i/pencil.png" alt="Edit" />{/if}</th>
				<td>
					<span class="showView">{', '|implode:array_values((array)$fileInfo->fileTokens)}</span>				
					<textarea class="editView" rows="5" cols="55" name="fileTokens">{', '|implode:array_values((array)$fileInfo->fileTokens)}</textarea>
				</td>
			</tr>
			{/if}
		</table>
                <div class="fileinfo_controls">
	                {if $isOwner}
	     	       		<input type="submit" value="Update" name="buttonPressed">
				<input type="submit" value="Delete" name="buttonPressed">
				<input type="submit" value="Share" name="buttonPressed">
	                        <input type="submit" value="Reexamine" name="buttonPressed">
	                {/if}
                	<input type="submit" value="Download" name="buttonPressed">
                </div> <!-- /fileinfo_controls -->

	</form>
</div> <!-- /fileinfo -->
