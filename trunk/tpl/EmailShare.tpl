			<h2>Email Share</h2>

			<p>Here you can share the file "<strong>{$fileName}</strong>" by sending email invites.</p>

			<form method="post">
			<label>Email Address <input title="Address to send an invite to" type="text" name="address" /></label>
			<input type="hidden" name="id" value="{$id}" />
                        <input type="hidden" name="action" value="updateEmailShare">
			<input type="submit" value="Share" />
			</form>

                        {if !empty($tokens)}
				<h2>Shared With</h2>
				<p>You sent an invite the the following email addresses:</p>
				<form method="post">
				<input type="hidden" name="action" value="deleteToken">
				<input type="hidden" name="id" value="{$id}">
				<table>
        	                        <tr><th>Delete</th><th>Email Address</th></tr>
		                        {foreach key=k item=v from=$tokens}
					<tr>
						<td>
							<input type="checkbox" title="Mark this token for deletion" name="token[]" value="{$k}">
						</td>
                                        	<td>{$v}</td>
					</tr>
		                        {/foreach}
				</table>
				<input type="submit" title="Delete the selected tokens" value="Delete Tokens">
                        {/if}
