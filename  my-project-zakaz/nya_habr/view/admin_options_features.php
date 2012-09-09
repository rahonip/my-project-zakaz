<?php
    // Get the list of categories and forums from the DB
    $query = array(
        'SELECT'	=> 'c.id AS cid, c.cat_name, f.id AS fid, f.forum_name',
        'FROM'		=> 'categories AS c',
        'JOINS'		=> array(
            array(
                'INNER JOIN'	=> 'forums AS f',
                'ON'			=> 'c.id=f.cat_id'
            )
        ),
        'ORDER BY'	=> 'c.disp_position, c.id, f.disp_position'
    );

    $result = App::$forum_db->query_build($query) or error(__FILE__, __LINE__);

    $output = "";
    $cur_category = 0;
    $forum_count = 0;
    while ($cur_forum = App::$forum_db->fetch_assoc($result))
    {
        if ($cur_forum['cid'] != $cur_category)	// A new category since last iteration?
        {
            if ($cur_category)
                $output .= '</optgroup>';

            $output .= '<optgroup label="'.forum_htmlencode($cur_forum['cat_name']).'">';
            $cur_category = $cur_forum['cid'];
        }

        $output .= '<option value="'.$cur_forum['fid'].'"';
        (App::$forum_config['o_habr_forum'] == $cur_forum['fid']) ? $output .= ' selected="selected"' : '';
        $output .= '>'.forum_htmlencode($cur_forum['forum_name']).'</option>';
        $forum_count++;
    }
?>
    <div class="content-head">
        <h2 class="hn"><span><?php echo App::$lang['Habr features head'] ?></span></h2>
    </div>
    <fieldset class="frm-group group<?php echo ++App::$forum_page['group_count'] ?>">
        <legend class="group-legend"><span><?php echo App::$lang['Habr legend'] ?></span></legend>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box checkbox">
                <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="form[habr_show_full]" value="1"<?php if (App::$forum_config['o_habr_show_full'] == '1') echo ' checked="checked"' ?> /></span>
                <label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php echo App::$lang['Habr show full'] ?></span> <?php echo App::$lang['Habr show full descr'] ?></label>
            </div>
        </div>        
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Vote count'] ?></span><small><?php echo App::$lang['Vote count help'] ?></small></label><br />
                <span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_count]" size="6" maxlength="6" value="<?php echo intval(App::$forum_config['o_habr_count']) ?>" /></span>
            </div>
        </div>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Timeout'] ?></span><small><?php echo App::$lang['Timeout help'] ?></small></label><br />
                <span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_timeout]" size="6" maxlength="6" value="<?php echo App::$forum_config['o_habr_timeout'] ?>" /></span>
            </div>
        </div>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['ReadOnly count'] ?></span><small><?php echo App::$lang['ReadOnly count help'] ?></small></label><br />
                <span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_read_only_count]" size="6" maxlength="6" value="<?php echo App::$forum_config['o_habr_read_only_count'] ?>" /></span>
            </div>
        </div>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['ReadOnly time'] ?></span><small><?php echo App::$lang['ReadOnly time help'] ?></small></label><br />
                <span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_read_only_time]" size="6" maxlength="6" value="<?php echo App::$forum_config['o_habr_read_only_time'] ?>" /></span>
            </div>
        </div>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Max message'] ?></span><small><?php echo App::$lang['Max message help'] ?></small></label><br />
                <span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_maxmessage]" size="6" maxlength="6" value="<?php echo App::$forum_config['o_habr_maxmessage'] ?>" /></span>
            </div>
        </div>
        <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
            <div class="sf-box text">
                <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Forum'] ?></span><small><?php echo App::$lang['Forum help'] ?></small></label><br />
                <span class="fld-input"><select id="fld<?php echo App::$forum_page['fld_count'] ?>" name="form[habr_forum]"><?php echo $output ?></select></span>
            </div>
        </div>
    </fieldset>