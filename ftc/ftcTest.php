<?php
$storageProvider = array('displayName' => 'Storage Provider One',
		'apiEndPoint' => 'http://192.168.56.101/fts', 'consumerKey' => '12345',
		'consumerSecret' => '54321');

require_once("lib/StorageClient/StorageClient.class.php");

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	try {
		$sc = new StorageClient($storageProvider);

		$parameters = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$parameters = array_merge($parameters, $_POST);
		}

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$parameters = array_merge($parameters, $_GET);
		}

		echo $sc->call($action, $parameters, $_SERVER['REQUEST_METHOD']);
		exit(0);
	} catch (Exception $e) {
		echo $e->getMessage();
		exit(1);
	}
}
// no action specified show default page

?>
<!DOCTYPE html>
<head>
<meta charset="utf8">
<title>ftc</title>
<style type="text/css">
ul {
	margin: 0;
	padding: 0;
}

li { 
	list-style: none;
	display: inline;
	margin: 0;
	padding: 5px;
	border: 1px solid #000;
	background-color: #ccc;
}

li a {
	text-decoration: none;
	font-weight: bold;
	color: #000;
}
td.header {
	background-color: #ccc;
}
</style>
<script type="text/javascript" src="j/jquery.js"></script>
<script type="text/javascript" src="j/ftc.js"></script>
</head>
<body>
<h2>File Trader Client (FTC)</h2>
<ul>
<li><a class="menu" id="getDirList" href="#">list files</a></li>
<li><a class="menu" id="pingServer" href="#">ping server</a></li>
<li><a class="menu" id="serverInfo" href="#">server info</a></li>
</ul>
<hr>
<div id="output"></div>
<hr>
<input type="text" id="dirName" />
<button id="createDirectory">Add Directory</button>

Files to upload: <input id="inputFiles" type="file">
</body>
</html>
