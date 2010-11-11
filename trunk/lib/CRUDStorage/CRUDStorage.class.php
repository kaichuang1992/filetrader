<?php

/*  
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2010 François Kooman <fkooman@tuxed.net>
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

abstract class CRUDStorage {
	function __construct() {
	}

	function createEntry($store, $fields) {
		if (!is_string($store) || !is_array($fields))
			throw new Exception('parameter error');
		return -1;
	}

	function readEntry($store, $id) {
		if (!is_string($store) || !is_string($id))
			throw new Exception('parameter error');
		return array ();
	}

	function updateEntry($store, $id, $fields) {
		if (!is_string($store) || !is_string($id) || !is_array($fields))
			throw new Exception('parameter error');
		return TRUE;
	}

	function deleteEntry($store, $id) {
		if (!is_string($store) || !is_string($id))
			throw new Exception('parameter error');
		return TRUE;
	}

	function searchEntry($store, $search) {
		return -1;
	}

	function listEntries($store) {
		return array ();
	}

	function __destruct() {
	}
}
?>