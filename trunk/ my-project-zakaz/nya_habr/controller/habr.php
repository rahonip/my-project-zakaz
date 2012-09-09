<?php
/**
 * Habr controller class
 *
 * @author KANekT
 * @copyright (C) 2012 KANekT Habr effect extension for PunBB (C)
 * @package Habr
 */
class Nya_Habr_Controller_Habr extends Controller
{
	protected $habr;
	
	public function __construct($ext_path)
	{
		parent::__construct($ext_path);
		App::load_language('nya_habr.habr');
		$this->check_access();
		$this->set_filter(array('uid' => 'int',	'pid' => 'int',	'rid' => 'int'));
		$this->habr = new Nya_Habr_Model_Habr;
		$this->page = 'habr';
	}

	public function check_access()
	{
		if (App::$forum_user['g_habr_enable'] == 0)
			message(App::$lang['Group Disabled']);
		
		if (App::$forum_user['habr_disable_adm'] == 1)
			message(App::$lang['Individual Disabled']);

		if (App::$forum_user['habr_enable'] == 0)
			message(App::$lang['Your Disabled']);		
	}

	public function view()
	{
		if (isset($this->id))
		{
			if (FALSE === ($user_habr = $this->habr->get_by_id($this->id)))
				message(App::$lang_common['Bad request']);
				
			global $smilies;
			if (!defined('FORUM_PARSER_LOADED'))
			{
				require FORUM_ROOT.'include/parser.php';
			}	
			$user_habr['reason'] = parse_message($user_habr['reason'], 0);
				
				
			App::send_json(array('message' => $user_habr['reason']));
		}
		
		if (FALSE === ($user_habr = $this->habr->get_user($this->uid)))
			message(App::$lang_common['Bad request']); 

		App::$forum_page['form_action'] = forum_link(App::$forum_url['habr_delete']);
		View::$instance = View::factory($this->view.'view', array ('heading' => sprintf(App::$lang['User habr'], forum_htmlencode($user_habr['username'])) . '&nbsp;&nbsp;<strong>[+'. $user_habr['count_habr_plus'] . ' / -' . $user_habr['count_habr_minus'] .'] &nbsp;</strong>'));
		$count = $this->habr->count_by_user_id($this->uid);
		
		if ($count > 0)
		{
			global $smilies;
			if (!defined('FORUM_PARSER_LOADED'))
			{
				require FORUM_ROOT.'include/parser.php';
			}			
			App::paginate($count, App::$forum_user['disp_topics'], App::$forum_url['habr_view'],array($this->uid));
			
			if (App::$forum_user['g_id'] == FORUM_ADMIN)
			{
				/*
				 * Fix table layout described on: http://punbb.ru/post31786.html#p31786
				 */
				App::$forum_loader->add_css('#brd-habr table{table-layout:inherit;}', array('type' => 'inline'));
				$template = 'view_admin';
			}
			else
			{
				$template = 'view_user';
			}
			View::$instance->content = View::factory($this->view.$template, array ('records' => $this->habr->get_info($this->uid, App::$forum_user['g_id'], App::$forum_page['start_from'], App::$forum_page['finish_at']))); 
		}
		else {
			
			View::$instance->content = View::factory($this->view.'view_empty', array ('lang' => App::$lang));	
		}
	
		App::$forum_page['crumbs'][] = array(sprintf(App::$lang['User habr'], forum_htmlencode($user_habr['username'])), forum_link(App::$forum_url['habr_view'], $this->uid));
	}
	
	public function delete()
	{
		if (!isset($_POST['delete_habr_id'])) {
/*
 * TODO
 * Add info for signal of empty ids
 */			
			$this->view();
			return;
		}
		
		$idlist = implode(',',array_map(array($this, '_check_int_val'), $_POST['delete_habr_id']));
		$this->habr->delete($idlist);
		
		App::$forum_flash->add_info(App::$lang['Deleted redirect']);
		redirect(forum_link(App::$forum_url['habr_view'], array($this->uid)), App::$lang['Deleted redirect']);
	}
	
	public function plus()
	{
		$this->do_action('plus');
	}
	
	public function minus()
	{
		$this->do_action('minus');
	}
	
	private function do_action($action)
	{
		$target = $this->pre_process($action);
		$errors = array();
		
		if (isset($_POST['form_sent']))
		{
			if ($this->add_voice($errors, $target, $action))
			{
	    		App::$forum_flash->add_info(App::$lang['Redirect Message']);
    			redirect(forum_link(App::$forum_url['post'], $this->pid), App::$lang['Redirect Message']);			
			}
		}	
			
		App::$forum_page['form_action'] = forum_link(App::$forum_url['habr_'.$action], array($this->pid, $this->uid));
		
		if (App::$is_ajax) 
		{
			if (empty($errors))
			{
				App::send_json(array(		
					'csrf_token'=> generate_form_token(App::$forum_page['form_action']),
					'title'		=> App::$lang['Habr'],
					'description'=> sprintf(App::$lang[ucfirst($action)], forum_htmlencode($target['username'])),
					'user'		=>  $target['username'],
					'cancel'	=>  forum_htmlencode(App::$lang_common['Cancel']),
					'submit'	=>  forum_htmlencode(App::$lang_common['Submit'])
				));
			}
			else 
			{
				App::send_json(array(
					'error'	=> implode('<br />',$errors),
				));				
			}
		}		
		
		View::$instance = View::factory($this->view.'form', array('heading' => sprintf(App::$lang[ucfirst($action)],forum_htmlencode($target['username']))));
		View::$instance->errors = View::factory($this->view.'errors', array('errors'=>$errors, 'head' => App::$lang['Errors']));
	}
	
