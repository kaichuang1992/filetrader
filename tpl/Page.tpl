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
	<div id="header">
		<span class="left">FileTrader</span>			
        {if $authenticated} 
		<span class="right">| User: <span title="{$userId}">{$userDisplayName}</span> | <a href="?action=logout">Logout</a> |</span>
	{/if}

	</div>

	<ul class="menu">
		<li><a href="?action=myFiles">Home</a></li>
		<li><a href="?action=fileUpload">Upload Files</a></li>
	</ul>

	{if $error} 
		<div id="error">
			<p>Error: {$errorMessage}</p>
		</div>
	{else}
		<div id="content">
		{$container}
		</div>
	{/if}
</body>
</html>
