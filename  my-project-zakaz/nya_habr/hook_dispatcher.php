<?php
/**
 * Habr hook dispatcher class
 *
 * @author KANekT
 * @copyright (C) 2012 KANekT Habr effect extension for PunBB (C)
 * @package Habr
 */
class Habr_Hook_Dispatcher extends Base {

	public function vt_init()
	{
		App::$forum_loader->add_css('.habr_plus_minus { font-style:italic; font-size: 90%; border-radius: 8px 8px; background-color:#F3F3F3; padding: 6px 12px !important;} .habr_plus_head { font-style:normal; color:#008000; } .habr_minus_head { font-style:normal; color:#FF0000; }', array('type' => 'inline'));
		App::$forum_loader->add_css($GLOBALS['ext_info']['url'].'/css/style.css', array('type' => 'url'));
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI_style(' .ui-widget {font-size: 0.8em;} .validateTips { border: 1px solid transparent; padding: 0.3em; }', 'ui_dailog_02'); // добавляем переопределение стиля в footer
		
		App::load_language('nya_habr.habr');

        App::inject_hook('vt_row_pre_display',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::vt_row_pre_display($forum_page, $cur_post, $cur_topic, $forum_user);'
        ));

		App::inject_hook('vt_qr_get_posts',array(
			'name'	=>	'habr',
			'url'	=>	$GLOBALS['ext_info']['url'],
			'code'	=>	'Habr_Hook_Dispatcher::vt_qr_get_posts($query, $posts_id);'
		));

