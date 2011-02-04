<?php
	echo "--- Verifying Mime Type of WebM sample file...\n";

	$finfo = new finfo(FILEINFO_MIME_TYPE, "/usr/share/misc/magic.mgc");
	$mT = $finfo->file("BigBuckBunnyFrame.webm");
	if($mT === "video/webm") {
		echo "*** Mime Type is '$mT' as expected, you are all set! :-)\n";

	} else {
		echo "*** Mime Type was '$mT' and not 'video/webm' as expected! :-(\n\n";	
		echo "    FIX:\n";
		echo "    Update the 'file' package on your OS, see http://www.darwinsys.com/file/\n";
	}
?>
