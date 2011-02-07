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
			{if $action == "myFiles"}
	                	<li class="selected">
			{else}
				<li>
			{/if}
				<a href="?action=myFiles">My Files</a>
			</li>

                        {if $action == "myMedia"}
                                <li class="selected">
                        {else}
                                <li>
                        {/if}
                                <a href="?action=myMedia">My Media</a>
                        </li>   

                        {if $action == "groupFiles"}
                                <li class="selected">
                        {else}
                                <li>
                        {/if}
                                <a href="?action=groupFiles">Group Files</a>
                        </li>   

                        {if $action == "fileUpload"}
                                <li class="selected">
                        {else}
                                <li>
                        {/if}
                                <a href="?action=fileUpload">Upload New Files</a>
                        </li>   
	        </ul>
        </div> <!-- /header -->

	{if $error} 
		<div id="error">
			<p>Error: {$errorMessage}</p>
		</div> <!-- /error -->
	{else}
		<div id="content">
		{$container}
		</div> <!-- /content -->
	{/if}

	<div id="footer">
		This Site is Using <a href="http://filetrader.googlecode.com">FileTrader</a> :)
	</div>
</body>
</html>
