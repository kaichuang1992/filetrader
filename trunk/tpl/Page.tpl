<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>FileTrader</title>
<style type="text/css">
	@import url("s/style.css");
</style>
<script type="text/javascript" src="ext/jquery.js"></script>
<script type="text/javascript" src="j/filetrader.js"></script>
</head>
<body>
	{if $authenticated}
		<span class="auth"><span title="{$userId}">{$userDisplayName}</span> | <a href="?action=logout">Logout</a></span>
        {/if}

	<div id="header">
		{if $authenticated}

		<form method="get" class="change_group left">
			<input type="hidden" name="action" value="showFiles">
			<input type="hidden" name="view" value="{$view}">
                        <label>Collection {html_options name=group class=change_group options=$groups selected=$group}</label>
		</form>

                {if $action == 'showFiles'}
		        <form method="get" class="right">
	                        <input type="hidden" name="action" value="showFiles">
				<input type="hidden" name="view" value="{$view}">
				{if $group}
					<input type="hidden" name="group" value="{$group}">
				{/if}
	                        <label>Search for Tag <input type="text" name="tag" size="10" /></label>
                        </form>
		{/if}
		{else}
			Welcome to FileTrader
		{/if}		
        </div> <!-- /header -->
        
<div id="content">
        {if $error}
                <div id="error">
                        <p>Error: {$errorMessage}</p>
                </div> <!-- /error -->
        {else}
		{$container}
        {/if}
        </div> <!-- /content -->

	<div id="footer">
		Powered by <a href="http://filetrader.googlecode.com">FileTrader</a>
	</div> <!-- /footer -->
</body>
</html>
