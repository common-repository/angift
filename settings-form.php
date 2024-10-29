<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
?>
<form method="post">

	<div id="universal-message-container">
		<h2>Settings form</h2>

		<h4><?php print($message);?></h4>

		<div class="options">
			<p>
				<label>Your e-mail (not used for spam)</label> <br /> <input
					type="text" name="email"
					value="<?php sanitize_email(print($_POST['email'] ?? ''));?>" />
			</p>
			<p>
				<label>Password</label> <br /> <input type="password"
					name="password" value="" />
			</p>
		</div>
		<!-- #universal-message-container -->
	</div>
	<?php
wp_nonce_field('angift', 'angift');
submit_button();
?>
	</form>