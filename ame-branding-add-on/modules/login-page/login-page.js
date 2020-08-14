/*global wsAmeLoginPageData, wsAmeLodash */
/**
 * @property {Object} wsAmeLoginPageData.defaultCodeEditorSettings
 * @property {Object} wp.codeEditor
 */

jQuery(function ($) {
	'use strict';
	var _ = wsAmeLodash;

	// noinspection JSUnresolvedFunction It's part of the WP API, script handle: "wp-color-picker".
	$('.ame-color-picker').wpColorPicker();

	var $backgroundPositions = $('#ame-background-position-selector').find('input:radio');
	$backgroundPositions.change(function () {
		var $this = $(this);
		if ($this.is(':checked')) {
			$backgroundPositions.closest('td').removeClass('ame-selected-background-position');
			$this.closest('td').addClass('ame-selected-background-position');

			$('#ame-background-position-output').text($this.val());
		}
	});

	//Enable syntax highlighting for HTML/CSS/JS fields.
	if (wp.hasOwnProperty('codeEditor') && wp.codeEditor.initialize && wsAmeLoginPageData.defaultCodeEditorSettings) {
		var defaultEditorSettings = wsAmeLoginPageData.defaultCodeEditorSettings;
		wp.codeEditor.initialize('ame-custom-login-message', defaultEditorSettings);

		wp.codeEditor.initialize(
			'ame-login-custom-css',
			_.merge({}, defaultEditorSettings, {
				'codemirror': {
					'mode': 'css',
					'lint': true,
					'autoCloseBrackets': true,
					'matchBrackets': true
				}
			})
		);

		wp.codeEditor.initialize(
			'ame-login-custom-js',
			_.merge({}, defaultEditorSettings, {
				'codemirror': {
					'mode': 'javascript',
					'lint': true,
					'autoCloseBrackets': true,
					'matchBrackets': true
				}
			})
		);
	}

	//Save the login logo width and height if the user has chosen an external image.
	var $form = $('#ame-login-page-settings');
	$form.on('submit', function () {
		var $urlField = $form.find('input[name="logo_image_external_url"]'),
			$attachmentField = $form.find('input[name="logo_image_attachment_id"]'),
			attachmentId = parseInt($attachmentField.val(), 10);

		if ($urlField.val() && !attachmentId) {
			var $externalImage = $urlField.closest('.ame-image-selector').find('.ame-image-preview img').first();
			if ($externalImage.length > 0) {
				$form.find('input[name="logo_image_width"]').val($externalImage.get(0).naturalWidth);
				$form.find('input[name="logo_image_height"]').val($externalImage.get(0).naturalHeight);
			}
		}
	});
});

