<?php
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
