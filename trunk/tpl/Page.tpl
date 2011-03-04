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
	<div id="header">
	        {if $auth->isLoggedIn()}
	                <ul>
				<li><a href="?action=showFiles">Home</a></li>
				<li><a href="?action=fileUpload">Upload</a></li>
			</ul>
		        <span class="right"><span title="{$auth->getUserId()}">{$auth->getUserDisplayName()}</span> | <a href="?action=logout">Logout</a></span>
		{else}
			<span class="left">FileTrader</span>
		{/if}
        </div> <!-- /header -->
	
	{if $action == 'showFiles'}
	<div id="nav">
                {if $auth->isLoggedIn()}
	                <form method="get" class="change_group left">
	                        <input type="hidden" name="action" value="showFiles">
	                        <label>List {html_options name=group class=change_group options=$groups selected=$group}</label>
        	        </form>

                        <form method="get" class="right">
                                <input type="hidden" name="action" value="showFiles">
                                {if $group}
                                        <input type="hidden" name="group" value="{$group}">
                                {/if}
                                <label>Search Tag <input type="text" name="tag" size="10" /></label>
                        </form>
                {/if}
	</div> <!-- /nav -->
	{/if}

<div id="content">
        {if $error}
                <div id="error">
                        <p><strong>Error</strong>: {$errorMessage}</p>
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
