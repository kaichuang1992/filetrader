<?php
require_once('config.php');
require_once('../fts/utils.php');
require_once("lib/StorageClient/StorageClient.class.php");
require_once("lib/Auth.class.php");
require_once("lib/SimpleAuth/SimpleAuth.class.php");

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

date_default_timezone_set(
		getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

$auth = new SimpleAuth();
$auth->login();

$storageProviders = getConfig($config, 'storage_providers', TRUE);

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	$spNumber = session_is_registered('storageProvider') ? $_SESSION['storageProvider']
			: 0;
	if ($action == "setSP") {
		$_SESSION['storageProvider'] = $_REQUEST['sp'];
		$spNumber = $_SESSION['storageProvider'];
	}
	try {
		$sc = new StorageClient($storageProviders[$spNumber]);

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
<h1>File Trader Client (FTC)</h1>
<h2>Storage Provider</h2>
<form method="post">
<input type="hidden" name="action" value="setSP">
<select name="sp">
<?php foreach ($storageProvider as $k => $v) { ?>
<option value="<?php echo $k; ?>" <?php if ($k == $spNumber) {
		echo "selected";
	} ?>><?php echo $v['displayName']; ?></option>
<?php } ?>
</select>
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
