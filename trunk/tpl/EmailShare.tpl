			<h2>Share</h2>

			<p>Here you can enter email addresses of people you want to share the file "<strong>{$fileName}</strong>" with.</p>

			<form method="post" action="?action=updateEmailShare">

			<label>Email Address <input title="The addressee(s) will receive an URL through email containing a special token to download this file after logging in" type="text" name="address" /></label>
			<input type="hidden" name="id" value="{$id}" />
			<input type="submit" value="Share" />
			</form>

                        {if !empty($tokens)}
				<h2>Shared With</h2>
				<p>Currently this file is shared with the following people.</p>
				<table width="100%">
        	                        <tr>
		                                {foreach key=k item=v from=$tokens}
	                                        <td>{$v}</td>
						<td>
							<a href="?action=deleteToken&id={$id}&token={$k}">
                	                                        <img src="i/delete_file.png" width="16" height="16" alt="Delete Token" title="Delete Token" />
                        	                        </a>
						</td>
		                                {/foreach}
	                                </tr>
				</table>		
                        {/if}
