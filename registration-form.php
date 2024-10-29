<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post">

		<div id="universal-message-container">
			<h2>Registration form</h2>

			<h4><?php print($message);?></h4>

			<div class="options">
				<p>
					<label>Your e-mail (not used for spam)</label> <br /> <input
						type="text" name="email"
						value="<?php print(sanitize_email($_POST['email'] ?? ''));?>" />
				</p>
				<p>
					<label>Password</label> <br /> <input type="password"
						name="password" value="" />
				</p>
				<p>
					<label>Password confirmation</label> <br /> <input type="password"
						name="password-confirmation" value="" />
				</p>
			</div>
			<!-- #universal-message-container -->
		</div>
	<?php
wp_nonce_field('angift', 'angift');
submit_button();
?>
	</form>
</div>