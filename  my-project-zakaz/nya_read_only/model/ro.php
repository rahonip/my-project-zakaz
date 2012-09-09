<?php
/**
 * Habr model class
 * 
 * @author hcs
 * @copyright (C) 2011 hcs habr extension for PunBB
 * @copyright Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package habr
 */
class Nya_Read_Only_Model_RO
{
    public function read_only($user, $time)
    {
        $query = array(
            'UPDATE'	=> 'users',
            'SET'		=> 'user_read_only='.$time,
            'WHERE'		=> 'id='.$user
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }
}