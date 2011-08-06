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

class StorageNodes {

    var $dbh;

    function __construct($config) {
        $this->config = $config;
        $this->dbh = new PDO(
                        "sqlite:" . getConfig($this->config, 'ftc_data_db', TRUE),
                        NULL, NULL, array(PDO::ATTR_PERSISTENT => TRUE));

        /* FIXME: move to SETUP procedure? */
        $this->dbh->query('CREATE TABLE IF NOT EXISTS storageNodes (id INTEGER PRIMARY KEY AUTOINCREMENT, displayName TEXT, apiUrl TEXT, consumerKey TEXT, consumerSecret TEXT, storageOwner TEXT)');
    }

    function __destruct() {
        $this->dbh = NULL;
    }

    function getStorageByOwner($owner) {
        if (!is_array($owner)) {
            $owner = array($owner);
        }

        /* ugliest code ever, to make the IN clause work,
          generate an array with "?" as placeholders for the prepare
          statement...this really makes me cry */
        $a = array_fill(0, sizeof($owner), '?');
        $in = implode(",", $a);

        $stmt = $this->dbh->prepare("SELECT id, displayName FROM storageNodes WHERE storageOwner IN ($in)");
        $stmt->execute($owner);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getStorageById($id) {
        $stmt = $this->dbh->prepare("SELECT displayName, apiUrl, consumerKey, consumerSecret FROM storageNodes WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function addStorage($displayName, $apiUrl, $consumerKey, $consumerSecret, $owner) {
        $stmt = $this->dbh->prepare("INSERT INTO storageNodes (displayName, apiUrl, consumerKey, consumerSecret, storageOwner) VALUES (:displayName, :apiUrl, :consumerKey, :consumerSecret, :owner)");
        $stmt->bindParam(':displayName', $displayName);
        $stmt->bindParam(':apiUrl', $apiUrl);
        $stmt->bindParam(':consumerKey', $consumerKey);
        $stmt->bindParam(':consumerSecret', $consumerSecret);
        $stmt->bindParam(':owner', $owner);
        $stmt->execute();
    }

}

?>
