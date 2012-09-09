<?php


if (!defined('FORUM'))
	die();


function top_get_page(&$forum_page){
	global $forum_config, $lang_common, $forum_url, $forum_user, $forum_db;

	$count = 10; // count users
	$guest = true; // display guest

	// Setup breadcrumbs
	$forum_page['crumbs'][] = array($forum_config['o_board_title'], forum_link($forum_url['index']));
	$forum_page['crumbs'][] = 'Топ 10 участников форума!';

	$forum_page['heading'] = 'По самому большому кол-ву постов';

	$query = array(
	  'SELECT'	=> 'u.id, u.username, u.num_posts AS num',
	  'FROM'		=> 'users AS u',
	  'ORDER BY'	=> 'u.num_posts DESC',
	  'LIMIT'		=> '0, '.$count
	);

	if (!$guest) $query['WHERE'] = 'u.id<>1';

	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);	

	$records = array();
	while ($row = $forum_db->fetch_assoc($result))
		$records[] = $row;

	$forum_page['list'] = $records;

	$o = page_render($forum_page);

	$forum_page['heading'] = 'По саммой большой репутатции';

	$query = array(
	  'SELECT'	=> 'u.id, u.username, u.rep_plus AS num',
	  'FROM'		=> 'users AS u',
	  'ORDER BY'	=> 'u.rep_plus DESC',
	  'LIMIT'		=> '0, '.$count
	);

	if (!$guest) $query['WHERE'] = 'u.id<>1';

	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);	

	$records = array();
	while ($row = $forum_db->fetch_assoc($result))
		$records[] = $row;

	$forum_page['list'] = $records;

	$o .= page_render($forum_page);

	$forum_page['heading'] = 'По самой отрицательной репутации';

	$query = array(
	  'SELECT'	=> 'u.id, u.username, u.rep_minus AS num',
	  'FROM'		=> 'users AS u',
	  'ORDER BY'	=> 'u.rep_minus DESC',
	  'LIMIT'		=> '0, '.$count
	);

	if (!$guest) $query['WHERE'] = 'u.id<>1';

	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);	

	$records = array();
	while ($row = $forum_db->fetch_assoc($result))
		$records[] = $row;

	$forum_page['list'] = $records;

	$o .= page_render($forum_page);

	return $o;
}

function page_render(&$forum_page){
	global $forum_url, $forum_user, $ext_info, $lang_common, $base_url, $ext_info;
	
	
	ob_start();
?>	

	<div class="main-head">
		<h2 class="hn"><span><?php echo $forum_page['heading'] ?></span></h2>
	</div>
	<div class="main-content main-frm">


		<div class="ct-group">
	
		<table cellspacing="0">
			<thead>
				<tr>
				<th class="tc3" style="width:90%">Имя</th>
				<th class="tc3" style="width:10%"></th>
				</tr>
			<tbody>
<?php foreach ($forum_page['list'] as $cur) : 

?>
				<tr>

					<td><?php echo '<a href="'.forum_link($forum_url['user'], $cur['id']).'">'. forum_htmlencode($cur['username']).'</a>' ?></td>	
					<td><?php echo $cur['num'] ?></td>
		
				</tr>
<?php endforeach;?>
			</tbody>
		</table>
		</div>

		
	</div>

	
<?php 
	$result = ob_get_contents();
	ob_end_clean();

	return $result;
}

