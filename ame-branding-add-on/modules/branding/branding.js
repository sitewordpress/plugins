jQuery(function ($) {
	//Currently it's not possible to have the original WP logo and a custom logo at the same time.
	//Let's automatically check the "remove WP logo" box when the user selects a custom logo.
	var $logoSelector = $('.wrap input[name="custom_toolbar_logo_attachment_id"]').closest('.ame-image-selector');
	$logoSelector.on('admin-menu-editor:media-image-selected', function () {
		$('#ame-is_toolbar_wp_logo_hidden').prop('checked', true);
	});

	//Add or remove predefined %tags% to the "admin page titles" field.
	var $adminTitleTemplate = $('#ame-admin_page_title_template'),
		titleTemplateWasFocused = false,
		$tagButtons = $adminTitleTemplate.closest('td').find('li button');

	//Remember if the title field had focus.
	$adminTitleTemplate.on('focus', function (event) {
		titleTemplateWasFocused = true;
		$(this).off(event);
	});

	/**
	 * Show tag buttons as active or inactive depending whether the tags have been used.
	 *
	 * @param [$buttons] Defaults to updating all buttons.
	 */
	function updateTagButtonState($buttons) {
		var template = $adminTitleTemplate.val();
		if (!$buttons) {
			$buttons = $tagButtons;
		}
		$buttons.each(function () {
			var $this = $(this);
			$this.toggleClass('active', template.indexOf($this.text().trim()) >= 0);
		});
	}

	$tagButtons.click(function () {
		var $this = $(this),
			tag = $this.text().trim(),
			template = $adminTitleTemplate.val(),
			selectionStart = $adminTitleTemplate[0].selectionStart,
			selectionEnd = $adminTitleTemplate[0].selectionEnd;

		if (template.indexOf(tag) >= 0) {
			//Remove the tag.
			template = template.replace(tag, '');
			$adminTitleTemplate.val(template);
			updateTagButtonState($this);
			return;
		}

		if (titleTemplateWasFocused) {
			//Insert the tag at the cursor.
			template = template.substr(0, selectionStart) + tag + template.substr(selectionEnd);
		} else {
			//Append the tag to the end.
			template += tag;
		}
		$adminTitleTemplate.val(template);

		//Return focus to the input.
		if (titleTemplateWasFocused && $adminTitleTemplate[0].setSelectionRange) {
			var newSelectionStart = selectionStart + tag.length;
			$adminTitleTemplate[0].setSelectionRange(newSelectionStart, newSelectionStart);
			$adminTitleTemplate.focus();
		}

		updateTagButtonState($this);
	});

	$adminTitleTemplate.on('change', function () {
		updateTagButtonState();
	});

	updateTagButtonState();
});
