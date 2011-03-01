<?php

class NullGroups extends Groups {
	
        function getUserGroups() {
                        return array (
                                'A' => 'Alpha',
                                'B' => 'Bravo',
                                'C' => 'Charlie',
                                'D' => 'Delta',
                                'E' => 'Echo',

                        );
        }
}
?>
