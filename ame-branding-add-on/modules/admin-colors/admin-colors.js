/**
 * @namespace wsAmeBrandingColorData
 * @property {string} wsAmeBrandingColorData.previewBaseUrl
 * @property {Array} wsAmeBrandingColorData.colorOrderForPreview
 *
 * @property {object} window.wsAmeLodash
 */
'use strict';

jQuery(function ($) {
	var _ = window.wsAmeLodash;

	var $currentPreview = null,
		$previousPreview = null,
		loadingPreviews = 0,
		$previewButton = $('#ame-color-preview-button'),
		$progressIndicator = $('#ame-color-preview-in-progress'),

		$livePreviewCheckbox = $('#ame-is_live_preview_enabled'),
		isLivePreviewEnabled = $livePreviewCheckbox.prop('checked'),

		$colorInputs = $('.wrap form input.ame-color-picker');

	function updatePreview() {
		var validColorCount = 0, previewColors = [''], stylesheetUrl, lastSetIndex = -1;

		$colorInputs.each(function () {
			var $input = $(this);
			if ($input.val() !== '') {
				var index = wsAmeBrandingColorData.colorOrderForPreview[$input.data('color-variable')];
				previewColors[index] = $input.val().replace('#', '');
				validColorCount++;
				lastSetIndex = Math.max(lastSetIndex, index);
			}
		});

		if (validColorCount < 1) {
			return false;
		}

		$previewButton.prop('disabled', true);
		$progressIndicator.addClass('is-active');

		previewColors = previewColors.slice(0, lastSetIndex + 1);
		stylesheetUrl = wsAmeBrandingColorData.previewBaseUrl
			+ '&colors=' + encodeURIComponent(previewColors.join('.'));

		if ($currentPreview) {
			$previousPreview = $currentPreview;
		}

		$currentPreview = $(
			'<link/>',
			{
				'id': 'ame-color-preview',
				'rel': 'stylesheet',
				'type': 'text/css',
				'href': stylesheetUrl
			}
		);

		var $nodeToRemove = $previousPreview;
		$currentPreview.on('load', function () {
			if ($nodeToRemove) {
				$nodeToRemove.remove();
			}
			loadingPreviews--;
			$previewButton.prop('disabled', loadingPreviews > 0);
			$progressIndicator.toggleClass('is-active', loadingPreviews > 0);
		});

		loadingPreviews++;
		$currentPreview.appendTo('head');

		return true;
	}

	$previewButton.click(function () {
		var hasColors = updatePreview();
		if (!hasColors) {
			alert('Please select at least one color to enable preview');
		}
	});

	var refreshLivePreview = _.throttle(function () {
		if (isLivePreviewEnabled) {
			//When wp-color-picker triggers a "change" or "clear" event, the corresponding input
			//still has the old color value. Let's wait for it to be updated before previewing.
			setTimeout(updatePreview, 30);
		}
	}, 1000, {leading: true, trailing: true});

	$livePreviewCheckbox.on('change', function () {
		isLivePreviewEnabled = $(this).prop('checked');
		refreshLivePreview();

		//When live preview gets turned off, remove the preview style sheet.
		if (!isLivePreviewEnabled && $currentPreview) {
			$currentPreview.remove();
			$currentPreview = null;
		}
	});

	setTimeout(function () { //This must be done *after* color pickers have been initialised.
		$colorInputs.wpColorPicker('option', 'change', refreshLivePreview);
		$colorInputs.wpColorPicker('option', 'clear', refreshLivePreview);
	}, 100);

	var $advancedOptions = $('.wrap .ame-advanced-admin-color'),
		$advancedOptionsCheckbox = $('#ame-are_advanced_options_visible'),
		$advancedOptionsLink = $('#ame-branding-toggle-advanced-fields'),
		areAdvancedOptionsVisible = $advancedOptionsCheckbox.prop('checked');

	function refreshAdvancedOptions() {
		$advancedOptions.toggle(areAdvancedOptionsVisible);
		$advancedOptionsCheckbox.prop('checked', areAdvancedOptionsVisible);
		$advancedOptionsLink.text(
			areAdvancedOptionsVisible ? 'Hide advanced options' : 'Show advanced options'
		)
	}
	$advancedOptionsCheckbox.on('change', function () {
		areAdvancedOptionsVisible = $(this).prop('checked');
		refreshAdvancedOptions();
	});
	$advancedOptionsLink.click(function() {
		areAdvancedOptionsVisible = !areAdvancedOptionsVisible;
		refreshAdvancedOptions();
		return false;
	});
});