<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>FileTrader</title>
<style type="text/css">
	@import url("s/style.css");
</style>
<script type="text/javascript" src="ext/jquery.js"></script>
</head>
<body>
	{if $authenticated}
		<span class="auth"><span title="{$userId}">{$userDisplayName}</span> | <a href="?action=logout">Logout</a></span>
        {/if}

	<div id="header">
	        <ul class="menu">
			<li>
				<a href="?action=showFiles&view=FileList">Files</a>
			</li>

			<li>
				<a href="?action=showFiles&view=MediaList">Videos</a>
			</li>

                        <li>
                                <a href="?action=fileUpload">Upload New Files</a>
                        </li>   
	        </ul>

                {if $action == 'showFiles'}
		        <form method="get">
	                        <input type="hidden" name="action" value="showFiles">
				<input type="hidden" name="view" value="{$view}">
	                        <label>Search for Tag <input type="text" name="tag" size="10" /></label>
                        </form>
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
