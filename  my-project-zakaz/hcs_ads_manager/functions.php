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
	
function generate_ads_cache()
{
	global $forum_db, $ads_pages;


	// Get the censor list from the DB
	$query = array(
		'SELECT'	=> '*',
		'FROM'		=> 'hcs_ads_code',
		'WHERE'		=> 'enabled=1'
	);

	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	$output = array();
	$ads_code = array();
	$enabled_pages = array ();
	while ($cur_ads = $forum_db->fetch_assoc($result)) {
		
		foreach ($ads_pages as $page_name) {
			$cur_ads['page_'.$page_name] = unserialize($cur_ads['page_'.$page_name]);
			if ($cur_ads['page_'.$page_name]['enable'] == 1) {
				$enabled_pages[$page_name][$cur_ads['id']] = array ('guest' => $cur_ads['page_'.$page_name]['guest'], 'num_pos' => $cur_ads['page_'.$page_name]['num_pos'], 'position' => $cur_ads['page_'.$page_name]['position']); 
			}
		}
		
		if (!empty($enabled_pages)) {
			$ads_code[$cur_ads['id']] = $cur_ads['code'];
		}
		
		$output[] = $cur_ads;
	}

	// Output censors list as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_ads.php', 'wb');
	if (!$fh)
		error('Unable to write censor cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'.', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'define(\'ADS_CACHE_LOADED\', 1);'."\n\n".'$ads_cache_pages = '.var_export($enabled_pages, true).';'."\n\n".'$ads_cache_code = '.var_export($ads_code, true).';'."\n\n".'?>');

	fclose($fh);
}


function get_ads_block($page, $position, $cur_num = 0, $total_count = 0, $force_show = false){
	global $ads_cache_pages, $ads_cache_code,$hcs_ads_placeholder, $forum_user;
	//static $already_show = false;
	
	if (isset($ads_cache_pages[$page])) :
	
		$num_delta = ($page == 'index') ? 2 : 1;
						
		foreach ($ads_cache_pages[$page] as $code_id => $params) :
			if($params['guest'] == 0 || ($forum_user['is_guest'] && $params['guest'] == 1))
			{
				if ($params['position'] == $position) {
					if ( $position < 2) {
						$hcs_ads_placeholder[$position][] = $ads_cache_code[$code_id];
						$ads_cache_pages[$page][$code_id]['show'] = true;
					}
					elseif ($cur_num != 0) {
						
						if ($force_show && !isset($ads_cache_pages[$page][$code_id]['show'])) {
							echo $ads_cache_code[$code_id];
							$ads_cache_pages[$page][$code_id]['show'] = true;						
						}					
						
						if ( $total_count > 1 && $cur_num == $total_count && $params['num_pos'] + $num_delta > $total_count && ( $total_count > 2  || ($total_count <= 2  &&  !isset($ads_cache_pages[$page][$code_id]['show'])))) {
								echo $ads_cache_code[$code_id];
								$ads_cache_pages[$page][$code_id]['show'] = true;
						}	
						if($params['num_pos'] + $num_delta == $cur_num && !isset($ads_cache_pages[$page][$code_id]['show'])) {
								echo $ads_cache_code[$code_id];
								$ads_cache_pages[$page][$code_id]['show'] = true;
						}
					}
				}
			}
		endforeach;
	endif;
}

$ads_pages = array('index','forum','topic','search');
$hcs_ads_placeholder = array();

if (file_exists(FORUM_CACHE_DIR.'cache_ads.php'))
	include FORUM_CACHE_DIR.'cache_ads.php';

if (!defined('ADS_CACHE_LOADED'))
{
	generate_ads_cache();
	require FORUM_CACHE_DIR.'cache_ads.php';
}
