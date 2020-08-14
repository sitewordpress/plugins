<?php
/**
 * @var string $moduleTabUrl
 * @var array $settings
 */

require_once AME_BRANDING_ADD_ON_DIR . '/includes/ameFormBuilder.php';

$saveFormAction = add_query_arg(array('noheader' => '1'), $moduleTabUrl);

function amePrintImageSelector($title, $name, $externalUrlField, $settings) {
	$dummyForm = ameFormBuilder::form('unused', 'unused', $settings);
	$dummyForm->field($title, $name)->imageSelector(array('externalUrlField' => $externalUrlField));
	$dummyForm->outputSingleField($name);
}
?>
<form method="post" action="<?php echo esc_attr($saveFormAction); ?>" id="ame-login-page-settings">
	<input type="hidden" name="action" value="ame_save_login_page_settings">
	<?php wp_nonce_field('ame_save_login_page_settings'); ?>

	<input type="hidden" name="logo_image_width" value="<?php
		echo esc_attr(intval(ameUtils::get($settings, 'logo_image_width', 0)));
	?>">
	<input type="hidden" name="logo_image_height" value="<?php
		echo esc_attr(intval(ameUtils::get($settings, 'logo_image_width', 0)));
	?>">

	<h2>Logo</h2>
	<table class="form-table">
		<tbody>
		<?php amePrintImageSelector(
			'Logo image',
			'logo_image_attachment_id',
			'logo_image_external_url',
			$settings
		); ?>
		<tr>
			<th scope="row"><label for="ame-logo-link-url">Logo link URL</label></th>
			<td>
				<input type="url" class="regular-text code" id="ame-logo-link-url"
				       name="logo_link_url" value="<?php echo esc_attr(ameUtils::get($settings, 'logo_link_url', '')); ?>">
				<p class="description"><?php
					if ( is_multisite() ) {
						echo 'Defaults to the network home URL: <code>' . esc_html(network_home_url()) . '</code>.';
					} else {
						printf('Defaults to <code>%s</code>.', __('https://wordpress.org/'));
					}
					?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ame-logo-title-text">Title text</label></th>
			<td>
				<input type="text" class="regular-text" id="ame-logo-title-text"
				       name="logo_title_text"
				       value="<?php echo esc_attr(ameUtils::get($settings, 'logo_title_text', '')); ?>">
				<p class="description"><?php
					if ( is_multisite() ) {
						echo 'Defaults to the network name';
						if ( function_exists('get_network') ) {
							echo ': "' . esc_html(get_network()->site_name) . '"';
						}
						echo '.';
					} else {
						printf('Defaults to "%s".', esc_html(__('Powered by WordPress')));
					}
					?></p>
			</td>
		</tr>
		</tbody>
	</table>

	<h2>Background</h2>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row"><label for="ame-login-page-background-color">Background color</label></th>
			<td>
				<input type="text" class="ame-color-picker" id="ame-login-page-background-color"
				       name="page_background_color"
				       value="<?php echo esc_attr(ameUtils::get($settings, 'page_background_color', '')); ?>">
			</td>
		</tr>
		<?php amePrintImageSelector(
			'Background image',
			'page_background_image_attachment_id',
			'page_background_image_external_url',
			$settings
		); ?>
		<tr>
			<th scope="row" title="background-repeat">Repeat image</th>
			<td>
				<fieldset>
					<?php
					$options = array(
						'repeat' => 'Repeat all',
						'repeat-x' => 'Repeat horizontally',
						'repeat-y' => 'Repeat vertically',
						'no-repeat' => 'Do not repeat',
					);

					$backgroundRepeat = ameUtils::get($settings, 'page_background_repeat', 'repeat');
					foreach($options as $value => $label) {
						/** @noinspection HtmlUnknownAttribute */
						printf(
							'<label><input type="radio" name="page_background_repeat" value="%s" %s> %s</label><br>',
							esc_attr($value),
							checked($backgroundRepeat, $value, false),
							$label
						);
					}
					?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<th scope="row" title="background-position">Image position</th>
			<td>
				<p><code id="ame-background-position-output"><?php
					$selectedPosition = ameUtils::get($settings, 'page_background_position', 'left top');
					echo esc_html($selectedPosition);
				?></code></p>
				<fieldset>
					<table id="ame-background-position-selector">
						<?php
						foreach (array('top', 'center', 'bottom') as $verticalPos) {
							echo '<tr>';
							foreach (array('left', 'center', 'right') as $horizontalPos) {
								$position = $horizontalPos . ' ' . $verticalPos;
								if ( ($horizontalPos === $verticalPos) && ($horizontalPos === 'center') ) {
									$position = 'center';
								}

								$isSelected = ($position === $selectedPosition);

								/** @noinspection HtmlUnknownAttribute */
								printf(
									'<td class="%2$s"><label><input type="radio" name="page_background_position" '
									. ' value="%1$s" %3$s></label></td>',
									esc_attr($position),
									$isSelected ? 'ame-selected-background-position' : '',
									$isSelected ? ' checked="checked"' : ''
								);
							}
							echo '</tr>';
						}
						?>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<th scope="row" title="background-size">Image size</th>
			<td>
				<fieldset>
					<?php
					$options = array(
						'auto' => 'Automatic',
						'cover' => 'Cover',
						'contain' => 'Contain',
					);

					$backgroundSize = ameUtils::get($settings, 'page_background_size', 'auto');
					foreach($options as $value => $label) {
						/** @noinspection HtmlUnknownAttribute */
						printf(
							'<label><input type="radio" name="page_background_size" value="%s" %s> %s</label><br>',
							esc_attr($value),
							checked($backgroundSize, $value, false),
							$label
						);
					}
					?>
				</fieldset>
			</td>
		</tr>
		</tbody>
	</table>

	<h2>Login Form</h2>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="ame-login-form-background-color">Background color</label></th>
			<td>
				<input type="text" class="ame-color-picker" id="ame-login-form-background-color"
				       name="login_form_background_color"
				       value="<?php echo esc_attr(ameUtils::get($settings, 'login_form_background_color', '')); ?>">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ame-custom-login-message">Custom message</label></th>
			<td>
			<textarea name="custom_login_message" id="ame-custom-login-message"
			          cols="100" rows="5" class="large-text"><?php
				echo esc_textarea(ameUtils::get($settings, 'custom_login_message', ''));
				?></textarea>
				<p class="description">
					Enter a custom message that will be displayed above the login form. HTML is allowed.
					Example: <code><?php echo esc_html('<p class="message">Hello World</p>'); ?></code>
				</p>
			</td>
		</tr>
	</table>

	<h2>
		Links
		<a class="ws_tooltip_trigger"
		   title="Hiding a link does not prevent people from visiting the corresponding page. It's only a cosmetic change.">
			<div class="dashicons dashicons-info"></div>
		</a>
	</h2>
	<?php
	$registrationEnabled = get_option('users_can_register');
	?>
	<table class="form-table">
		<?php if ( $registrationEnabled ): ?>
			<tr>
				<th scope="row"><label for="ame-register-link-enabled">Register</label></th>
				<td>
					<label>
						<input type="checkbox" name="register_link_enabled"
							<?php
							checked($registrationEnabled && ameUtils::get($settings, 'register_link_enabled', true));
							?>>
						Show the registration link
					</label><br>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<th scope="row">Back</th>
			<td>
				<label>
					<input type="checkbox" name="back_to_link_enabled" <?php
					checked(ameUtils::get($settings, 'back_to_link_enabled', true));
					?>>
					Show the "Back to [site name]" link
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Lost Password</th>
			<td>
				<label>
					<input type="checkbox" name="lost_password_link_enabled" <?php
					checked(ameUtils::get($settings, 'lost_password_link_enabled', true))
					?>>
					Show the "Lost your password?" link
				</label>
			</td>
		</tr>
	</table>

	<h2>Custom CSS/JS</h2>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="ame-login-custom-css">Custom CSS</label></th>
			<td>
			<textarea name="custom_css" id="ame-login-custom-css"
			          class="code large-text" cols="100" rows="5"><?php
				echo esc_textarea(ameUtils::get($settings, 'custom_css', ''));
				?></textarea>
				<p class="description">The CSS will be added to the login page header.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ame-login-custom-js">Custom JS</label></th>
			<td>
			<textarea name="custom_js" id="ame-login-custom-js"
			          class="code large-text" cols="100" rows="5"><?php
				echo esc_textarea(ameUtils::get($settings, 'custom_js', ''));
				?></textarea>
				<p class="description">The JavaScript will be added to the login page footer.</p>
			</td>
		</tr>
	</table>

	<h2>Other</h2>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row"><label for="ame-login-page-title">Login page title</label></th>
			<td>
				<input type="text" class="regular-text" id="ame-login-page-title"
				       name="page_title" value="<?php echo esc_attr(ameUtils::get($settings, 'page_title', '')); ?>">
				<p class="description">Leave empty to use the default page title.</p>
			</td>
		</tr>
		</tbody>
	</table>

	<?php if (false): /* Not implemented. */ ?>
		<h2>Login URL Alias</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="ame-login-alias">Slug</label></th>
				<td>
					<input type="text" class="regular-text" id="ame-login-alias"
					       name="login_alias"
					       value="<?php echo esc_attr(ameUtils::get($settings, 'login_alias', '')); ?>">
					<p class="description">
						Enter an alternative URL for the login page.
						For example, setting this to <code>sign-in</code> will make it possible to
						log in at <code><?php echo esc_html(site_url('sign-in', 'login')); ?></code>.
					</p>
				</td>
			</tr>
		</table>
	<?php endif; ?>

	<?php submit_button('Save Changes', 'primary', 'submit', false); ?>
</form>

<!--suppress JSUnresolvedFunction QTip is part of the base plugin. -->
<script type="text/javascript">
	jQuery(function ($) {
		//Set up tooltips
		$('.ws_tooltip_trigger').qtip({
			style: {
				classes: 'qtip qtip-rounded ws_tooltip_node ws_wide_tooltip'
			}
		});
	});
</script>