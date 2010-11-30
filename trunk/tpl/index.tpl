<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>FileTrader</title>
<style type="text/css">
	@import url("s/style.css");
</style>
<script type="text/javascript" src="ext/jquery.js"></script>
<script type="text/javascript" src="ext/plupload/js/plupload.min.js"></script>
<script type="text/javascript" src="ext/plupload/js/plupload.html5.min.js"></script>
<script type="text/javascript" src="ext/plupload/js/jquery.plupload.queue.min.js"></script>
<script type="text/javascript" src="j/upload.js"></script>
</head>
<body>
	<p class="header"><a href="?action=myFiles">Home</a> 
	{if $authenticated} 
		| Logged in as: <span title="{$userId}">{$userDisplayName}</span> | <a href="?action=logout">Logout</a>
	{/if}
	</p>

	{if $error} 
		<div class="error">
			<p>Error: {$errorMessage}</p>
		</div>
	{else}
		{$container}
	{/if}
</body>
</html>
