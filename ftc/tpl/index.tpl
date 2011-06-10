<!DOCTYPE html>
<head>
<meta charset="utf8">
<title>ftc</title>
<style type="text/css">
	@import url(s/style.css);
</style>
<script type="text/javascript" src="j/jquery.js"></script>
<script type="text/javascript" src="j/ftc.js"></script>
</head>
<body>
<h1>File Trader Client (FTC)</h1>
<h2>Storage Provider</h2>
<form method="post">
<input type="hidden" name="action" value="setSP">
{html_options name=sp options=$storageProviders selected=$spNumber}
<input type="submit" value="Switch">
</form>
<h2>Server Operations</h2>
<ul>
<li><a class="menu" id="getDirList" href="#">list files</a></li>
<li><a class="menu" id="pingServer" href="#">ping server</a></li>
<li><a class="menu" id="serverInfo" href="#">server info</a></li>
</ul>
<h2>Output</h2>
<div id="output"></div>
<h3>File Operations</h3>
<input type="text" id="dirName" />
<button id="createDirectory">Add Directory</button>

Files to upload: <input id="inputFiles" type="file">
</body>
</html>
