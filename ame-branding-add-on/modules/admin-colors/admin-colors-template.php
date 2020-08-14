<?php
require_once AME_BRANDING_ADD_ON_DIR . '/includes/ameFormBuilder.php';
require_once AME_BRANDING_ADD_ON_DIR . '/includes/ameBoxyFormBuilder.php';

/**
 * @var string $moduleTabUrl
 * @var array $settings
 */

$form = ameBoxyFormBuilder::form(
	'ame-save-branding-colors',
	add_query_arg(array('noheader' => '1'), $moduleTabUrl),
	$settings
);

$colorOptions = ameAdminColorScheme::getAvailableOptions();

$basics = array_fill_keys(array(
	'base-color',
	'text-color',
	'highlight-color',
	'icon-color',
	'notification-color',
), true);

foreach ($colorOptions as $key => $label) {
	$isAdvanced = !isset($basics[$key]);
	$form->field(
		$label ? $label : $key,
		'colors[' . $key . ']',
		array(
			'class' => $isAdvanced ? 'ame-advanced-admin-color' : '',
			'attr'     => array(
				'style' => ($isAdvanced && empty($settings['are_advanced_options_visible'])) ? 'display: none;' : '',
			),
		)
	)->colorPicker(array('attr' => array('data-color-variable' => $key)));
}

//$form->fullWidthField('')->html('<a href="#" id="ame-branding-toggle-advanced-fields">Show advanced options</a>');

$form->sidebar(array('mainColumnWidth' => 540));
$form->section();

$form->fullWidthField('action_buttons')
	->submitButton(false)
	->html('<input id="ame-color-preview-button" class="button" value="Preview" type="button" style="float: right;">')
	->html('<span class="spinner" style="float: right;" id="ame-color-preview-in-progress"></span>');

$form->fullWidthField('is_color_override_enabled')
	->checkbox('Apply this color scheme to all users');

$form->fullWidthField('are_advanced_options_visible')
	->checkbox('Show advanced options');

$form->fullWidthField('is_live_preview_enabled')
	->checkbox('Live preview (may be slow)');

$form->output();