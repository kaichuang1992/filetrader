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

	class CouchCRUDStorage extends CRUDStorage {
		var $sag;

		function __construct() {
			$this->sag = new Sag();
			$this->sag->decode(FALSE);
		}

		function createEntry($store, $fields) {
			$this->sag->setDatabase($store);
			return $this->sag->post($fields)->body->id;
		}

		function readEntry($store, $id) {
			$this->sag->setDatabase($store);
			return json_decode($this->sag->get($id)->body, TRUE);
		}

		function updateEntry($store, $id, $fields) {
			$this->sag->setDatabase($store);
			$this->sag->put($id, $fields);
			return true;
		}

		function deleteEntry($store, $id, $rev = null) {
                        $this->sag->setDatabase($store);
			$this->sag->delete($id, $rev);
			return true;
		}

		function searchEntry($store, $search) {
			return NULL;
		}

		function listEntries($store) { 
			$this->sag->setDatabase($store);
			$data = json_decode($this->sag->getAllDocs(TRUE)->body, TRUE);
			$rows = $data['rows'];
			
			$docs = array();
			foreach($rows as $k => $v) {
				$id = $v['doc']['_id'];
				$docs[$id] = $v['doc'];
			}
			return $docs;
		}	
	}
?>
