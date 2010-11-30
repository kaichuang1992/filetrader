			<h2>Email Share</h2>

			<p>Sharing file "<strong>{$fileName}</strong>".</p>

			{if !empty($tokens)}
				<h3>Currently invited</h3>
				<form method="post">
				<input type="hidden" name="action" value="deleteToken">
				<input type="hidden" name="id" value="{$id}">
		                        {foreach key=k item=v from=$tokens}
						<label><input type="checkbox" title="Mark this invite for deletion" name="token[]" value="{$k}"> {$v}</label>
		                        {/foreach}
				<input class="button" type="submit" title="Delete the selected invites" value="Delete Invites">
				</form>
                        {/if}

			<h3>New invite</h3>
                        <form method="post">
                        <label>Send a new invite to <input title="Address to send an invite to" type="text" name="address" /></label>
                        <input type="hidden" name="id" value="{$id}" />
                        <input type="hidden" name="action" value="updateEmailShare">
                        <input type="submit" value="Invite" />
                        </form>
			</p>

