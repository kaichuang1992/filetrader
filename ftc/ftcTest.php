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
</style>
<script type="text/javascript" src="j/jquery.js"></script>
<script type="text/javascript" src="j/ftc.js"></script>
</head>
<body>
<h2>Storage Engine</h2>
<pre><?php var_dump($storageProvider); ?></pre>
<ul>
<li><a class="menu" id="pingServer" href="#">ping server</a></li>
<li><a class="menu" id="getFileList" href="#">list files</a></li>
<li><a class="menu" id="serverInfo" href="#">server info</a></li>
</ul>

<input type="text" id="dirName" />
<button id="createDirectory">Add Directory</button>

Files to upload: <input id="inputFiles" type="file">

<hr>

<div id="output">
This content will be replaced with actual output...
</div>
</body>
</html>
