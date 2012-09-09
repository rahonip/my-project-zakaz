<?php
/**
 * Habr model class
 *
 * @author KANekT
 * @copyright (C) 2012 KANekT Habr effect extension for PunBB (C)
 * @package Habr
 */
class Nya_Habr_Model_Habr
{
	
	public function get_user($user_id)
	{
		$query = array(
			'SELECT'	=> 'u.username, u.habr_plus AS count_habr_plus, u.habr_minus AS count_habr_minus',
			'FROM'		=> 'users AS u',
			'WHERE'		=> 'u.id='.$user_id
		);	
		
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);	
		
		return App::$forum_db->fetch_assoc($result);
	}
	
	public function get_by_id($id)
	{
		$query = array(
			'SELECT'	=> 'h.*, u.username',
			'FROM'		=> 'habr AS h',
			'JOINS'		=> array(
				array(
					'LEFT JOIN'	=> 'users AS u',
					'ON'			=> 'h.from_user_id = u.id'
				),
			),	
			'WHERE'		=> 'h.id='.$id
		);	
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

		return App::$forum_db->fetch_assoc($result);
	}
	
	
	function count_by_user_id($user_id) 
	{
		$query = array(
			'SELECT'	=> 'count(id)',
			'FROM'		=> 'habr',
			'WHERE'		=> 'user_id = '.$user_id
		);

		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

		list($count) = App::$forum_db->fetch_row($result);

		return $count;
	}	
	
	public function get_info($user_id, $group_id, $from, $to)
	{
		$query = array(
			'SELECT'	=> 'h.id, h.time, h.reason, h.post_id, h.habr_plus, h.habr_minus, h.user_id, t.subject, u.username as from_user_name, u.id as from_user_id, fp.read_forum',
			'FROM'		=> 'habr AS h',
			'JOINS'		=> array(
				array(
					'LEFT JOIN'		=> 'topics AS t',
					'ON'			=> 't.id=h.topic_id'
				),
				array(
					'LEFT JOIN'		=> 'users AS u',
					'ON'			=> 'h.from_user_id = u.id'
				),
				array(
					'LEFT JOIN'		=> 'forum_perms AS fp',
					'ON'			=> '(fp.forum_id=t.forum_id AND fp.group_id='.$group_id.')'
				)		
			),
			'WHERE'		=> 'h.user_id = '.$user_id,
			'ORDER BY'	=> 'h.time DESC',
			'LIMIT'		=> $from.','.$to		
		);	
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);	
	
		$records = array();
		while ($row = App::$forum_db->fetch_assoc($result))
		{
			$records[] = $row;
		}

		return $records;		
	}
	
	public function get_post_info($post_id, $user_id, $from_user_id)
	{
		$query = array(
			'SELECT'	=> 'p.id, p.topic_id, p.poster_id, p.habr_immunity as post_immunity, t.first_post_id, t.habr_immunity as topic_immunity, t.subject, f.f_habr as forum_immunity, u.habr_enable, u.username, h.post_id',
			'FROM'		=> 'posts AS p',
			'JOINS'		=> array(
				array(
					'INNER JOIN'	=> 'topics AS t',
					'ON'			=> 'p.topic_id=t.id'
				),
                array(
                    'INNER JOIN'	=> 'forums AS f',
                    'ON'			=> 'f.id=t.forum_id'
                ),
                array(
					'INNER JOIN'	=> 'users AS u',
					'ON'			=> 'p.poster_id = u.id'
				),
				array(
					'LEFT JOIN'		=> 'habr as h',
					'ON'			=> 'h.from_user_id ='.$from_user_id.' AND h.user_id = u.id AND h.post_id = '.$post_id
				)
			),
			'WHERE'		=>  'p.id = '.$post_id.' AND p.poster_id = '.$user_id,
			'ORDER BY'	=>	'h.time DESC',
			'LIMIT'	    =>  '0, 1',
		);	
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

		return App::$forum_db->fetch_assoc($result);
	}

    public function get_post_info_minus($post_id, $user_id, $time)
    {
        $time = mktime() - $time;
        $query = array(
            'SELECT'	=> 'h.habr_plus, h.habr_minus',
            'FROM'		=> 'habr as h',
            'WHERE'		=> 'h.post_id = '.$post_id.' AND h.user_id = '.$user_id.' AND h.time > '.$time,
            'ORDER BY'	=> 'h.time DESC'
        );
        $result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        $summa = 0;
        while ($row = App::$forum_db->fetch_assoc($result))
        {
            if (intval($row['habr_plus']) > 0)
                $summa++;
            if (intval($row['habr_minus']) > 0)
                $summa--;
        }

        return $summa;
    }

    public function post_hidden($post_id, $username)
    {
        $query = array(
            'UPDATE'	=> 'posts',
            'SET'		=> 'hidden='.mktime().', hidden_user=\''.$username.'\'',
            'WHERE'		=> 'id='.$post_id
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }

    public function add_ro_user($user_id, $time)
    {
        $time = mktime() - $time*60*60;
        $query = array(
            'SELECT'	=> 'h.habr_plus, h.habr_minus',
            'FROM'		=> 'habr as h',
            'WHERE'		=> 'h.user_id = '.$user_id.' AND h.time > '.$time,
            'ORDER BY'	=> 'h.time DESC'
        );
        $result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        $summa = 0;
        while ($row = App::$forum_db->fetch_assoc($result))
        {
            if (intval($row['habr_plus']) > 0)
                $summa++;
            if (intval($row['habr_minus']) > 0)
                $summa--;
        }

        return $summa;
    }

    public function user_ro($user_id, $time)
    {
        $time  = mktime() + $time*24*60*60;
        $query = array(
            'UPDATE'	=> 'users',
            'SET'		=> 'user_read_only='.$time,
            'WHERE'		=> 'id='.$user_id
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }

    public function get_topic_info_minus($topic_id, $user_id, $time)
    {
        $time = mktime() - $time;
        $query = array(
            'SELECT'	=> 'h.habr_plus, h.habr_minus',
            'FROM'		=> 'habr as h',
            'WHERE'		=> 'h.topic_id = '.$topic_id.' AND h.user_id = '.$user_id.' AND h.time > '.$time,
            'ORDER BY'	=> 'h.time DESC'
        );
        $result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        $summa = 0;
        while ($row = App::$forum_db->fetch_assoc($result))
        {
            if (intval($row['habr_plus']) > 0)
                $summa++;
            if (intval($row['habr_minus']) > 0)
                $summa--;
        }

        return $summa;
    }

    public function move_topic($tid, $fid)
    {
        $query = array(
            'UPDATE'	=> 'topics',
            'SET'		=> 'forum_id='.$fid,
            'WHERE'		=> 'id='.$tid
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }

    public function add_voice($target, $message, $from_user_id, $method)
	{
		$query = array(
			'INSERT'	=> 'user_id, from_user_id, time, post_id, reason, topic_id, habr_'. $method,
			'INTO'		=> 'habr',
			'VALUES'	=> '\''.$target['poster_id'].'\', '.$from_user_id.', '.mktime().', '.$target['id'].', \''.App::$forum_db->escape($message).'\', '.$target['topic_id'].', 1',
		);	
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);		

		$query = array(
			'UPDATE'	=> 'users',
			'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'+1',
			'WHERE'		=> 'id='.$target['poster_id']
		);
		App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        $query = array(
            'UPDATE'	=> 'posts',
            'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'+1',
            'WHERE'		=> 'id='.$target['id']
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        $query = array(
            'UPDATE'	=> 'topics',
            'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'+1',
            'WHERE'		=> 'id='.$target['topic_id']
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }

	public function delete($id_list)
	{
        $query = array(
            'SELECT'    => 'user_id, post_id, topic_id, habr_plus',
            'FROM'	    => 'habr',
            'WHERE'		=> 'id IN('.$id_list.')'
        );

        $result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

        while($cur_habr = App::$forum_db->fetch_assoc($result))
        {
            $method = 'minus';
            if ($cur_habr['habr_plus'] > 0)
                $method = 'plus';

            $query = array(
                'UPDATE'	=> 'users',
                'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'-1',
                'WHERE'		=> 'id = '.$cur_habr['user_id']
            );

            App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

            $query = array(
                'UPDATE'	=> 'posts',
                'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'-1',
                'WHERE'		=> 'id = '.$cur_habr['post_id']
            );

            App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

            $query = array(
                'UPDATE'	=> 'topics',
                'SET'		=> 'habr_'. $method.'='.'habr_'. $method.'-1',
                'WHERE'		=> 'id = '.$cur_habr['topic_id']
            );

            App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
        }

		$query = array(
			'DELETE'	=> 'habr',
			'WHERE'		=> 'id IN('.$id_list.')'
		);
	
		$result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }
}