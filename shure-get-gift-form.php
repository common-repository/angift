<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
?>
<div id="universal-message-container">
	<h2>Are you shure?</h2>

	<a
		href="admin.php?page=angift&tab=get_gift&action_id=<?php print(esc_html($_GET['action_id']));?>">Yes!</a>&nbsp;<a
		href="admin.php?page=angift&tab=actions">No</a>
</div>