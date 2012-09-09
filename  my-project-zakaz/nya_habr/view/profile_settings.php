<div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
    <div class="sf-box checkbox">
<?php if ($user['id'] == App::$forum_user['id']): 
			if (App::$forum_user['is_admmod'] || $user['habr_disable_adm'] == 0) : ?>						
                    <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="form[habr_enable]" value="1"<?php if ($user['habr_enable'] == '1') echo ' checked="checked"' ?> /></span>
                    <label for="fld<?php echo App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Habr Manage'] ?></span><?php echo App::$lang['Habr Manage help'] ?></label>
<?php		else : ?>
                    <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="disabled" value="1" disabled="disabled" /></span>
                    <label for="fld<?php echo App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Habr Manage'] ?></span><?php echo App::$lang['Habr Individual Disabled'] ?></label>
<?php 		endif; ?>
<?php else : ?>
                    <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="form[habr_disable_adm]" value="1"<?php if ($user['habr_disable_adm'] == '1') echo ' checked="checked"' ?> /></span>
                    <label for="fld<?php echo App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Habr Manage'] ?></span><?php echo App::$lang['Habr Individual adm'] ?></label>
<?php endif; ?>							
    </div>
</div>
