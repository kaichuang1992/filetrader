<?php
$config['time_zone'] = 'Europe/Amsterdam';

// Whether or not to allow only SSL connections
$config['ssl_only'] = FALSE;

// Location to directory where to store uploaded files, can be
// either absolute or relative location. Should be writable by
// web server!
$config['storage_dir'] = 'data';

// Static list of storage providers (format: key => secret)
$config['storage_providers'] = array(
		array('displayName' => 'Storage Provider One',
				'apiEndPoint' => 'http://127.0.0.2/storage',
				'consumerKey' => '12345', 'consumerSecret' => '54321'),
		array('displayName' => 'Storage Provider Two',
				'apiEndpoint' => 'http://127.0.0.1/storage',
				'consumerKey' => 'abcde', 'consumerSecret' => 'edcba'));
?>
