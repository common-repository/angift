<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<ul class="subsubsub">
		<li class="maual"><a href="admin.php?page=angift&tab=manual"
			class="<?php print($activeTab === 'manual' ? 'current' : '');?>"
			aria-current="page">Manual</a> |</li>
		<li class="actions"><a href="admin.php?page=angift&tab=actions"
			class="<?php print($activeTab === 'actions' ? 'current' : '');?>">List
				of actions</span>
		</a> |</li>
		<li class="gifts"><a href="admin.php?page=angift&tab=settings"
			class="<?php print($activeTab === 'settings' ? 'current' : '');?>">Settings</span>
		</a> |</li>
		<li class="gifts"><a href="admin.php?page=angift&tab=feedback"
			class="<?php print($activeTab === 'feedback' ? 'current' : '');?>">Feedback</span>
		</a></li>
	</ul>

	<div class="clear"></div>
	<?php
switch ($activeTab) {
    case ('manual'):
        require_once (__DIR__ . '/manual.php');
        break;
    case ('settings'):
        angift_settings_form_handler();
        break;
    case ('actions'):
        angift_actions_form_handler();
        break;
    case ('shure_get_gift'):
        angift_actions_shure_get_gift();
        break;
    case ('get_gift'):
        angift_actions_get_gift();
        break;
    case ('registration'):
        angift_registration_form_handler();
        break;
    case ('feedback'):
        angift_feedback_form_handler();
        break;
}
?>
</div>