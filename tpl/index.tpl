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
	{if $authenticated}
        <div id="userinfo">
                <strong title="{$userId}">{$userDisplayName}</strong>
                <br>
                <a href="?action=logout">Logout</a>
        </div>
	{/if}

	<h1>FileTrader</h1>

	{if $error} 
		<div class="error">
			<p>{$errorMessage}</p>
		</div>
	{else}
		<div id="container">
			{$container}
		</div>
	{/if}

</body>
</html>
