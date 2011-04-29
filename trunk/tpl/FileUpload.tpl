<script type="text/javascript" src="j/upload.js"></script>
<table>
        <thead>
                <tr><th>File Name</th><th>File Size</th><th>Progress</th></tr>
        </thead>
        <tbody id="fileList">
                <tr><td colspan="3">No files selected yet...</td></tr>
        </tbody>
        <tfoot>
                <tr><td colspan="2">
                        <input id="inputFiles" type="file" onchange="listFiles(this.files)" multiple>
                        <button id="startButton" onclick="startUpload()" disabled>Start Upload</button>
                        <button id="abortButton" onclick="abortUpload()" disabled>Abort Upload</button>
                </td>
                <td><span id="uploadStatus"></span></td>
                </tr>
        </tfoot>
</table>
