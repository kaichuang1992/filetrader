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
	<p>You can login using the account provided to you by your organization.</p>

	<form method="post">
		<input id="input_focus" type="submit" value="Proceed" />
		<input type="hidden" name="samlProceed" value="TRUE" />
	</form>
</div>
