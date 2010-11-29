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
				<table>
        	                        <tr><th>Email Address</th><th>Action</th></tr>
		                        {foreach key=k item=v from=$tokens}
					<tr>
	                                       <td>{$v}</td>
						<td>
			                                <form method="post" action="index.php">
								<input type="hidden" name="action" value="deleteToken">
								<input type="hidden" name="id" value="{$id}">
								<input type="hidden" name="token" value="{$k}">
								<input type="submit" value="Delete">
							</form>
						</td>
					</tr>
		                        {/foreach}
				</table>		
                        {/if}
