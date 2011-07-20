<?php
	require_once('config.php');
	require_once('utils.php');

	$commands = array('list','add','del');

	if($argc < 2) {
		showHelp();
		exit(1);
	}

	if(!in_array($argv[1], $commands)) {
		echo "Invalid command!\n";
		showHelp();
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
		if($argc < 3) {
			echo "specify a (unique) consumerKey value, for example the name of the consumer\n";
			exit(1);
		}
                $stmt = $dbh->prepare('INSERT INTO storageConsumers (consumerKey, consumerSecret) VALUES(:key, :secret)');
		$stmt->bindValue(':key', $argv[2]);
		if($argc > 3) {
			/* secret specified */
			if(file_exists($argv[3]) && is_file($argv[3])) {
				/* certificate instead of secret specified */
				$secret = file_get_contents($argv[3]);
			} else {
	                	$secret = $argv[3];
			}
		} else {
			/* generate us a new secret */
			$secret = generateToken();
		}
		$stmt->bindValue(':secret', $secret);
		$stmt->execute();
		echo "ADDED consumer '" . $argv[2] . "'\n";
		break;

	case "del":
                if($argc != 3) {
                        echo "specify a the consumerKey value which you want to delete (see list)\n";
                        exit(1);
                }

                $stmt = $dbh->prepare('DELETE FROM storageConsumers WHERE consumerKey = :key');
                $stmt->bindValue(':key', $argv[2]);
                $stmt->execute();
                echo "DELETED consumer '" . $argv[2] . "'\n";
                break;
	}

	function showHelp() {
		echo "Manage the OAuth consumers\n\n";
		echo "Add with specified secret        add <consumer_key> <consumer_secret>\n";
		echo "Add with specified certificate   add <consumer_key> <consumer_certificate>\n";
		echo "Add with generated secret        add <consumer_key>\n";
		echo "\n";
		echo "Delete consumer                  del <consumer_key>\n";
		echo "\n";
		echo "List consumers and secrets       list\n";
		echo "\n";
		echo "Examples:\n";
		echo "\n";
		echo "add www.google.com igoogle.pem\n";
		echo "\twhere 'igoogle.pem' is the file containing the iGoogle certificate\n";
		echo "add test_key test_secret\n";
		echo "add another_test_key\n";
		echo "\n";	
	}
?> 
