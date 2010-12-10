<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>FileTrader</title>
<style type="text/css">
	@import url("s/style.css");
	@import url("s/plupload.queue.css");
</style>
<script type="text/javascript" src="ext/jquery.js"></script>
<script type="text/javascript" src="ext/plupload/js/plupload.min.js"></script>
<script type="text/javascript" src="ext/plupload/js/plupload.html5.min.js"></script>
<script type="text/javascript" src="ext/plupload/js/jquery.plupload.queue.min.js"></script>
<script type="text/javascript" src="j/plupload.js"></script>
</head>
<body>
		<div id="header">
			<p>
				<a href="?action=myFiles">Home</a> 
				{if $authenticated} 
				| Logged in as: <span title="{$userId}">{$userDisplayName}</span> | <a href="?action=logout">Logout</a>
				{/if}
			</p>
		</div>

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
