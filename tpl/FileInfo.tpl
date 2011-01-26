			<h2>Info</h2>
			<form method="post">
		
			<a id="debugButton" href="?action=rawFileInfo&id={$id}">Raw Info</a>

                        <input type="hidden" name="id" value="{$id}" />
                        <input type="hidden" name="action" value="updateFileInfo">

			<table class="fileInfo">
			<tr><th>Name</th><td><input type="text" name="fileName" value="{$fileName}"/></td></tr>
			<tr><th>Size</th><td>{$fileSize}</td></tr>
			<tr><th>Tags</th><td><input type="text" name="fileTags" value="{$fileTags}" /></td></tr>
			<tr><th>Description</th><td><textarea name="fileDescription" rows="5" cols="30">{$fileDescription}</textarea></td></tr>
			<tr><td colspan="2"><input type="submit" value="Update"></td></tr>
			</table>
			</form>

                        <h2>Sharing</h2>
                        {if !empty($tokens)}
                                <form method="post">
				<fieldset>
				<legend>Invited</legend>
                                <input type="hidden" name="action" value="deleteToken">
                                <input type="hidden" name="id" value="{$id}">
                                        {foreach key=k item=v from=$tokens}
                                                <label><input type="checkbox" title="Mark this invite for deletion" name="token[]" value="{$k}"> {$v}</label>
                                        {/foreach}
                                <input class="button" type="submit" title="Delete the selected invites" value="Delete Invites">
				</fieldset>
                                </form>
                        {/if}

                        <form method="post">
			<fieldset>
			<legend>New Invite</legend>
                        <label>Send a new invite to <input title="Address to send an invite to" type="text" name="address" /></label>
                        <input type="hidden" name="id" value="{$id}" />
                        <input type="hidden" name="action" value="updateEmailShare">
                        <input type="submit" value="Invite" />
			</fieldset>
                        </form>
