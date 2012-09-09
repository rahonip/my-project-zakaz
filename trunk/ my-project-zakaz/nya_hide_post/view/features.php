<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
    <div class="sf-box text">
        <label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Hide Post label'] ?></span><small><?php echo sprintf(App::$lang['Hide Post help'],App::$forum_config['o_hide_post']) ?></small></label><br />
        <span class="fld-input"><input type="number" id="fld<?php echo $forum_page['fld_count'] ?>" name="form[hide_post]" size="5" maxlength="3" value="<?php echo App::$forum_config['o_hide_post'] ?>" /></span>
    </div>
</div>