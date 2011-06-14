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
<div id="progress"></div>
<h1>File Trader Client (FTC)</h1>
{if !empty($storageProviders)}
<form method="post">
<input type="hidden" name="action" value="setStorageProvider">
{html_options name=storageProvider options=$storageProviders selected=$activeStorageProvider}
<input type="submit" value="Use This Storage Provider">
</form>
{else}
No storage providers configured, please add some!
{/if}
<br><a id="toggleAddStorageProvider" href="#">Add Storage Provider</a>
<div id="addStorageProvider">
<form method="post">
<input type="hidden" name="action" value="addStorageProvider">
<table>
<tr><td>Display Name</td><td><input type="text" name="displayName"></td></tr>
<tr><td>API URL</td><td><input type="text" name="apiUrl"></td></tr>
<tr><td>Consumer Key</td><td><input type="text" name="consumerKey"></td></tr>
<tr><td>Consumer Secret</td><td><input type="text" name="consumerSecret"></td></tr>
<tr><td>Groups</td><td>
<small><strong>If you want to make this a group storage, select one of your groups. If you want it to be private don't select anything.</strong></small><br>
{html_radios name='group' options=$groups->getUserGroups() separator='<br />'}</td></tr>
<tr><td colspan="2"><input type="submit" value="Add Storage Provider"></td></tr>
</table>
</form>
</div>

<h2>Server Operations</h2>
<ul>
<li><a class="menu" id="getDirList" href="#">list files</a></li>
<li><a class="menu" id="pingServer" href="#">ping server</a></li>
<li><a class="menu" id="serverInfo" href="#">server info</a></li>
</ul>
<h2>File List</h2>
<div id="output"></div>
<h3>File Operations</h3>
<input type="text" id="dirName" />
<button id="createDirectory">Add Directory</button>
Files to upload: <input id="inputFiles" type="file">
</body>
</html>
