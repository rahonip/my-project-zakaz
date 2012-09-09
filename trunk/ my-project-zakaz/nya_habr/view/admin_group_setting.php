				<div class="content-head">
					<h3 class="hn"><span><?php echo App::$lang['Habr permissions'] ?></span></h3>
				</div>
				<fieldset class="mf-set set<?php echo ++App::$forum_page['item_count'] ?>">
					<legend><span><?php echo App::$lang['Habr enable legend'] ?></span></legend>
					<div class="mf-box">
						<div class="mf-item">
							<span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="habr_enable" value="1"<?php if ($group['g_habr_enable'] == '1') echo ' checked="checked"' ?> /></span>
							<label for="fld<?php echo App::$forum_page['fld_count'] ?>"><?php echo App::$lang['Habr enable'] ?></label>
						</div>
					</div>
                    <legend><span><?php echo App::$lang['Habr immunity legend'] ?></span></legend>
                    <div class="mf-box">
                        <div class="mf-item">
                            <span class="fld-input"><input type="checkbox" id="fld<?php echo ++App::$forum_page['fld_count'] ?>" name="habr_immunity" value="1"<?php if ($group['g_habr_immunity'] == '1') echo ' checked="checked"' ?> /></span>
                            <label for="fld<?php echo App::$forum_page['fld_count'] ?>"><?php echo App::$lang['Habr immunity'] ?></label>
                        </div>
                    </div>
                </fieldset>
				<div class="sf-set set<?php echo ++App::$forum_page['item_count'] ?>">
					<div class="sf-box text">
						<label for="fld<?php echo ++App::$forum_page['fld_count'] ?>"><span><?php echo App::$lang['Min post for habr'] ?></span> <small><?php echo App::$lang['Min post habr help'] ?></small></label><br />
						<span class="fld-input"><input type="text" id="fld<?php echo App::$forum_page['fld_count'] ?>" name="habr_min" size="5" maxlength="4" value="<?php echo $group['g_habr_min'] ?>" /></span>
					</div>
				</div>	
