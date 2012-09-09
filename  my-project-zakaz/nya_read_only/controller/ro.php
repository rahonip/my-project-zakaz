<?php
/**
 * Habr controller class
 * 
 * @author hcs
 * @copyright (C) 2011 hcs habr extension for PunBB
 * @copyright Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package habr
 */
class Nya_Read_Only_Controller_RO extends Controller
{
	protected $habr;
	
	public function __construct($ext_path)
	{
		parent::__construct($ext_path);
		App::load_language('nya_read_only');
		$this->set_filter(array('uid' => 'int', 'token' => 'string'));
		$this->ro = new Nya_Read_Only_Model_RO;
		$this->page = 'read_only';
	}

    public function add()
    {
        $this->pre_process();
        $errors = array();

        if (generate_form_token('ro'.$this->uid) == $this->token)
        {
            $this->set_read_only($errors, $this->uid);
            App::$forum_flash->add_info(App::$lang['RO Redirect Message Add']);
            redirect(forum_link(App::$forum_url['profile_admin'], $this->uid), App::$lang['RO Redirect Message Add']);
        }
        else
        {
            message(App::$lang_common['Bad request']);
        }
    }

    public function del()
    {
        $this->pre_process();
        $errors = array();

        if (generate_form_token('ro'.$this->uid) == $this->token)
        {
            $this->unset_read_only($errors, $this->uid);
            App::$forum_flash->add_info(App::$lang['RO Redirect Message Del']);
            redirect(forum_link(App::$forum_url['profile_admin'], $this->uid), App::$lang['RO Redirect Message Del']);
        }
        else
        {
            message(App::$lang_common['Bad request']);
        }
    }

    private function set_read_only(& $errors, $uid)
    {
        if (empty($errors))
        {
            $this->ro->read_only($uid, mktime() + App::$forum_config['o_user_read_only']*24*60*60);
            return TRUE;
        }
        return FALSE;
    }

    private function unset_read_only(& $errors, $uid)
    {
        if (empty($errors))
        {
            $this->ro->read_only($uid, 0);
            return TRUE;
        }
        return FALSE;
    }

    private function pre_process()
    {
        if (!isset($this->token) OR !isset($this->uid))
            message(App::$lang_common['Bad request']);

        if (App::$forum_user['g_id'] != FORUM_ADMIN OR App::$forum_user['id'] == $this->uid)
            message(App::$lang_common['No permission']);
    }
}