	private function add_voice(& $errors, $target, $method)
	{
		$message = $this->prepare_message($errors);
		
		if (empty($errors))
		{
			$this->habr->add_voice($target, $message, App::$forum_user['id'], $method);
            if($method = "minus")
            {
                if($target['first_post_id'] == $target['id'])
                {
                    $diff = $this->habr->get_topic_info_minus($target['topic_id'], $this->uid, App::$forum_config['o_habr_timeout']);
                    if($diff < 0)
                    {
                        $diff = -1*$diff;
                        if($diff > App::$forum_config['o_habr_count'])
                        {
                            if(isset(App::$forum_config['o_hide_post']))
                                $this->habr->post_hidden($this->pid, App::$lang['Habr Vote hidden']);
                            $this->habr->move_topic($target['topic_id'], App::$forum_config['o_habr_forum']);
                        }
                    }
                }
                else
                {
                    $diff = $this->habr->get_post_info_minus($this->pid, $this->uid, App::$forum_config['o_habr_timeout']);
                    if($diff < 0)
                    {
                        $diff = -1*$diff;
                        if($diff > App::$forum_config['o_habr_count'] AND isset(App::$forum_config['o_hide_post']))
                            $this->habr->post_hidden($this->pid, App::$lang['Habr Vote hidden']);
                    }
                }
                if ($target['user_immunity'] == 0)
                {
                    $diff = $this->habr->add_ro_user($this->uid, App::$forum_config['o_habr_read_only_time']);
                    if($diff < 0)
                    {
                        $diff = -1*$diff;
                        if($diff > App::$forum_config['o_habr_read_only_count'])
                        {
                            if(isset(App::$forum_config['o_user_read_only']))
                                $this->habr->user_ro($this->uid, App::$forum_config['o_user_read_only']);
                        }
                    }
                }
            }
            return TRUE;
		}
		return FALSE;
	}
	
	private function prepare_message(& $errors)
	{
		if (!isset($_POST['req_message']))
			message(App::$lang_common['Bad request']);
			
		$message = forum_linebreaks(forum_trim($_POST['req_message']));

		if ($message == '')
		{
			$errors[] = (App::$lang['No message']);
		}
		else if (strlen($message) > App::$forum_config['o_habr_maxmessage'])
		{
			$errors[] = sprintf(App::$lang['Too long message'], App::$forum_config['o_habr_maxmessage']);
		}
		
		if (App::$forum_config['p_message_bbcode'] == '1' || App::$forum_config['o_make_links'] == '1')
		{
			if (!defined('FORUM_PARSER_LOADED'))
			{
				require FORUM_ROOT.'include/parser.php';
			}
			$message = preparse_bbcode($message, $errors);
		}	
		return $message;	
	}
	
	private function pre_process($method)
	{
		if (!isset($this->pid) OR !isset($this->uid))
			message(App::$lang_common['Bad request']);
			
		if (App::$forum_user['is_guest'])
			message(App::$lang_common['No permission']);

		if (App::$forum_user['id'] == $this->uid)
    		message(App::$lang['Silly user']);

		if (($method == 'plus' OR $method == 'minus') AND App::$forum_user['g_habr_min'] > App::$forum_user['num_posts'])
		{
			message(App::$lang['Small Number of post']);
		}

		if (FALSE === ($target = $this->habr->get_post_info($this->pid, $this->uid, App::$forum_user['id'])))
			message(App::$lang_common['Bad request']);
			
		if ($target['post_id'] AND $this->pid == $target['post_id'])
			message(App::$lang['Error habr revote']);
			
		if ($target['habr_enable'] == 0 || $target['forum_immunity'] == 0 || $target['topic_immunity'] == 1 || $target['post_immunity'] == 1)
			message(App::$lang['Error habr vote']);
					
		App::$forum_page['crumbs'][] = array(sprintf(App::$lang['Message on topic'],forum_htmlencode($target['subject'])), forum_link(App::$forum_url['post'], $this->pid));
		
		if ($method == 'plus')
		{
			App::$forum_page['crumbs'][] = sprintf(App::$lang['Plus'], forum_htmlencode($target['username']));
		}
		else 
		{
			App::$forum_page['crumbs'][] = sprintf(App::$lang['Minus'], forum_htmlencode($target['username']));
		}

        $target['user_immunity'] = App::$forum_user['g_habr_immunity'];

		return $target;
	}
	
	private function _check_int_val($val)
	{
		if (!is_numeric($val))
			message(App::$lang_common['Bad request']);
			
		return $val;
	}	
}