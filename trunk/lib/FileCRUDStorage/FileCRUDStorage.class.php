<?php
	class FileCRUDStorage extends CRUDStorage {
		var $data;
		var $file;

		function __construct($storage_file) {
			$this->file = $storage_file;

			if(file_exists($this->file) || is_readable($this->file))
				$this->data = include($this->file);
			if(!is_array($this->data))
				$this->data = array();
		}

		function createEntry($store, $fields) {
			$id = bin2hex(openssl_random_pseudo_bytes(10));
			$this->data[$store][$id] = $fields;
			return $id;
		}

		function readEntry($store, $id) {
			if(!array_key_exists($id, $this->data[$store]))
				return FALSE;
			return array_merge($this->data[$store][$id], array('id' => $id));
		}

		function updateEntry($store, $id, $fields) {
			$this->data[$store][$id] = $fields;
			return true;
		}

		function deleteEntry($store, $id) {
			unset($this->data[$store][$id]);
			return true;
		}

		/* FIXME, look in all columns! */
		function searchEntry($store, $search) {
			$results = array();
			foreach($this->data[$store] as $k => $v) {
				if(stristr($v['title'], $search) !== FALSE) {
					array_push($results, $k);
					/* if title already matches, don't look at 
					   description anymore */
					continue;
				}
				if(stristr($v['description'], $search) !== FALSE)
					array_push($results, $k);
			}
			return $results;
		}

		function listEntries($store) { 
			if(!array_key_exists($store, $this->data))
				return array();
			return $this->data[$store];
		}	

		function save() {
			/* write structure to file */
			$content = '<?php $data=';
			$content .= var_export($this->data, TRUE);
			$content .= '; return $data; ?>';
			file_put_contents($this->file, $content);
		}

		function __destruct() {
			$this->save();
		}
	}
?>
