<?php
/**
 * Habr installer
 *
 * @author KANekT
 * @copyright (C) 2011 KANekT Habr effect extension for PunBB (C)
 * @copyright Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

defined('RO_INSTALL') or die('Direct access not allowed');

if (!defined('EXT_CUR_VERSION')){
    /*время ареста пользователя*/
    if (!$forum_db->field_exists('users', 'user_read_only'))
        $forum_db->add_field('users', 'user_read_only', 'INT(10)', true, '0');

    $ro_config = array(
        'o_user_read_only'		=> '5',
    );
    foreach ($ro_config as $key => $value) {
        if(!array_key_exists($key, $forum_config)) {
            $query_habr = array(
                'INSERT'	=> 'conf_name, conf_value',
                'INTO'		=> 'config',
                'VALUES'	=> '\''.$key.'\', \''.$forum_db->escape($value).'\''
            );
            $forum_db->query_build($query_habr) or error(__FILE__, __LINE__);
        }
    }
    unset($query_habr);
    require_once FORUM_ROOT.'include/cache.php';
    generate_config_cache();
}