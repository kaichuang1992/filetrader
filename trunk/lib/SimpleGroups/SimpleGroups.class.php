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

class SimpleGroups extends Groups {
	
        function getUserGroups() {
		$groups = getConfig($this->config, 'simple_groups', TRUE);
		$userId = $this->auth->getUserId();

		$memberGroups = array();

		foreach($groups as $groupId => $groupInfo) {
			if(in_array($userId, $groupInfo['members']))
				$memberGroups[$groupId] = $groupInfo['display_name'];
		}
		return $memberGroups;
        }
}
?>
