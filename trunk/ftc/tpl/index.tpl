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
{if !empty($storageProviders)}
<form method="post">
<input type="hidden" name="action" value="setStorageProvider">
{html_options name=storageProvider options=$storageProviders selected=$activeStorageProvider}
<input type="submit" value="Switch">
</form>
{else}
No storage providers configured, please add some!
{/if}
<br><a id="toggleAddStorageProvider" href="#">Add Storage Provider</a>
<div id="addStorageProvider">
<form method="post">
<input type="hidden" name="action" value="addStorageProvider">
<label>Display Name<input type="text" name="displayName"></label><br>
<label>API URL<input type="text" name="apiUrl"></label><br>
<label>Consumer Key<input type="text" name="consumerKey"></label><br>
<label>Consumer Secret<input type="text" name="consumerSecret"></label><br>
<input type="submit" value="Add Storage Provider">
</form>
</div>

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
