<?php
/**
 * Habr uninstaller
 *
 * @author KANekT
 * @copyright (C) 2012 KANekT Habr effect extension for PunBB (C)
 * @package Habr
 */

defined('HABR_UNINSTALL') or die('Direct access not allowed');

$forum_db->drop_table('habr');

$forum_db->drop_field('users', 'habr_disable_adm');
$forum_db->drop_field('users', 'habr_minus');
$forum_db->drop_field('users', 'habr_plus');
$forum_db->drop_field('users', 'habr_enable');

$forum_db->drop_field('posts', 'habr_enable');
$forum_db->drop_field('posts', 'habr_immunity');
$forum_db->drop_field('posts', 'habr_minus');
$forum_db->drop_field('posts', 'habr_plus');

$forum_db->drop_field('topics', 'habr_minus');
$forum_db->drop_field('topics', 'habr_plus');
$forum_db->drop_field('topics', 'habr_immunity');

$forum_db->drop_field('forums', 'f_habr');

$forum_db->drop_field('groups', 'g_habr_min');
$forum_db->drop_field('groups', 'g_habr_enable');
$forum_db->drop_field('groups', 'g_habr_immunity');

$config_names  =  array('o_habr_count', 'o_habr_timeout', 'o_habr_forum', 'o_habr_maxmessage', 'o_habr_show_full');
$query = array(
    'DELETE'	=> 'config',
    'WHERE'		=> 'conf_name IN (\''.implode('\', \'', $config_names).'\')'
);
$forum_db->query_build($query) or error(__FILE__, __LINE__);