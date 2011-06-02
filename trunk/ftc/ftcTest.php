<?php
$storageProvider = array('displayName' => 'Storage Provider One',
                                'apiEndPoint' => 'http://192.168.56.101/fts',
                                'consumerKey' => '12345', 'consumerSecret' => '54321');

require_once("lib/StorageClient/StorageClient.class.php");

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
try {
	$sc = new StorageClient($storageProvider);

	$parameters =  array('userName' => 'demoUser');

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
      		$parameters = array_merge($parameters, $_POST);
	}	
	echo $sc->call($action, $parameters, $_SERVER['REQUEST_METHOD']);
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
<script type="text/javascript"  src="j/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("button").click(function(event) {
                var actionType = $(this).attr('id');

		//var parameters = Array();
	//	parameters['dirName'] = $("#dirName").value;

                $.post('?action=' + actionType, { dirName : $("#dirName").val() });
                event.preventDefault(); 
	});

        $("a.file").live('click', function(event) {
		var fileName = $(this).text();
		$.post('?action=getDownloadToken', { fileName : fileName }, function(data) {
			var resp = jQuery.parseJSON(data);
			window.location.href = resp.downloadLocation;
		});
		event.preventDefault();
        });

	$("a.menu").live('click', function(event) {
		var actionType = $(this).attr('id');

		$.getJSON('?action=' + actionType, function(data) {
			  var items = [];

			  if(actionType === 'getFileList') {
			  $.each(data.files, function(key, val) {
			    if(val.isDirectory) {
				items.push('<tr><th>' + key + '</th><td>[DIR]</td></tr>>');
				}else {
                                items.push('<tr><th><a href="#" class="file">' + key + '</a></th><td>' + val.fileSize + '</td></tr>>');
				}
			  });
			}else {
                          $.each(data, function(key, val) {
                            items.push('<tr><th>' + key + '</th><td>' + val + '</td></tr>>');
                          });
			}
			$("#output").html(	 $('<table/>', {
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
<h2>Storage Engine</h2>
<pre><?php var_dump($storageProvider); ?></pre>
<ul>
<li><a class="menu" id="pingServer" href="#">ping server</a></li>
<li><a class="menu" id="getFileList" href="#">list files</a></li>
<li><a class="menu" id="serverInfo" href="#">server info</a></li>
</ul>

<input type="text" id="dirName" />
<button id="createDirectory">Add Directory</button>

<hr>

<div id="output">
This content will be replaced with actual output...
</div>
</body>
</html>
