                    <div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
                        <div class="sf-box checkbox">
                            <label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Habr enable'] ?></span></label><br />
                            <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="f_habr" value="1"<?php if ($cur_forum['f_habr'] == '1') echo ' checked="checked"' ?> /></span>
                        </div>
                    </div>
