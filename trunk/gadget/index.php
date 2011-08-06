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

$view = getRequest('fmt', FALSE, 'html');

if(!in_array($view, array('html','os'))) {
	die ("invalid view");
}

require_once ("Smarty.class.php");

$smarty = new Smarty();
$smarty->template_dir = 'tpl';
$smarty->compile_dir = 'tpl_c';
$smarty->assign('css_url', getProtocol() . getServerName() . dirname($_SERVER['PHP_SELF']) . '/s/style.css');
$smarty->assign('js_url', getProtocol() . getServerName() . dirname($_SERVER['PHP_SELF']) . '/j/' . $view . '.js');
$smarty->assign('js_common_url', getProtocol() . getServerName() . dirname($_SERVER['PHP_SELF']) . '/j/common.js');
$smarty->assign('protocol', getProtocol());

$content = $smarty->fetch('content.tpl');
$smarty->assign('content', $content);

if($view === "os") {
	/* Disable Caching */
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Content-Type: text/xml");
}
$smarty->display($view . '.tpl');

?>
