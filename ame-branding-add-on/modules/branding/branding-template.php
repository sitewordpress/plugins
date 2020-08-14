<?php
require_once AME_BRANDING_ADD_ON_DIR . '/includes/ameFormBuilder.php';

/**
 * @var string $moduleTabUrl
 * @var array $settings
 */

$updateVisibilityOptions = array(
	'default'     => 'Show to all users',
	'update_core' => 'Show to users who can install updates',
	'hidden'      => 'Hide from all users',
);

if ( is_multisite() ) {
	//In Multisite, only users who have the "update_core" capability can see the core update
	//notification, so the "update_core" and "default" options are equivalent.
	$updateVisibilityOptions['default'] = 'Show to users who can install updates';
	unset($updateVisibilityOptions['update_core']);
}

//This is how the wp_mail() function chooses the default "From" address in WP 4.9.4.
$hostname = strtolower($_SERVER['SERVER_NAME']);
if ( substr($hostname, 0, 4) === 'www.' ) {
	$hostname = substr($hostname, 4);
}
$defaultFromEmail = 'wordpress@' . $hostname;

$form = ameFormBuilder::form(
	'ame_save_branding_settings',
	add_query_arg(array('noheader' => '1'), $moduleTabUrl),
	$settings
);

$form
	->section('Toolbar')
		->field('WordPress logo', 'is_toolbar_wp_logo_hidden')
			->checkbox('Remove the WordPress logo from the Toolbar')
		->field('Custom logo', 'custom_toolbar_logo_attachment_id')
			->imageSelector(array(
				'description' => 'Recommended size: 16x16 px.',
				'externalUrlField' => 'custom_toolbar_logo_external_url',
			))
		->field('Logo link URL', 'custom_toolbar_logo_link')
			->textBox(array('type' => 'url', 'class' => 'code'))
		->field('Custom "Howdy" text', 'custom_howdy_text')
			->textBox(array(
				'description' => 'Enter the greeting to use instead of "howdy". Example: <code>Welcome</code>. '
					. ' Alternatively, you can enter a sentence like <code>Hi, %s!</code>. '
					. ' The <code>%s</code> will be replaced with the user\'s display name.'
			))
	->section('Admin Footer')
		->field('Visibility', 'is_admin_footer_hidden')
			->checkbox('Hide the entire admin footer')
		->field('Footer text', 'admin_footer_text')
			->editor()
		->field('WordPress version', 'is_footer_version_hidden')
			->checkbox('Remove WordPress version information from the admin footer')

	->section('Admin Settings')
		->field('Admin page titles', 'admin_page_title_template')
			->textBox(array('class' => 'large-text code'))
			->html('<p>Available tags:</p>')
			->html('<ul class="ame-page-title-tags">
				<li><button class="button button-secondary" type="button" title="Current admin page or menu item">%page%</button></li>
				<li><button class="button button-secondary" type="button" 
					title="The &quot;Site Title&quot; set in &quot;Settings -&gt; General&quot;">%site_title%</button></li>
			</ul>')
			->html('<p>Examples:</p><ul class="ame-page-title-examples">
				<li><code>' . '%page% &lsaquo; %site_title% &#8212; Company Name' . '</code></li>
				<li><code>' . htmlspecialchars('Company Name &rsaquo; %page%') . '</code></li>
				<li><code>' . htmlspecialchars('%site_title% - %page%') . '</code></li>
			</ul>')
		->field('WordPress update notifications', 'core_update_notification_visibility')
			->radioGroup($updateVisibilityOptions)
			->defaultValue('default')
		->field('WordPress version in Dashboard', 'is_right_now_version_hidden')
			->checkbox('Remove the WordPress version from the "At a Glance" widget')

	->section(
		'WordPress Emails',
		array('description' => 'You can change the "From" header for all emails sent by WordPress. 
			These settings will also affect any plugins that don\'t specify their own "From" header.')
	)
		->field('Sender name', 'wp_mail_from_name')
			->textBox(array(
				'description' => 'The default is <code>WordPress</code>.'
			))
		->field('"From" email', 'wp_mail_from_email')
			->textBox(array(
				'type' => 'email',
				'description' => 'The default is <code>' . htmlspecialchars($defaultFromEmail) . '</code>.'
			));

//(Custom) sender name
//(Custom) "From" email

$form->output();

	/*
	 * Text Replacement
	 *  Replace "WordPress" in HTML with something else
	 *  Probably using output buffers or gettext filters. That means you can only replace translated/-able text.
	 */