<?php
$storageProvider = array('displayName' => 'Storage Provider One',
		'apiEndPoint' => 'http://127.0.0.2/storage', 'consumerKey' => '12345',
		'consumerSecret' => '54321');

require_once("lib/StorageClient/StorageClient.class.php");

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];

	$sc = new StorageClient($storageProvider);

} else {
	// no action specified show default page
}

?>
<!DOCTYPE html>
<head>
<meta charset="utf8">
<title>ftc</title>
<style type="text/css">
</style>
<script>
	("#pingServer").onclick(function() {

		$.getJSON('?action=pingServer', function(data) {
			  var items = [];

			  $.each(data, function(key, val) {
			    items.push('<li id="' + key + '">' + val + '</li>');
			  });

			  $('<ul/>', {
			    'class': 'my-new-list',
			    html: items.join('')
			  }).appendTo('output');
			});		
	});
</script>
</head>
<body>
<ul>
<li><a id="pingServer" href="#">ping server</a></li>
<li><a id="listFiles" href="#">list files</a></li>
<li><a id="serverInfo" href="#">server info</a></li>
</ul>
<div id="output">
This content will be replaced with actual output...
</div>
</body>
</html>