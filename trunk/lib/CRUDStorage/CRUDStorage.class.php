<?php
	abstract class CRUDStorage {
		function __construct() {
		}

		function createEntry($store, $fields) {
			if(!is_string($store) || !is_array($fields))
				throw new Exception('parameter error');
			return -1;
		}

		function readEntry($store, $id) {
			if(!is_string($store) || !is_string($id))
                                throw new Exception('parameter error');
			return array();
		}

		function updateEntry($store, $id, $fields) {
			if(!is_string($store) || !is_string($id) || !is_array($fields))
                                throw new Exception('parameter error');
			return TRUE;
		}

		function deleteEntry($store, $id) {
                        if(!is_string($store) || !is_string($id))
                                throw new Exception('parameter error');
			return TRUE;
		}

		function searchEntry($store, $search) {
			return -1;
		}

                function listEntries($store) {
			return array();
		}

		function __destruct() {
		}
	}
?>
