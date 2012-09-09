<?php
/**
 * Habr uninstaller
 *
 * @author KANekT
 * @copyright (C) 2011 KANekT Habr effect extension for PunBB (C)
 * @copyright Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

defined('RO_UNINSTALL') or die('Direct access not allowed');

$forum_db->drop_field('users', 'user_read_only');

$config_names  =  array('o_user_read_only');
$query = array(
    'DELETE'	=> 'config',
    'WHERE'		=> 'conf_name IN (\''.implode('\', \'', $config_names).'\')'
);
$forum_db->query_build($query) or error(__FILE__, __LINE__);