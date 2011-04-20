<style type="text/css">
        @import url("ext/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css");
</style>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="ext/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="ext/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<script type="text/javascript">
$(function() {
        $("#uploader").pluploadQueue({
                runtimes : 'html5,flash',
                url : 'index.php?action=handleUpload',
                max_file_size : '4096mb',
                chunk_size : '1mb',
                dragdrop : true,
                flash_swf_url : 'ext/plupload/js/plupload.flash.swf',
        });
});
</script>
<form id="uploader">
</form> <!-- /uploader -->
