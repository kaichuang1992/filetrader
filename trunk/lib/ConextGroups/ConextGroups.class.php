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

require_once ('ext/opensocial-php-client/osapi/osapi.php');
require_once ('lib/ConextGroups/osapiConextProvider.php');

class ConextGroups extends Groups {
	var $osapi; 

        function __construct($config, $auth = NULL) {
		parent::__construct($config, $auth);

		osapiLogger::setLevel(osapiLogger::INFO);
		osapiLogger::setAppender(new osapiFileAppender("data/osapi.log"));

		$provider = new osapiConextProvider();
		$auth = new osapiOAuth2Legged(getConfig($config, 'conext_key', TRUE), getConfig($config, 'conext_secret', TRUE), $this->auth->getUserId());
		$this->osapi = new osapi($provider, $auth);
	}

	function getUserGroups() {
		$params = array('userId' => $this->auth->getUserId());
		$batch = $this->osapi->newBatch();
		// $batch->add($this->osapi->people->get($params), 'getPeople');
		$batch->add($this->osapi->groups->get($params), 'getGroups');
		$result = $batch->execute();

		$groups = array();
		foreach($result['getGroups']['result']['list'] as $group) {
			$groupId = $group['id']['groupId'];
			$groupTitle = $group['title'];
			$groups[$groupId] = $groupTitle;
		}
		return $groups;
	}
}
?>
