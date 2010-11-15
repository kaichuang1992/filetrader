<script type="text/javascript" src="j/auth.js"></script>
<style type="text/css">
        @import url("s/auth.css");
</style>
{if $error }
	<div class="error">
		{$errorMessage}
	</div>
{/if}
<div class="login">
	<h2>Login</h2>
        <p>You can login using your OpenID account. If you select a domain you only need to provide your user account for that domain.</p>

	<form method="post">
		<label class="domain">Domain 
		{if !empty($domains)}
			{html_options class=provider name=domain options=$domains}
		{/if}	
		</label>
	        <label class="identifier">Identifier <input type="text" name="openid_identifier" /></label>
		<input class="proceed" type="submit" value="Proceed" />
	</form>
</div>
