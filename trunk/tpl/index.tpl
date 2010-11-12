<html>
<head>
<title>FileTrader</title>
<meta name="viewport" content="width = 320" />
<style type="text/css">
	@import url("s/style.css");
        @import url("ext/plupload/examples/css/plupload.queue.css"); 
</style>
<script type="text/javascript" src="ext/jquery.js"></script>
{if $authenticated}
<script type="text/javascript" src="j/index.js"></script> 
{/if}
</head>
<body>
	{if $authenticated}
        <div id="userinfo">
                <strong title="{$userId}">{$userDisplayName}</strong>
                <br>
                <a href="#">Logout</a>
        </div>
	{/if}

	<h1>FileTrader</h1>

	<div id="navigation">
	<ul>
		{if $authenticated}
			<li class="myfiles"><a href="#">My Files</a></li>
			{if !empty($userGroups) }
			<li class="groupfiles"><a href="#">Group Files</a></li>
			{/if}
			<li class="uploadfiles"><a href="#">Upload Files</a></li>
		{else}
			<li class="login"><a href="#">Login</a></li>
		{/if}
	</ul>
	</div>

	<div id="content">
		{$content}
	</div>
</body>
</html>
