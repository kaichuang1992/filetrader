			<h2>Share</h2>

			<p>Here you can enter email addresses of people you want to share the file "<strong>{$fileName}</strong>" with.</p>

			<form method="post">
			<label>Email Address <input title="The addressee(s) will receive an URL through email containing a special token to download this file after logging in" type="text" name="address" /></label>
			<input type="hidden" name="id" value="{$id}" />
                        <input type="hidden" name="action" value="updateEmailShare">
			<input type="submit" value="Share" />
			</form>

                        {if !empty($tokens)}
				<h2>Shared With</h2>
				<p>Currently this file is shared with the following people.</p>
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
