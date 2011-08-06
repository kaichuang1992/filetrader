<html>
<head>
<title>FileTrader</title>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>

  <script type="text/javascript" src="j/jq.js"></script>
  <script id="directoryEntry" type="text/x-jquery-tmpl">
        <tr class="${Parity}">
          <td><a id="file_${FileId}" href="#"><img src="i/download_bl.png" height="20" width="20"></a></td>
          <td class="${Type}">${Name}</td>
          <td>${FileSize}</td>
          <td>${FileTime}</td>
          <td><a id="delete_${FileId}" href="#"><img src="i/bin_bl.png" height="20" width="20"></a></td>
        </tr>
  </script>
</head>
<body>
  <div id="header">
    <span class="breadcrumb">
	<a href="#">Home</a>
    </span>
    <span class="icons">
	<a href="#">
		<img src="i/add_bl.png" height="32" width="32">
	</a>
	<a href="#">
		<img src="i/setting_bl.png" height="32" width="32">
	</a>
    </span>
  </div>

  <div id="content">
    <table class="filelist">
      <tbody>
        <tr>
          <th>Action</th>

          <th>Name</th>

          <th>Size</th>

          <th>Created</th>

          <th>Delete</th>
        </tr>

      </tbody>
    </table>
  </div>

</body>
</html>

