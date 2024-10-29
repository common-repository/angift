<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
?>
<table class="wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<td class="manage-column column-title" width="25"><a
				href="admin.php?page=angift&tab=actions&orderby=id&order=desc"><span>id</span></a></td>
			<th scope="col" id="title" class="manage-column column-title"><span>Title</span></th>
			<th scope="col" id="description" class="manage-column column-poster">Poster</th>
		</tr>
	</thead>

	<tbody id="the-list">
	<?php foreach($actions as $action) {?>
		<tr id="post-1"
			class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-1">
			<td class="id" data-colname="id"><?php print($action->id);?></td>
			<td class="title column-title page-title" data-colname="Title"><?php print($action->title.'<br/><br/><a href="admin.php?page=angift&tab=shure_get_gift&action_id='.$action->id.'">Get this gift</a>');?>
			</td>
			<td class="author column-poster" data-colname="Poster"><img
				height="150" src="<?php print($action->img);?>"></td>
		</tr>
	<?php }?>
	</tbody>

	<tfoot>
		<tr>
			<td scope="col" id="id" class="manage-column column-title"><a
				href="admin.php?page=angift&tab=actions&orderby=id&order=desc"><span>id</span></a></td>
			<th scope="col" id="title" class="manage-column column-title"><span>Title</span></th>
			<th scope="col" id="description" class="manage-column column-poster">Poster</th>
		</tr>
	</tfoot>
</table>