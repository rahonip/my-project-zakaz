<?php

/**
 * Add advertisment to forum pages
 *
 *	hcs_ads_manager
 * @copyright (C) 2010 hcs hcs@mail.ru
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 *	Extension for PunBB (C) 2008-2009 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */


// Make sure no one attempts to run this script "directly"
if (!defined('FORUM'))
	exit;
	
if (isset($_POST['add_ads'])) {

	$ads_name = (isset($_POST['ads_name'])) ? $_POST['ads_name'] : 'Empty block';
	
	$query = array(
		'INSERT'	=> 'name',
		'INTO'		=> 'hcs_ads_code',
		'VALUES'	=> '\''.$forum_db->escape($ads_name).'\''
	);

	$forum_db->query_build($query) or error(__FILE__, __LINE__);
	
	$forum_flash->add_info($lang_hcs_ads['Ads added']);
	redirect(forum_link($forum_url['hcs_ads_manager'].'&amp;id='.$forum_db->insert_id()), $lang_hcs_ads['Ads added'].' '.$lang_hcs_ads['Redirect']);		
}
elseif (isset($_GET['id'])) {

	$ads_id = intval($_GET['id']);

	if (isset($_GET['f'])){
		if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token('del_banner'.$forum_user['id']))
			csrf_confirm_form();
		if (file_exists(EXT_PATH.'/uploads/'.$_GET['f']))
			unlink(EXT_PATH.'/uploads/'.$_GET['f']);
		else 
			message('Файл не найден');
			
		$query = array(
			'UPDATE'	=> 'hcs_ads_code',
			'SET'		=> 'file_name=\'\'',
			'WHERE'		=> 'id='.$ads_id
		);
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
	}	
	
	
	$query = array(
		'SELECT'	=> '*',
		'FROM'		=> 'hcs_ads_code',
		'WHERE'		=> 'id='.$ads_id
	);

	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	$cur_ads = $forum_db->fetch_assoc($result);
	
	if (empty($cur_ads['id']))
		message($lang_common['Bad request']);
	
	if (isset($_GET['enable'])) {

		if ((!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token(forum_link($forum_url['hcs_ads_manager']).'&id='.$ads_id)))
			csrf_confirm_form();
			
			$query = array(
				'UPDATE'	=> 'hcs_ads_code',
				'SET'		=> 'enabled = 1', 
				'WHERE'		=> 'id='.$ads_id
			);
			
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			
			generate_ads_cache();
			
			$forum_flash->add_info($lang_hcs_ads['Ads enabled']);
			redirect(forum_link($forum_url['hcs_ads_manager']), $lang_hcs_ads['Ads enabled'].' '.$lang_hcs_ads['Redirect']);			
			
	}

	if (isset($_GET['disable'])) {

		if ((!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token(forum_link($forum_url['hcs_ads_manager']).'&id='.$cur_ads['id'])))
			csrf_confirm_form();
			
			$query = array(
				'UPDATE'	=> 'hcs_ads_code',
				'SET'		=> 'enabled = 0', 
				'WHERE'		=> 'id='.$ads_id
			);
			
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			
			generate_ads_cache();
			
			$forum_flash->add_info($lang_hcs_ads['Ads enabled']);
			redirect(forum_link($forum_url['hcs_ads_manager']), $lang_hcs_ads['Ads disabled'].' '.$lang_hcs_ads['Redirect']);			
	}
	
	if (isset($_POST['save'])) {

		$errors = array();
		
		foreach ($ads_pages as $page_name) {
			$pages[$page_name]['enable'] = (isset($_POST['ads_enable'][$page_name])) ? 1 : 0;
			$pages[$page_name]['guest'] = (isset($_POST['ads_guest'][$page_name])) ? 1 : 0;
			$pages[$page_name]['num_pos'] = (isset($_POST['ads_numpos'][$page_name])) ? intval($_POST['ads_numpos'][$page_name]) : 0; 			 
			$pages[$page_name]['position'] = (isset($_POST['ads_position'][$page_name])) ? intval($_POST['ads_position'][$page_name]) : 0;
		}

		$new_f_image = '';
		
		if ( isset($_FILES['req_file_ads_image']) && $_FILES['req_file_ads_image']['size'] != 0 ) {
			
			$uploaded_file = $_FILES['req_file_ads_image'];

			// Make sure the upload went smooth
			if (isset($uploaded_file['error']) && empty($errors))
			{
				switch ($uploaded_file['error'])
				{
					case 1:	// UPLOAD_ERR_INI_SIZE
					case 2:	// UPLOAD_ERR_FORM_SIZE
						$errors[] = $lang_profile['Too large ini'];
						break;

					case 3:	// UPLOAD_ERR_PARTIAL
						$errors[] = $lang_profile['Partial upload'];
						break;

					case 4:	// UPLOAD_ERR_NO_FILE
						$errors[] = $lang_profile['No file'];
						break;

					case 6:	// UPLOAD_ERR_NO_TMP_DIR
						$errors[] = $lang_profile['No tmp directory'];
						break;

					default:
						// No error occured, but was something actually uploaded?
						if ($uploaded_file['size'] == 0)
							$errors[] = $lang_profile['No file'];
						break;
				}
			}

			
			if (is_uploaded_file($uploaded_file['tmp_name']) && empty($errors))
			{
				if (empty($errors))
				{
					// Move the file to the avatar directory. We do this before checking the width/height to circumvent open_basedir restrictions.
					//if (!@move_uploaded_file($uploaded_file['tmp_name'], $ext_info['path'].'/uploads/banner.tmp'))
					if (!move_uploaded_file($uploaded_file['tmp_name'], EXT_PATH.'/uploads/'.$uploaded_file['name']))
						$errors[] = sprintf($lang_profile['Move failed'], '<a href="mailto:'.forum_htmlencode($forum_config['o_admin_email']).'">'.forum_htmlencode($forum_config['o_admin_email']).'</a>');

					if (empty($errors))
					{
						$new_f_image =  $uploaded_file['name'];
						//@rename($ext_info['path'].'/uploads/banner.tmp', $ext_info['path'].'/uploads/'.$new_f_image);
						@chmod(EXT_PATH.'/uploads/'.$new_f_image, 0644);
						if ($cur_ads['file_name']!='' && file_exists(EXT_PATH.'/uploads/'.$cur_ads['file_name']))
							@unlink(EXT_PATH.'/uploads/'.$cur_ads['file_name']);						
					}
				}
			}
			else //if (empty($errors))
				$errors[] = $lang_profile['Unknown failure'];
		}		
		
		
		
		
		if (!isset($_POST['ads_name']) || $_POST['ads_name'] == '' )
			$errors[] = $lang_hcs_ads['Invalid ads name'];
		
		$ads_enabled = (isset($_POST['ads_enabled'])) ? 1 : 0;
			
		if (empty($errors)) {
			$query = array(
				'UPDATE'	=> 'hcs_ads_code',
				'SET'		=> 'name=\''.$forum_db->escape($_POST['ads_name']).'\', code=\''.$forum_db->escape($_POST['ads_code']).'\',	page_index=\''.$forum_db->escape(serialize($pages['index'])).'\', page_forum=\''.$forum_db->escape(serialize($pages['forum'])).'\', page_topic=\''.$forum_db->escape(serialize($pages['topic'])).'\', page_search=\''.$forum_db->escape(serialize($pages['search'])).'\', enabled=\''.$ads_enabled.'\'',
				'WHERE'		=> 'id='.$ads_id
			);
			
			if ($forum_db->escape($new_f_image) != '')
				$query['SET'] .= ', file_name=\''. $forum_db->escape($new_f_image) .'\'';
			
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			
			generate_ads_cache();
			
			$forum_flash->add_info($lang_hcs_ads['Ads updated']);
			redirect(forum_link($forum_url['hcs_ads_manager']).'&id='.$ads_id, $lang_hcs_ads['Ads updated'].' '.$lang_hcs_ads['Redirect']);
		}
	}
	
	function check_serialized($page) {
		if (!is_array($page))
			$page = array('enable' => 0, 'guest' => 0, 'num_pos' => 0, 'position' => 0);
		return $page;
	}

	if (isset($_GET['delete'])) {
		// User pressed the cancel button
		if (isset($_POST['del_ads_cancel']))
			redirect(forum_link($forum_url['hcs_ads_manager']), $lang_admin_common['Cancel redirect']);
			
		if (file_exists(EXT_PATH.'/uploads/'.$cur_ads['file_name']) && $cur_ads['file_name'] != '')
			unlink(EXT_PATH.'/uploads/'.$cur_ads['file_name']);						
			
		if (isset($_POST['del_ads_comply'])) {

			$query = array(
				'DELETE'	=> 'hcs_ads_code',
				'WHERE'		=> 'id='.$ads_id
			);

			$forum_db->query_build($query) or error(__FILE__, __LINE__);
			
			$forum_flash->add_info($lang_hcs_ads['Ads deleted']);
			redirect(forum_link($forum_url['hcs_ads_manager']), $lang_hcs_ads['Ads deleted'].' '.$lang_hcs_ads['Redirect']);
		}
		else {
			
			// Setup breadcrumbs
			$forum_page['crumbs'][] = $lang_hcs_ads['Delete ads'];

			define('FORUM_PAGE_SECTION', 'management');
			define('FORUM_PAGE', 'admin-ads-manage');
			require FORUM_ROOT.'header.php';

			// START SUBST - <!-- forum_main -->
			ob_start();

?>
	<div class="main-subhead">
		<h2 class="hn"><span><?php printf($lang_hcs_ads['Confirm delete ads'], forum_htmlencode($cur_ads['name'])) ?></span></h2>
	</div>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo forum_link($forum_url['hcs_ads_manager']) ?>&id=<?php echo $ads_id ?>&delete">
			<div class="hidden">
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token(forum_link($forum_url['hcs_ads_manager']).'&id='.$ads_id.'&delete') ?>" />
			</div>
			<div class="ct-box warn-box">
				<p class="warn"><?php echo $lang_hcs_ads['Delete ads warning'] ?></p>
			</div>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="del_ads_comply" value="<?php echo $lang_hcs_ads['Delete ads'] ?>" /></span>
				<span class="cancel"><input type="submit" name="del_ads_cancel" value="<?php echo $lang_admin_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>

<?php
		}
		
		
			
	}
	else {
		
	
		if (!isset($errors)) {
			$pages['index'] = check_serialized(@unserialize($cur_ads['page_index']));
			$pages['forum'] = check_serialized(@unserialize($cur_ads['page_forum']));
			$pages['topic'] = check_serialized(@unserialize($cur_ads['page_topic']));
			$pages['search'] = check_serialized(@unserialize($cur_ads['page_search']));
		}
		
		define('FORUM_PAGE_SECTION', 'management');
		define('FORUM_PAGE', 'admin-ads-manage');
		require FORUM_ROOT.'header.php';

		$forum_page['item_count'] = $forum_page['group_count'] = $forum_page['fld_count'] = 0;
	
		// START SUBST - <!-- forum_main -->
		ob_start();
?>
<script>
function insert_img(img)
{
	var t = document.getElementsByName('ads_code');
	t[0].value += '<img src="' + img + '">';
}
</script>

	<div class="main-subhead">
		<h2 class="hn"><span><?php printf($lang_hcs_ads['Edit ad head'], forum_htmlencode($cur_ads['name'])) ?></span></h2>
	</div>
	<div class="main-content main-frm">
	
<?php
		// If there were any errors, show them
		if (!empty($errors)) :
			$forum_page['errors'] = array();
			foreach ($errors as $cur_error)
				$forum_page['errors'][] = '<li class="warn"><span>'.$cur_error.'</span></li>';

?>
		<div class="ct-box error-box">
			<h2 class="warn hn"><?php echo $lang_hcs_ads['Update ads errors'] ?></h2>
			<ul class="error-list">
				<?php echo implode("\n\t\t\t\t", $forum_page['errors'])."\n" ?>
			</ul>
		</div>
<?php 	endif ?>	
		<form method="post" class="frm-form" accept-charset="utf-8" action="<?php echo forum_link($forum_url['hcs_ads_manager']) ?>&amp;id=<?php echo $ads_id ?>" enctype="multipart/form-data">
			<div class="hidden">
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token(forum_link($forum_url['hcs_ads_manager']).'&amp;id='.$ads_id) ?>" />
			</div>
			<div class="content-head">
				<h3 class="hn"><span><?php echo $lang_hcs_ads['Edit ads details head'] ?></span></h3>
			</div>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_hcs_ads['Edit ads details legend'] ?></strong></legend>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box text">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Ads name'] ?></span></label><br />
						<span class="fld-input"><input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="ads_name" size="35" maxlength="80" value="<?php echo forum_htmlencode($cur_ads['name']) ?>" /></span>
					</div>
				</div>
				<div class="txt-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="txt-box textarea">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Ads block code'] ?></span> <small><?php echo $lang_hcs_ads['Ads code description help'] ?></small></label><br />
						<div class="txt-input"><span class="fld-input"><textarea id="fld<?php echo $forum_page['fld_count'] ?>" name="ads_code" rows="7" cols="50"><?php echo forum_htmlencode($cur_ads['code']) ?></textarea></span></div>
					</div>
				</div>
				
				
			<div class="ct-set set<?php echo ++$forum_page['item_count'] ?>">
						<div class="ct-box">
							<h3 class="ct-legend hn"><span><?php echo $lang_hcs_ads['current file'] ?></span></h3>
<?php
	if ($cur_ads['file_name']!=''): 
	$ext_file_name = EXT_URL.'/uploads/'.$cur_ads['file_name'];
?>
							<p class="options"><span class="fld-input"><img src="<?php echo $ext_file_name; ?>" width="240px" alt="" /></span></p>
							<p class="options"><span class="fld-input"><input id="fld<?php echo ++$forum_page['fld_count'] ?>" name="cut_file_name" type="text" size="80" value="<?php echo $ext_file_name ?>" /></span></p>
<?php endif; ?>
							<p><?php echo ($cur_ads['file_name']!='') ? '<a href="'.forum_link($forum_url['hcs_ads_manager']).'&id='.$cur_ads['id'].'&f='.$cur_ads['file_name'].'&csrf_token='.generate_form_token('del_banner'.$forum_user['id']).'"><strong>'.$lang_hcs_ads['Delete image info'].'</strong></a> <a href="#" onclick="insert_img(\''.$ext_file_name.'\');"><strong>'.$lang_hcs_ads['Insert image info'].'</strong></a>' : $lang_hcs_ads['No image info'] ?></p>
						</div>
					</div>				
				
				
					<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
						<div class="sf-box text required">
							<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Upload image file'] ?></span><small><?php echo $lang_hcs_ads['Image upload help'] ?></small></label><br />
							<span class="fld-input"><input id="fld<?php echo ++$forum_page['fld_count'] ?>" name="req_file_ads_image" type="file" size="60" /></span>
						</div>
					</div>		
					
							
				
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="ads_enabled" value="1"<?php if ($cur_ads['enabled'] == 1) echo ' checked="checked"'; ?> /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php  echo $lang_hcs_ads['Ads enable block']  ?></span><?php  echo $lang_hcs_ads['Enable']  ?></label>
					</div>					
				</div>				
			</fieldset>
<?php
		foreach ($pages as $page_name => $cur_page) :
			$forum_page['group_count'] = 0;  
?>
	
			<div class="content-head">
				<h2 class="hn"><span><?php printf($lang_hcs_ads['Page ads edit head'], forum_htmlencode($lang_hcs_ads[$page_name])) ?></span></h2>
			</div>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_hcs_ads['Ads page setup legend'] ?></strong></legend>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="ads_enable[<?php echo $page_name ?>]" value="1"<?php if ($cur_page['enable'] == 1) echo ' checked="checked"'; ?> /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php  echo $lang_hcs_ads['Ads enable on page']  ?></span><?php  echo $lang_hcs_ads['Enable']  ?></label>
					</div>					
				</div>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="ads_guest[<?php echo $page_name ?>]" value="1"<?php if ($cur_page['guest'] == 1) echo ' checked="checked"'; ?> /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php  echo $lang_hcs_ads['Ads enable for Guest']  ?></span><?php  echo $lang_hcs_ads['Enable']  ?></label>
					</div>					
				</div>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box select">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Ads position label'] ?></span></label><br />
						<span class="fld-input">
							<select id="fld<?php echo $forum_page['fld_count'] ?>" name="ads_position[<?php echo $page_name ?>]">
								<option value="0"<?php if ($cur_page['position'] == 0) echo ' selected="selected"' ?>><?php echo $lang_hcs_ads['Before'] ?></option>
								<option value="1"<?php if ($cur_page['position'] == 1) echo ' selected="selected"' ?>><?php echo $lang_hcs_ads['After'] ?></option>
								<option value="2"<?php if ($cur_page['position'] == 2) echo ' selected="selected"' ?>><?php echo $lang_hcs_ads['After position'] ?></option>
							</select>
						</span>													
					</div>
				</div>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box text">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Ads numpos label'] ?></span><small><?php echo $lang_hcs_ads['Ads numpos help'] ?></small></label><br />
						<span class="fld-input"><input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="ads_numpos[<?php echo $page_name ?>]" size="4" maxlength="2" value="<?php echo intval($cur_page['num_pos']) ?>" /></span>
					</div>
				</div>					
			</fieldset>
<?php endforeach; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></span>
			</div>
		</form>
	</div>
<?php 	
	
	}
}	
else {
	
	define('FORUM_PAGE_SECTION', 'management');
	define('FORUM_PAGE', 'admin-ads-manage');
	require FORUM_ROOT.'header.php';

	$forum_page['item_count'] = $forum_page['group_count'] = $forum_page['fld_count'] = 0;
	
	// START SUBST - <!-- forum_main -->
	ob_start();
	

?>

	<div class="main-subhead">
		<h2 class="hn"><span><?php echo $lang_hcs_ads['Add ads head'] ?></span></h2>
	</div>
	<div class="main-content main-frm">
		<form method="post" class="frm-form" accept-charset="utf-8" action="<?php echo forum_link($forum_url['hcs_ads_manager']) ?>">
			<div class="hidden">
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token(forum_link($forum_url['hcs_ads_manager'])) ?>" />
			</div>
			<div class="content-head">
				<h3 class="hn"><span><?php echo $lang_hcs_ads['Add ads details head'] ?></span></h3>
			</div>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_hcs_ads['Add ads details legend'] ?></strong></legend>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box text">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_hcs_ads['Ads name'] ?></span></label><br />
						<span class="fld-input"><input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="ads_name" size="35" maxlength="80" value="" /></span>
					</div>
				</div>
			</fieldset>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="add_ads" value="<?php echo $lang_hcs_ads['Ads add'] ?>" /></span>
			</div>
		</form>
	</div>
	<div class="main-subhead">
		<h2 class="hn"><span><?php echo $lang_hcs_ads['Edit ads head'] ?></span></h2>
	</div>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo forum_link($forum_url['hcs_ads_manager']) ?>?action=add">
			<div class="hidden">
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token(forum_link($forum_url['hcs_ads_manager']).'?action=add') ?>" />
			</div>

<?php

	$query = array(
		'SELECT'	=> '*',
		'FROM'		=> 'hcs_ads_code',
		'ORDER BY'	=> 'id'
	);
	
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	$forum_page['group_count'] = $forum_page['item_count'] = 0;
?>

<?php
$ads = array();
while ($cur_ads = $forum_db->fetch_assoc($result))
{
	$ads[] = $cur_ads;
}
if (!empty($ads)) : ?>
			<div class="main-content">
				<table cellspacing="0">
					<thead>
						<tr>
							<th class="tc0" scope="col">№</th>
							<th class="tc1" scope="col"><?php echo $lang_hcs_ads['Ads name'] ?></th>
							<th class="tc1" scope="col"><?php echo $lang_hcs_ads['Status manage'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_hcs_ads['Edit'] ?></th>
							<th class="tc3" scope="col"><?php echo $lang_hcs_ads['Delete'] ?></th>
					
						</tr>
					</thead>
					<tbody>
<?php foreach ($ads as $cur_ads) : 
		$link = forum_link($forum_url['hcs_ads_manager']).'&id='.$cur_ads['id'];
		$token = generate_form_token($link);
?>
						<tr class="odd row<?php echo ++$forum_page['item_count'] ?>">
							<td class="tc0"><?php echo $forum_page['item_count'] ?></td>
							<td class="tc1"><?php echo forum_htmlencode($cur_ads['name']) ?></td>
							<td class="tc1"><?php echo ($cur_ads['enabled'] == 1 ) ? '<a href="'. $link.'&amp;disable&amp;csrf_token='.$token.'">'.$lang_hcs_ads['Disable'].'</a>' : '<a href="'. $link.'&amp;enable&amp;csrf_token='.$token.'">'.$lang_hcs_ads['Enable'].'</a>' ?></td>
							<td class="tc2"><a href="<?php echo forum_link($forum_url['hcs_ads_manager']).'&amp;id='.$cur_ads['id'] ?>"><?php echo $lang_hcs_ads['Edit'] ?></a></td>
							<td class="tc3"><a href="<?php echo forum_link($forum_url['hcs_ads_manager']).'&amp;id='.$cur_ads['id'].'&delete' ?>"><?php echo $lang_hcs_ads['Delete'] ?></a></td>
						</tr>
<?php endforeach; ?>
					</tbody>
				</table>
			</div>
<?php else : ?>
			<div class="frm-group frm-hdgroup group<?php echo ++$forum_page['group_count'] ?>">
				<fieldset class="mf-set set<?php echo ++$forum_page['item_count'] ?><?php echo ($forum_page['item_count'] == 1) ? ' mf-head' : ' mf-extra' ?>">
					<?php echo $lang_hcs_ads['Ads is empty'] ?>
				</fieldset>
			</div>					
<?php endif; ?>			
		</form>
	</div>
<?php 

}
