<?php
	require_once('config.php');
	require_once('utils.php');

	$commands = array('list','add','del');

	if($argc < 2) {
		echo "specify a command [" . implode(", ", $commands) . "]\n";
		exit(1);
	}

	if(!in_array($argv[1], $commands)) {
		echo "invalid command specified [" . implode(", ", $commands) . "]\n";
		exit(1);
	}

        $dbh = new PDO(getConfig($config, 'fts_db_dsn', TRUE),
                            getConfig($config, 'fts_db_user', FALSE, NULL),
                            getConfig($config, 'fts_db_pass', FALSE, NULL),
                            getConfig($config, 'fts_db_options', FALSE, array()));

            /* FIXME: move to SETUP procedure? */
        $dbh->query('CREATE TABLE IF NOT EXISTS storageConsumers (consumerKey TEXT PRIMARY KEY, consumerSecret TEXT)');

	switch($argv[1]) {
	case "list":
		$stmt = $dbh->prepare('SELECT consumerKey, consumerSecret FROM storageConsumers');
		$stmt->execute();
        	$sc = $stmt->fetchAll(PDO::FETCH_ASSOC);

		echo "*** LIST OF STORAGE CONSUMERS ***\n\n";
		foreach($sc as $i) {
			echo "[KEY]    = " . $i['consumerKey'] . "\n[SECRET] = " . $i['consumerSecret'] . "\n[PATH]   = " . getConfig($config, 'fts_data', TRUE) . DIRECTORY_SEPARATOR . urlencode($i['consumerKey']) . "\n\n";
		}
		break;

	case "add":
		if($argc != 3) {
			echo "specify a (unique) consumerKey value, for example the name of the consumer\n";
			exit(1);
		}
                $stmt = $dbh->prepare('INSERT INTO storageConsumers (consumerKey, consumerSecret) VALUES(:key, :secret)');
		$stmt->bindValue(':key', $argv[2]);
		$secret = generateToken();
		$stmt->bindValue(':secret', $secret);
		$stmt->execute();
		echo "consumerKey: [" . $argv[2] . "], consumerSecret: [" . $secret . "]\n";
		break;

	case "del":
                if($argc != 3) {
                        echo "specify a the consumerKey value which you want to delete (see list)\n";
                        exit(1);
                }

                $stmt = $dbh->prepare('DELETE FROM storageConsumers WHERE consumerKey = :key');
                $stmt->bindValue(':key', $argv[2]);
                $stmt->execute();
                echo "storage consumer [" . $argv[2] . "] deleted\n";
                break;
	}
?> 
