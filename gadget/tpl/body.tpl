  <link rel="stylesheet" type="text/css" href=
  "{$css_url}">
  <script type="text/javascript" src=
  "{$js_url}">
  </script>

  <div id="header">
    <button id="upButton">Parent Directory</button> <button id=
    "uploadButton" onclick="createUploadWindow()">Upload
    Files</button> <button id="createDir" onclick=
    "createButtonWindow()">Create Directory</button>
  </div>

  <div id="ft_output"></div>

  <div id="status"></div>

  <div id="overlay">
    <p>Please enter the name of the directory you want to
    create:</p>

    <form method="post" action="javascript:void(0)">
      <input type="text" id="dirName" name="dirName" size="25">
      <button id="createDirButton" onclick=
      "handleAdd(this.form)">Create Directory</button>
    </form><a href="#" onclick="closeButtonWindow()">Close Window</a>
  </div>

  <div id="upload_overlay">
    <p>Please drag the files you want to upload to the drop area
    below or press the button to get the traditional file
    selector:</p>

    <div id="upload_area">
      Drag Files Here...
    </div><input type="file" id="fileElem" multiple="true">
    <button id="fileSelect">Select Files...</button> <a href="#"
    onclick="closeUploadWindow()">Close Window</a>
  </div>
