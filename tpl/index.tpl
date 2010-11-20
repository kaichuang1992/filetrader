<html>
<head>
<title>FileTrader</title>
<style type="text/css">
	@import url("s/style.css");
</style>
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

	<div id="navigation">
	<ul>
		{if $authenticated}
			<li><a href="?action=myFiles">My Files</a></li>

			{if $share_groups} 
				<li><a href="?action=groupFiles">Group Files</a></li>
			{/if}
			<li><a href="?action=uploadFiles">Upload</a></li>
		{/if}
	</ul>
	</div>

	<div id="content">
	        {if $error}
	                <div class="error">
	                        {$errorMessage}
	                </div>
		{/if}

		{$content}
	</div>
</body>
</html>
