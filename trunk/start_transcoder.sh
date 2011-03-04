#!/bin/sh
/usr/bin/php transcode.cron.php >/dev/null 2>/dev/null &
/usr/bin/php transcode_progress.cron.php >/dev/null 2>/dev/null &
