	        <script type="text/javascript" src="j/share.js"></script>
		{if $group_share && !empty($groups)}
                <li>
			<form>
			<fieldset>
			<legend>Groups</legend>
                        {html_checkboxes name=groups selected=$sharegroups class=$id title='Select the groups you want to share this file with' options=$groups separator='<br />'}
			</fieldset>
			</form>
                </li>
		{/if}
		{if $email_share}
		<li>
			<form>
			<fieldset>
			<legend>Email</legend>
			<label>Address <input title="The adressee(s) will receive an URL through email containing a special token to download this file after logging in" type="text" name="address" /></label>
			<input type="button" class="{$id}" value="Share" />
			<br>
                        {if !empty($tokens)}
                                Already shared with:
                                <ul>
                                {foreach key=k item=v from=$tokens}
                                        <li>{$v}

<a id="delete_{$id}_{$k}" class="delete" href="#">
                                                        <img src="i/delete.png" width="16" height="16" alt="Delete Token" title="Delete Token" />
                                                </a>
					</li>
                                {/foreach}
                                </ul>
                        {/if}
			</fieldset>
			</form>
		</li>
		{/if}

