<?php

/*
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2011 François Kooman <fkooman@tuxed.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class StorageProvider {

	var $dbh;

	function __construct($config) {
		$this->config = $config;
		$this->dbh = new PDO(
				"sqlite:" . getConfig($this->config, 'ftc_db', TRUE),
				NULL, NULL, array(PDO::ATTR_PERSISTENT => TRUE));

		/* FIXME: maybe this should be placed somewhere else, inefficient?!... */
		$this->dbh->query('CREATE TABLE IF NOT EXISTS storageProviders (id INTEGER PRIMARY KEY AUTOINCREMENT, displayName TEXT, apiUrl TEXT, consumerKey TEXT, consumerSecret TEXT, storageOwner TEXT)');

		/* future */
		/*
                $this->dbh->query('CREATE TABLE IF NOT EXISTS Providers (id INTEGER PRIMARY KEY AUTOINCREMENT, displayName TEXT, apiUrl TEXT, consumerKey TEXT, consumerSecret TEXT, userId TINYTEXT)');
		$this->dbh->query('CREATE TABLE IF NOT EXISTS Shares (providerId INTEGER FOREIGN KEY ???, groupId TINYTEXT, filePath TINYTEXT)');
		*/
	}

	function __destruct() {
		$this->dbh = NULL;
	}

	function getUserStorage($ownerId) {
		/* FIXME: validate ownerId? */
		$stmt = $this->dbh->prepare("SELECT id, displayName, apiUrl, consumerKey, consumerSecret FROM storageProviders WHERE storageOwner=:ownerId");
		$stmt->bindParam(':ownerId', $ownerId);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function getUserStorageById($id) {
		/* FIXME: validate ownerId? */
//		$stmt = $this->dbh->prepare("SELECT id, displayName, apiUrl, consumerKey, consumerSecret FROM storageProviders WHERE id=:id AND storageOwner=:ownerId");
		$stmt = $this->dbh->prepare("SELECT id, displayName, apiUrl, consumerKey, consumerSecret FROM storageProviders WHERE id=:id");
		$stmt->bindParam(':id', $id);
		// $stmt->bindParam(':ownerId', $ownerId);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function addUserStorage($displayName, $apiUrl, $consumerKey, $consumerSecret, $ownerId) {
		$stmt = $this->dbh->prepare("INSERT INTO storageProviders (displayName, apiUrl, consumerKey, consumerSecret, storageOwner) VALUES (:displayName, :apiUrl, :consumerKey, :consumerSecret, :ownerId)");
		$stmt->bindParam(':displayName', $displayName);
		$stmt->bindParam(':apiUrl', $apiUrl);
		$stmt->bindParam(':consumerKey', $consumerKey);
		$stmt->bindParam(':consumerSecret', $consumerSecret);
		$stmt->bindParam(':ownerId', $ownerId);
		$stmt->execute();
	}
}

