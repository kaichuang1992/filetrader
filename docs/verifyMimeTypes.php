<?php 
/**
 */

/*
 * In order to create a small WebM sample video one can take a PNG file
 * and encode it with ffmpeg:
 *
 * ffmpeg -r 24 -i big_buck_bunny_00100.png output.webm
 *
 * This will create a video containing one frame only, a tiny 
 * video for testing whether the system recognized the file type...
 */

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
