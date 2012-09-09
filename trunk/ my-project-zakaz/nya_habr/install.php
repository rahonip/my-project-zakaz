<?php
/**
 * Habr installer
 *
 * @author KANekT
 * @copyright (C) 2012 KANekT Habr effect extension for PunBB (C)
 * @package Habr
 */

defined('HABR_INSTALL') or die('Direct access not allowed');

if (!defined('EXT_CUR_VERSION')){
    if (!$forum_db->table_exists('habr')) {
        $schema = array(
            'FIELDS' => array(
                'id'		=> array(
                    'datatype'		=> 'SERIAL',
                    'allow_null'	=> false
                ),
                'user_id'	=> array(
                    'datatype'		=> 'INT(10) UNSIGNED',
                    'allow_null'	=> false,
                    'default'		=> '0'
                ),
                'from_user_id'	=> array(
                    'datatype'		=> 'INT(10) UNSIGNED',
                    'allow_null'	=> false,
                    'default'		=> '0'
                ),
                'time'		=> array(
                    'datatype'		=> 'INT(10) UNSIGNED',
                    'allow_null'	=> false
                ),
                'post_id'	=> array(
                    'datatype'		=> 'INT(10) UNSIGNED',
                    'allow_null'	=> false
                ),
                'topic_id'		=> array(
                    'datatype'		=> 'INT(10) UNSIGNED',
                    'allow_null'	=> false
                ),
                'reason'		=> array(
                    'datatype'		=> 'TEXT',
                    'allow_null'	=> false
                ),
                'habr_plus'			=> array(
                    'datatype'		=> 'TINYINT(1)',
                    'allow_null'	=> false,
                    'default'		=> '0'
                ),
                'habr_minus'			=> array(
                    'datatype'		=> 'TINYINT(1)',
                    'allow_null'	=> false,
                    'default'		=> '0'
                )
            ),
            'PRIMARY KEY'	=> array('id'),
            'INDEXES'		=> array(
                'habr_post_id_idx'	=> array('post_id'),
                'hadr_time_idx'	=> array('time')
            )
        );
        $forum_db->create_table('habr', $schema);
    }

    /*Может ли пользователь голосовать*/
    if (!$forum_db->field_exists('users', 'habr_disable_adm'))
        $forum_db->add_field('users', 'habr_disable_adm', 'TINYINT(1)', true, '0');
    /*Отключил ли пользователь сам голосование*/
    if (!$forum_db->field_exists('users', 'habr_enable'))
        $forum_db->add_field('users', 'habr_enable', 'TINYINT(1)', true, '1');
    /*Кол-во сообщений в плюсе у пользователя*/
    if (!$forum_db->field_exists('users', 'habr_plus'))
        $forum_db->add_field('users', 'habr_plus', 'INT(10)', true, '0');
    /*Кол-во сообщений в минусе у пользователя*/
    if (!$forum_db->field_exists('users', 'habr_minus'))
        $forum_db->add_field('users', 'habr_minus', 'INT(10)', true, '0');

    /*Показывается ли сообщение*/
    if (!$forum_db->field_exists('posts', 'habr_enable'))
        $forum_db->add_field('posts', 'habr_enable', 'TINYINT(1)', true, '1');
    /*Назначен ли иммунитет*/
    if (!$forum_db->field_exists('posts', 'habr_immunity'))
        $forum_db->add_field('posts', 'habr_immunity', 'TINYINT(1)', true, '0');
    /*+ сообщение*/
    if (!$forum_db->field_exists('posts', 'habr_plus'))
        $forum_db->add_field('posts', 'habr_plus', 'INT(10)', true, '0');
    /*- сообщение*/
    if (!$forum_db->field_exists('posts', 'habr_minus'))
        $forum_db->add_field('posts', 'habr_minus', 'INT(10)', true, '0');

    /*Назначен ли иммунитет*/
    if (!$forum_db->field_exists('topics', 'habr_immunity'))
        $forum_db->add_field('topics', 'habr_immunity', 'TINYINT(1)', true, '0');
    /*+ сообщение*/
    if (!$forum_db->field_exists('topics', 'habr_plus'))
        $forum_db->add_field('topics', 'habr_plus', 'INT(10)', true, '0');
    /*- сообщение*/
    if (!$forum_db->field_exists('topics', 'habr_minus'))
        $forum_db->add_field('topics', 'habr_minus', 'INT(10)', true, '0');

    /*Включено для форума*/
    if (!$forum_db->field_exists('forums', 'f_habr'))
        $forum_db->add_field('forums', 'f_habr', 'TINYINT(1)', true, '1');

    /*Могут ли голосовать*/
    if (!$forum_db->field_exists('groups', 'g_habr_enable'))
        $forum_db->add_field('groups', 'g_habr_enable', 'TINYINT(1)', true, '1');
    /*Кол-во сообщений для возможности голосования*/
    if (!$forum_db->field_exists('groups', 'g_habr_min'))
        $forum_db->add_field('groups', 'g_habr_min', 'INT(10)', true, '0');
    /*Назначен ли имунитет для группы*/
    if (!$forum_db->field_exists('groups', 'g_habr_immunity'))
        $forum_db->add_field('groups', 'g_habr_immunity', 'TINYINT(1)', true, '0');

    $habr_config = array(
        'o_habr_count'				=> '0',
        'o_habr_timeout'			=> '300',
        'o_habr_maxmessage'         => '200',
        'o_habr_read_only_count'	=> '10',
        'o_habr_read_only_time'	    => '10',
        'o_habr_forum'				=> '1',
        'o_habr_show_full'			=> '1'
    );
    foreach ($habr_config as $key => $value) {
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