        App::inject_hook('vt_qr_get_topic_info',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::vt_qr_get_topic_info($query);'
        ));

        App::inject_hook('vt_row_pre_post_actions_merge',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::vt_row_pre_post_actions_merge($forum_user, $cur_post);'
        ));
    }

	public function vt_row_pre_display(& $forum_page, $cur_post, $cur_topic, $forum_user)
	{
        App::$forum_page['message']['habr'] = '<div style="float: right;">';

        if ($cur_post['poster_id']!=1 && $forum_user['g_habr_enable'] == 1 && $cur_post['habr_enable'] == 1 && $forum_user['habr_disable_adm'] == 0 && $forum_user['habr_enable'] == 1)
        {
            $bufer = array ('minus' => array(), 'plus' => array());
            if (isset($forum_page['habr_info'][$cur_post['id']]))
            {
                foreach ($forum_page['habr_info'][$cur_post['id']] as $cur_habr_info )
                {
                    if ($cur_habr_info['from_user_id'] == $forum_user['id'])
                        $vote = true;

                    if ($cur_habr_info['habr_minus'])
                    {
                        $bufer['minus'][]= '<a href="'.forum_link(App::$forum_url['user'], $cur_habr_info['from_user_id']).'" rel="'.forum_link(App::$forum_url['habr_by_id'], $cur_habr_info['habr_id']).'">'.forum_htmlencode($cur_habr_info['username']).'</a>';
                    }
                    if ($cur_habr_info['habr_plus'])
                    {
                        $bufer['plus'][]= '<a href="'.forum_link(App::$forum_url['user'], $cur_habr_info['from_user_id']).'" rel="'.forum_link(App::$forum_url['habr_by_id'], $cur_habr_info['habr_id']).'">'.forum_htmlencode($cur_habr_info['username']).'</a>';
                    }
                }

                $habr = array();
                if (!empty($bufer['plus']))
                {
                    $habr[] = '<span class="habr_plus_head">'.App::$lang['Positive assessed'].'</span><span>'.implode(', ', $bufer['plus']).'</span>';
                }

                if (!empty($bufer['minus']))
                {
                    $habr[] = '<span class="habr_minus_head">'.App::$lang['Negative assessed'].'</span><span>'.implode(', ', $bufer['minus']).'</span>';
                }

                if(!empty($habr))
                {
                    $sig = '<div class="habr_plus_minus">'.implode(', ',$habr).'</div>';
                }
            }

            if(!$forum_user['is_guest'] AND $forum_user['id'] != $cur_post['poster_id'] AND $cur_topic['f_habr'] == 1 AND $cur_post['habr_immunity'] == 0 AND $cur_topic['habr_immunity'] == 0 AND !isset($vote))// AND $cur_post['habr_id'] == NULL)// AND $GLOBALS['forum_page']['habr_info'][$cur_post['id']]['habr_time'] < $GLOBALS['forum_page']['time'])
            {
                if (App::$forum_user['g_habr_min'] < App::$forum_user['num_posts'])
                {
                    App::$forum_page['message']['habr'] .= '<a class="habr_info_link" href="'.forum_link(App::$forum_url['habr_plus'], array($cur_post['id'],$cur_post['poster_id'])).'"><img src="'.forum_link('extensions/nya_habr').'/img/up.png" alt="+"></a>&nbsp;&nbsp;';
                    App::$forum_page['message']['habr'] .= '<a class="habr_info_link" href="'.forum_link(App::$forum_url['habr_minus'], array($cur_post['id'],$cur_post['poster_id'])).'"><img src="'.forum_link('extensions/nya_habr').'/img/down.png" alt="-"></a>';
                }
            }
        }

        $diff = $cur_post['habr_plus'] - $cur_post['habr_minus'];
        if ($diff > 0)
            App::$forum_page['message']['habr'] .= '&nbsp;&nbsp;<span class="habr positive">'.$diff.'</span></div>';
        elseif($diff == 0)
            App::$forum_page['message']['habr'] .= '&nbsp;&nbsp;<span class="habr zero">0</span></div>';
        else
            App::$forum_page['message']['habr'] .= '&nbsp;&nbsp;<span class="habr negative">'.$diff.'</span></div>';

        if($diff <= -App::$forum_config['o_habr_count']/2)
            App::$forum_page['message']['message'] = '<div class="habr_message_white">'.App::$forum_page['message']['message'].'</div>';
        else if($diff < 0)
            App::$forum_page['message']['message'] = '<div class="habr_message">'.App::$forum_page['message']['message'].'</div>';

        if (isset($sig) AND !empty($sig))
        {
            if (!isset($forum_page['message']['signature']))
            {
                $forum_page['message']['habr_sig'] = '<div class="sig-content"><span class="sig-line"><!-- --></span><br />'.$sig.'</div>';
            }
            else
            {
                $forum_page['message']['habr_sig'] = '<div class="sig-content"><br />'.$sig.'</div>';
            }
        }
	}

	public function vt_qr_get_posts(& $query, $posts_id)
	{
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI("Dialog");
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI("Fade");
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI("Resizable");
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI("Draggable");
		$GLOBALS['ext_jQuery_UI']->add_jQuery_UI("Button");
		
		$habr_js_env = '
    		PUNBB.env.habr_vars = {
				"Reason" : "'.App::$lang['Form reason'].'",
		    };';

		App::$forum_loader->add_js($habr_js_env, array('type' => 'inline'));
		App::$forum_loader->add_js($GLOBALS['ext_info']['url'].'/js/habr.js', array('type' => 'url'));

		$GLOBALS['forum_page']['habr_info'] = array();
		$query_habr = array(
			'SELECT'	=> 'h.id AS habr_id, h.post_id, u.username, h.from_user_id, h.habr_plus, h.habr_minus, h.time AS habr_time',
			'FROM'		=> 'habr AS h',
			'JOINS'		=> array(
				array(
					'INNER JOIN'	=> 'users AS u',
					'ON'			=> 'u.id = h.from_user_id'
				),
			),
			'WHERE'		=> 'h.post_id IN ('.implode(',', $posts_id).')'
		);
		
		$habr_result = App::$forum_db->query_build($query_habr) or error(__FILE__, __LINE__);
		
		while($cur_habr = App::$forum_db->fetch_assoc($habr_result))
		{
			$GLOBALS['forum_page']['habr_info'][$cur_habr['post_id']][] = $cur_habr;
		}
/**
 * 
 * TODO:
 * make query separately
 * temporary fix
 */		
		//$query['SELECT'] .= ', u.habr_plus, u.habr_minus, u.habr_enable, u.habr_disable_adm, h.id as habr_id';
		$query['SELECT'] .= ', u.habr_plus as uh_plus, u.habr_minus as uh_minus, u.habr_enable, u.habr_disable_adm, p.habr_immunity, p.habr_plus, p.habr_minus';
		/*
		$query['JOINS'][] = array(
			'LEFT JOIN'	=> 'habr AS h',
			'ON'			=> '(h.post_id = p.id AND h.from_user_id = '.$user_id.') OR (h.user_id = u.id AND h.from_user_id = '.$user_id.' AND h.time > '. $time.')'
		);	
		*/
		$GLOBALS['forum_page']['time'] = App::$now - App::$forum_config['o_habr_timeout']*60;
	}

    public function vt_qr_get_topic_info(& $query)
    {
        $query['SELECT'] .= ', t.habr_immunity, f.f_habr';
    }

    public function vt_row_pre_post_actions_merge($forum_user, $cur_post)
    {
        if ($cur_post['poster_id']!=1 && $forum_user['g_habr_enable'] == 1 && $cur_post['habr_enable'] == 1 && $forum_user['habr_disable_adm'] == 0 && $forum_user['habr_enable'] == 1)
        {
            App::$forum_page['author_info']['habr'] = '<li><span><a href="'.forum_link(App::$forum_url['habr_view'], $cur_post['poster_id']).'">'.App::$lang['Habr View Topic'].'</a> : ';

            if(!$forum_user['is_guest'] AND $forum_user['id'] != $cur_post['poster_id'])
            {
                if (App::$forum_config['o_habr_show_full']== '1' )
                {
                    App::$forum_page['author_info']['habr'] .= '[ <span style="color:green">'.$cur_post['uh_plus'] . '</span> | <span style="color:red">'. $cur_post['uh_minus'] . '</span> ]';
                }
                else
                {
                    App::$forum_page['author_info']['habr'] .= $cur_post['uh_plus'] - $cur_post['uh_minus'];
                }
            }
            else
            {
                if (App::$forum_config['o_habr_show_full']== '1' )
                {
                    App::$forum_page['author_info']['habr'] .= '[ <span style="color:green">'.$cur_post['uh_plus'] . '</span> | <span style="color:red">'. $cur_post['uh_minus'] . '</span> ]';
                }
                else
                {
                    App::$forum_page['author_info']['habr'] .= $cur_post['uh_plus'] - $cur_post['uh_minus'];
                }

                App::$forum_page['author_info']['habr'] .= '</span></li>';
            }
        }

    }

    public function po_init()
    {
        App::load_language('nya_habr.habr');

        App::inject_hook('po_pre_optional_fieldset',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::po_pre_optional_fieldset();'
        ));

        App::inject_hook('po_pre_add_topic',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::po_pre_add_topic($post_info);'
        ));
    }

    public function po_pre_optional_fieldset()
    {
        if(App::$forum_user['g_fp_enable'] == 1 AND !App::$forum_user['is_guest'])
            App::$forum_page['checkboxes']['habr_immunity'] = '<div class="mf-item"><span class="fld-input"><input type="checkbox" id="fld'.(++App::$forum_page['fld_count']).'" name="habr_immunity" value="1"'.(isset($_POST['habr_immunity']) ? ' checked="checked"' : '').' /></span> <label for="fld'.App::$forum_page['fld_count'].'">'.App::$lang['Immunity Topic'].'</label></div>';
    }

    public function po_pre_add_topic(& $post_info)
    {
        $post_info['habr_immunity'] = isset($_POST['habr_immunity']) ? 1 : 0;
    }

    public function ed_init()
    {
        App::load_language('nya_habr.habr');

        App::inject_hook('ed_pre_checkbox_display',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::ed_pre_checkbox_display($can_edit_subject, $cur_post);'
        ));

        App::inject_hook('ed_qr_update_subject',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::ed_qr_update_subject($query);'
        ));

        App::inject_hook('ed_qr_update_post',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::ed_qr_update_post($query);'
        ));

        App::inject_hook('ed_qr_get_post_info',array(
            'name'	=>	'nya_habr_immunity',
            'code'	=>	'Habr_Hook_Dispatcher::ed_qr_get_post_info($query);'
        ));
    }

    public function ed_pre_checkbox_display($can_edit_subject, $cur_post)
    {
        if ($can_edit_subject && App::$forum_user['g_fp_enable'] == 1 && !App::$forum_user['is_guest'])
            App::$forum_page['checkboxes']['habr_immunity'] = '<div class="mf-item"><span class="fld-input"><input type="checkbox" id="fld'.(++App::$forum_page['fld_count']).'" name="hi_topic" value="1"'.((isset($_POST['hi_topic']) || $cur_post['hi_topic'] == '1') ? ' checked="checked"' : '').' /></span> <label for="fld'.App::$forum_page['fld_count'].'">'.App::$lang['Immunity Topic'].'</label></div>';
        elseif (App::$forum_page['is_admmod'])
            App::$forum_page['checkboxes']['habr_immunity'] = '<div class="mf-item"><span class="fld-input"><input type="checkbox" id="fld'.(++App::$forum_page['fld_count']).'" name="hi_post" value="1"'.((isset($_POST['hi_post']) || $cur_post['hi_post'] == '1') ? ' checked="checked"' : '').' /></span> <label for="fld'.App::$forum_page['fld_count'].'">'.App::$lang['Immunity Post'].'</label></div>';
    }

    public function ed_qr_update_subject(& $query)
    {
        $query['SET'] .= ', habr_immunity='.(isset($_POST['hi_topic']) ? 1 : 0);
    }

    public function ed_qr_update_post(& $query)
    {
        $query['SET'] .= ', habr_immunity='.(isset($_POST['hi_post']) ? 1 : 0);
    }

    public function ed_qr_get_post_info(& $query)
    {
        $query['SELECT'] .= ', t.habr_immunity as hi_topic, p.habr_immunity as hi_post';
    }

    public function pf_init()
    {
        App::load_language('nya_habr.habr');

        App::inject_hook('pf_change_details_settings_pre_local_fieldset_end',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::pf_change_details_settings_pre_local_fieldset_end($user);'
        ));

        App::inject_hook('pf_change_details_settings_validation',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::pf_change_details_settings_validation($user, $form);'
        ));

        App::inject_hook('pf_change_details_about_pre_header_load',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::pf_details_about_pre_header_load($user);'
        ));

        App::inject_hook('pf_view_details_pre_header_load',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::pf_details_about_pre_header_load($user);'
        ));

        App::inject_hook('pf_delete_user_form_submitted',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::pf_delete_user_form_submitted($id);'
        ));
    }

    public function pf_change_details_settings_pre_local_fieldset_end($user)
    {
        View::$instance = View::factory(FORUM_ROOT.'extensions/nya_habr/view/profile_settings', array('user' => $user));
        echo  View::$instance->render();
    }

    public function pf_change_details_settings_validation($user, & $form)
    {
        if (App::$forum_user['is_admmod'] && $user['id'] != App::$forum_user['id'])
        {
            $form['habr_disable_adm'] = (isset($_POST['form']['habr_disable_adm'])) ? 1 :0;
        }
        else
        {
            $form['habr_enable'] = (isset($_POST['form']['habr_enable'])) ? 1 :0;
        }
    }

    public function pf_details_about_pre_header_load($user)
    {
        if ($user['habr_disable_adm'] == 1)
        {
            App::$forum_page['user_info']['habr'] = '<li><span>'.App::$lang['Individual Disabled'].'</span></li></a> ';
        }
        else if ($user['habr_enable'] == 0)
        {
            App::$forum_page['user_info']['habr'] = '<li><span>'.App::$lang['User Disable'].'</span></li></a> ';
        }
        else
        {
            App::$forum_page['user_info']['habr'] = '<li><span><a href="'.forum_link(App::$forum_url['habr_view'], $user['id']).'">'.App::$lang['Habr'].':</a> <strong>[ + '.$user['habr_plus'].' | '. $user['habr_minus'].' - ]</strong></span></li>';
        }
    }

    public function pf_delete_user_form_submitted($id)
    {
        $query = array(
            'DELETE'	=> 'habr',
            'WHERE'		=> 'user_id='.$id
        );
        App::$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }

	public function admin_init()
	{
		App::load_language('nya_habr.habr');
		
		App::inject_hook('agr_edit_end_qr_update_group',array(
			'name'	=>	'habr',
			'code'	=>	'Habr_Hook_Dispatcher::agr_edit_end_qr_update_group($query, $is_admin_group);'
		));

		App::inject_hook('agr_add_edit_group_flood_fieldset_end',array(
			'name'	=>	'habr',
			'code'	=>	'Habr_Hook_Dispatcher::agr_add_edit_group_flood_fieldset_end($group);'
		));

		App::inject_hook('aop_features_message_fieldset_end',array(
			'name'	=>	'habr',
			'code'	=>	'Habr_Hook_Dispatcher::aop_features_message_fieldset_end();'
		));
		
		App::inject_hook('aop_features_validation',array(
			'name'	=>	'habr',
			'code'	=>	'Habr_Hook_Dispatcher::aop_features_validation($form);'
		));

        App::inject_hook('afo_edit_forum_qr_get_forum_details',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::afo_edit_forum_qr_get_forum_details($query);'
        ));

        App::inject_hook('afo_edit_forum_pre_details_fieldset_end',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::afo_edit_forum_pre_details_fieldset_end($cur_forum);'
        ));

        App::inject_hook('afo_save_forum_qr_update_forum',array(
            'name'	=>	'habr',
            'code'	=>	'Habr_Hook_Dispatcher::afo_save_forum_qr_update_forum($query);'
        ));
	}

	public function agr_add_edit_group_flood_fieldset_end($group)
	{
		View::$instance = View::factory(FORUM_ROOT.'extensions/nya_habr/view/admin_group_setting', array('group' => $group));	
		echo  View::$instance->render();
	}

	public function aop_features_message_fieldset_end()
	{
		$forum_page['group_count'] = $forum_page['item_count'] = 0;
		View::$instance = View::factory(FORUM_ROOT.'extensions/nya_habr/view/admin_options_features', array('forum_page' => $forum_page));	
		echo  View::$instance->render();
	}

	public function agr_edit_end_qr_update_group(& $query, $is_admin_group)
	{
		$habr_enable = (isset($_POST['habr_enable']) && $_POST['habr_enable'] == '1') || $is_admin_group ? '1' : '0';
		$habr_min = isset($_POST['habr_min']) ? intval($_POST['habr_min']) : '0';
		$query['SET'] .= ', g_habr_enable= '.$habr_enable.', g_habr_min='.$habr_min;
	}

	public function aop_features_validation(& $form)
	{
		$form['habr_show_full'] = intval($form['habr_show_full']);
        $form['habr_count'] = intval($form['habr_count']);
		$form['habr_timeout'] = intval($form['habr_timeout']);
        $form['habr_read_only_count'] = intval($form['habr_read_only_count']);
        $form['habr_read_only_time'] = intval($form['habr_read_only_time']);
        $form['habr_maxmessage'] = intval($form['habr_maxmessage']);
        $form['habr_forum'] = intval($form['habr_forum']);
    }

    public function afo_edit_forum_qr_get_forum_details(& $query)
    {
        $query['SELECT'] .= ', f.f_habr';
    }

    public function afo_save_forum_qr_update_forum(& $query)
    {
        $habr_enable = (isset($_POST['f_habr']) && $_POST['f_habr'] == '1') ? '1' : '0';
        $query['SET'] .= ', f_habr='.$habr_enable;
    }

    public function afo_edit_forum_pre_details_fieldset_end($cur_forum)
    {
        View::$instance = View::factory(FORUM_ROOT.'extensions/nya_habr/view/admin_forum_setting', array('cur_forum' => $cur_forum));
        echo  View::$instance->render();
    }
}