<script type="text/javascript" src="j/auth.js"></script>
<style type="text/css">
        @import url("s/auth.css");
</style>
{if $error}
        <div class="error">
                {$errorMessage}
        </div>
{/if}
<div class="login">
	<h2>Login</h2>
	<p>You can login using your Google account.</p>
	<form method="post">
		<input type="hidden" name="openid_identifier" value="https://www.google.com/accounts/o8/id">
		<input class="proceed" id="input_focus" type="submit" value="Google Login" />
	</form>
</div>
