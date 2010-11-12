<script type="text/javascript" src="j/auth.js"></script>
<style type="text/css">
        @import url("s/auth.css");
</style>
<div class="login">
	<h2>Login</h2>
        <p>You can login using your OpenID account.</p>

	<form method="post">
		{if !empty($domains)}
			{html_options class=provider name=domain options=$domains}
		{/if}	
	        <label class="identifier">Account <input type="text" name="openid_identifier" /></label>
		<input type="submit" value="Proceed" />
	</form>
</div>
