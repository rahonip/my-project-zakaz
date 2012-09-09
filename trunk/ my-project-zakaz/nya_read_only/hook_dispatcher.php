<?php
/**
 * Habr hook dispatcher class
 * 
 * 
 * @author hcs
 * @copyright (C) 2011 hcs habr extension for PunBB Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package habr
 */
class RO_Hook_Dispatcher extends Base {
	/**
 	 * Front-end hook dispatcher
	 * Inject hooks for showing habr in topic messages
	 */
	public function vt_init()
	{
		App::load_language('nya_read_only');

        App::inject_hook('vt_row_pre_post_actions_merge',array(
            'name'	=>	'habr',
            'code'	=>	'RO_Hook_Dispatcher::vt_row_pre_post_actions_merge(& $forum_page, $forum_user, $cur_post);'
        ));

        App::inject_hook('vt_modify_page_details',array(
            'name'	=>	'habr',
            'code'	=>	'RO_Hook_Dispatcher::vt_modify_page_details(& $forum_user);'
        ));
    }

    public function vt_row_pre_post_actions_merge(& $forum_page, $forum_user)
    {
        if ($forum_user['user_read_only'] > mktime() && !$forum_page['is_admmod'])
        {
            $forum_page['post_actions'] = array();
            App::$forum_config['o_quickpost'] = 0;
            $forum_page['page_post']['posting'] = "";
        }
    }

    public function vt_modify_page_details(& $forum_user)
    {
        if ($forum_user['user_read_only'] > mktime() && !App::$forum_page['is_admmod'])
        {
            $forum_user['may_post'] = false;
        }
    }

    public function po_init()
    {
        App::load_language('nya_read_only');

        App::inject_hook('po_posting_location_selected',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'RO_Hook_Dispatcher::po_posting_location_selected($forum_user);'
        ));
    }

    public function po_posting_location_selected($forum_user)
    {
        if ($forum_user['user_read_only'] > mktime() && !App::$forum_page['is_admmod'])
            message(App::$lang_common['No permission']);
    }

    public function ed_init()
    {
        App::load_language('nya_read_only');

        App::inject_hook('ed_post_selected',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'RO_Hook_Dispatcher::ed_post_selected($forum_user);'
        ));
    }

    public function ed_post_selected($forum_user)
    {
        if ($forum_user['user_read_only'] > mktime() && !App::$forum_page['is_admmod'])
            message(App::$lang_common['No permission']);
    }

    public function dl_init()
    {
        App::inject_hook('dl_post_selected',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'RO_Hook_Dispatcher::dl_post_selected($forum_user);'
        ));
    }

    public function dl_post_selected($forum_user)
    {
        if ($forum_user['user_read_only'] > mktime() && !App::$forum_page['is_admmod'])
            message(App::$lang_common['No permission']);
    }

    /**
     * Profile dispatcher init
     */
    public function pf_init()
    {
        App::load_language('nya_read_only');

        App::inject_hook('pf_change_details_admin_pre_header_load',array(
            'name'	=>	'habr',
            'code'	=>	'RO_Hook_Dispatcher::pf_change_details_admin_pre_header_load(& $forum_page, $user, $id);'
        ));

        App::inject_hook('pf_change_details_about_pre_header_load',array(
            'name'	=>	'habr',
            'code'	=>	'RO_Hook_Dispatcher::pf_details_about_pre_header_load($user);'
        ));
    }

    /**
     * Hook pf_change_details_settings_local_fieldset_end handler
     * @param array $user
     * @param array $lang_profile
     */
    public function pf_change_details_admin_pre_header_load(& $forum_page, $user, $id)
    {
        if ($user['g_id'] != FORUM_ADMIN )
        {
            if ($user['user_read_only'] > 0)
                $forum_page['user_management']['ro'] = '<div class="ct-set set'.++$forum_page['item_count'].'">'."\n\t\t\t\t".'<div class="ct-box">'."\n\t\t\t\t\t".'<h3 class="ct-legend hn">'.App::$lang['User Read Only Legend'].'</h3>'."\n\t\t\t\t".'<p><a href="'.forum_link(App::$forum_url['user_unset_read_only'], array($id,generate_form_token('ro'.$id))).'">'.App::$lang['User unSet Read Only'].'</a></p>'."\n\t\t\t\t".'</div>'."\n\t\t\t".'</div>';
            else
                $forum_page['user_management']['ro'] = '<div class="ct-set set'.++$forum_page['item_count'].'">'."\n\t\t\t\t".'<div class="ct-box">'."\n\t\t\t\t\t".'<h3 class="ct-legend hn">'.App::$lang['User Read Only Legend'].'</h3>'."\n\t\t\t\t".'<p><a href="'.forum_link(App::$forum_url['user_set_read_only'], array($id,generate_form_token('ro'.$id))).'">'.App::$lang['User Set Read Only'].'</a></p>'."\n\t\t\t\t".'</div>'."\n\t\t\t".'</div>';
        }
    }

    /**
     * Hook pf_change_details_about_pre_header_load handler
     * @param array $user user data
     */
    public function pf_details_about_pre_header_load($user)
    {
        if ($user['user_read_only'] > 0)
        {
            App::$forum_page['user_info']['user_ro'] = '<li><span>'.App::$lang['User Read Only'].'</span></li></a> ';
        }
    }

    /*
      * Back-end hook  dispatcher
      * Inject hooks for manage global admin options of the habr
      */
	
	public function aop_init()
	{
		App::load_language('nya_read_only');

		App::inject_hook('aop_features_pre_message_fieldset_end',array(
			'name'	=>	'habr',
			'code'	=>	'RO_Hook_Dispatcher::aop_features_pre_message_fieldset_end($forum_page);'
		));
		
		App::inject_hook('aop_features_validation',array(
			'name'	=>	'habr',
			'code'	=>	'RO_Hook_Dispatcher::aop_features_validation($form);'
		));
	}
	
	/**
	 * Hook aop_features_message_fieldset_end handler
	 * Show global habr setting form
	 */
	public function aop_features_pre_message_fieldset_end($forum_page)
	{
		View::$instance = View::factory(FORUM_ROOT.'extensions/nya_read_only/view/admin_options_features', array('forum_page' => $forum_page));
		echo  View::$instance->render();
	}
	
	/**
	 * Hook aop_features_validation handler
	 * @param $form
	 */
	public function aop_features_validation(& $form)
	{
        $form['user_read_only'] = intval($form['user_read_only']);
    }

} 
