		<fieldset class="frm-group group<?php echo ++App::$forum_page['group_count'] ?>">
		<div class="ct-group">
	
		<table cellspacing="0">
			<thead>
				<tr>
				<th class="tc3"><?php echo App::$lang['From user'] ?></th>
				<th class="tc3"><?php echo App::$lang['For topic'] ?></th>
				<th class="tc3"  style="width:35%"><?php echo App::$lang['Reason'] ?></th>
				<th class="tc3" style="text-align:center;"><?php echo App::$lang['Estimation'] ?></th>
				<th class="tc3"><?php echo App::$lang['Date'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach ($records as $cur_rep) : 
			$cur_rep['reason']= parse_message($cur_rep['reason'], 0);
?>
				<tr>					
					<td><?php echo $cur_rep['from_user_name'] ? '<a href="'.forum_link(App::$forum_url['habr_view'], $cur_rep['from_user_id']).'">'. forum_htmlencode($cur_rep['from_user_name']).'</a>' :  App::$lang['Profile deleted'] ?></td>
					<td>
<?php 
	if ($cur_rep['read_forum'] == null ||  $cur_rep['read_forum'] == 1)
		echo $cur_rep['subject'] ? '<a href="'.forum_link(App::$forum_url['post'], $cur_rep['post_id']) . '">'.forum_htmlencode($cur_rep['subject']).'</a>' : App::$lang['Removed or deleted'];
	else 
		echo App::$lang['Topic not readable'];
?>
					</td>
					<td>
<?php 
	if ($cur_rep['read_forum'] == null ||  $cur_rep['read_forum'] == 1) {
		echo $cur_rep['reason'];
	}
	else 
		echo App::$lang['Message not readable'];	
?>
					</td>
					<td style="text-align:center;"><?php echo $cur_rep['habr_plus']==1 ? '<img src="'.forum_link('extensions/nya_habr').'/img/up.png" alt="+" border="0">' : '<img src="'.forum_link('extensions/nya_habr').'/img/down.png" alt="-" border="0">'; ?></td>
					<td><?php echo format_time($cur_rep['time']) ?></td>
				</tr>
<?php endforeach;?>
			</tbody>
		</table>
		</div>
		</fieldset>