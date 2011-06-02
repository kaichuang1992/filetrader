<?php
$storageProvider = array('displayName' => 'Storage Provider One',
                                'apiEndPoint' => 'http://192.168.56.101/fts',
                                'consumerKey' => '12345', 'consumerSecret' => '54321');

require_once("lib/StorageClient/StorageClient.class.php");

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
try {
	$sc = new StorageClient($storageProvider);
	echo $sc->call($action, array(), "GET");
	exit(0);
}catch(Exception $e) {
	echo $e->getMessage();
	exit(1);	
}
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
<script src="j/jquery.js"></script>
<script>
$(document).ready(function(){
	$("a").click(function(event) {
		var actionType = $(this).attr('id');
		$.getJSON('?action=' + actionType, function(data) {
			  var items = [];

			  $.each(data, function(key, val) {
			    items.push('<tr><th>' + key + '</th><td>' + val + '</td></tr>>');
			  });

			$("#output").html(                          $('<table/>', {
                            'class': 'my-new-list',
                            html: items.join('')
                          }));

			});
                event.preventDefault();	
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
