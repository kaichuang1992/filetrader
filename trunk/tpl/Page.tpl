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
	        {if isset($auth) && $auth->isLoggedIn()}
	                <ul>
				<li><a href="?action=showFiles">Home</a></li>
				<li><a href="?action=fileUpload">Upload</a></li>
				<li><a href="?action=logout">Logout</a> <span class="small" title="{$auth->getUserID()}">({$auth->getUserDisplayName()})</span></li>
			</ul>
		{else}
			<strong>FileTrader</strong>
		{/if}
        </div> <!-- /header -->
	
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
