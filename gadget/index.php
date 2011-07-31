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

require_once ('config.php');
require_once ('utils.php');

if (!isset($config) || !is_array($config)) {
    die("broken or missing configuration file?");
}

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($config, "smarty_lib_dir", TRUE));

$view = getRequest('view', FALSE, 'web');

require_once ("Smarty.class.php");

$smarty = new Smarty();
$smarty->template_dir = 'tpl';
$smarty->compile_dir = 'tpl_c';
$smarty->assign('css_url', getProtocol() . getServerName() . dirname($_SERVER['PHP_SELF']) . '/s/style.css');
$smarty->assign('js_url', getProtocol() . getServerName() . dirname($_SERVER['PHP_SELF']) . '/j/opensocial.js');
$content = $smarty->fetch('body.tpl');
$smarty->assign('content', $content);

switch($view) {
case "opensocial":
	$smarty->display('opensocial.tpl');
	break;
case "web":
	$smarty->display('web.tpl');
	break;
default:
	die("invalid view");
}

?>
