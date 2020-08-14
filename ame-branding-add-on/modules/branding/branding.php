<?php

class ameBrandingEditor extends amePersistentProModule {
	protected $optionName = 'ws_ame_general_branding';

	protected $tabTitle = 'Branding';
	protected $tabSlug = 'branding';

	protected $settingsFormAction = 'ame_save_branding_settings';

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		add_action('admin_init', array($this, 'registerAdminHooks'));

		add_action('admin_bar_menu', array($this, 'customizeWordPressToolbar'), 15, 1);
		add_action('admin_print_styles', array($this, 'adjustToolbarLogoStyles'));
		add_action('add_admin_bar_menus', array($this, 'registerGreetingHooks'));

		//Change the "From" name and address used by wp_mail(). Other plugins (e.g. contact forms)
		//also need to be able to change this, so let's use priority 9 instead of the default 10.
		add_filter('wp_mail_from_name', array($this, 'filterFromName'), 9, 1);
		add_filter('wp_mail_from', array($this, 'filterFromEmail'), 9, 1);
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_enqueue_media();
		wp_enqueue_script('ame-branding-image-selector');

		wp_enqueue_auto_versioned_script(
			'ws-ame-general-branding-settings',
			plugins_url('modules/branding/branding.js', AME_BRANDING_ADD_ON_FILE),
			array('jquery', 'ame-branding-image-selector')
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();

		wp_enqueue_auto_versioned_style(
			'ame-branding-tab-styles',
			plugins_url('modules/branding/branding.css', AME_BRANDING_ADD_ON_FILE)
		);
	}

	public function handleSettingsForm($post = array()) {
		$htmlSanitization = null;
		if ( !current_user_can('unfiltered_html') ) {
			$htmlSanitization = array(
				'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			);
		}

		//Validate settings.
		$definitions = array(
			'is_toolbar_wp_logo_hidden'         => array(
				'filter'  => FILTER_VALIDATE_BOOLEAN,
				'options' => array(
					//The "default" option has no effect if the field isn't present, like when
					//the checkbox is unchecked.
					'default' => false,
				),
			),
			'custom_toolbar_logo_attachment_id' => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array('default' => 0, 'min_range' => 0),
			),
			'custom_toolbar_logo_external_url'  => array(
				'filter'  => FILTER_VALIDATE_URL,
				'options' => array('default' => ''),
			),
			'custom_toolbar_logo_link'          => array(
				'filter'  => FILTER_VALIDATE_URL,
				'options' => array('default' => ''),
			),
			'custom_howdy_text'                 => array(
				'filter' => FILTER_SANITIZE_STRING,
			),

			'is_admin_footer_hidden'   => array(
				'filter'  => FILTER_VALIDATE_BOOLEAN,
				'options' => array('default' => false),
			),
			'admin_footer_text'        => $htmlSanitization,
			'is_footer_version_hidden' => array(
				'filter'  => FILTER_VALIDATE_BOOLEAN,
				'options' => array('default' => false),
			),

			'admin_page_title_template'           => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'options' => array('default' => ''),
			),
			'core_update_notification_visibility' => array(
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => array('default' => 'default', 'regexp' => '/^(default|update_core|hidden)$/'),
			),
			'is_right_now_version_hidden'         => array(
				'filter'  => FILTER_VALIDATE_BOOLEAN,
				'options' => array('default' => false),
			),

			'wp_mail_from_name'  => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'options' => array('default' => ''),
			),
			'wp_mail_from_email' => array(
				'filter'  => FILTER_VALIDATE_EMAIL,
				'options' => array('default' => ''),
			),
		);
		$settings = array_intersect_key($post, $definitions);
		$settings = filter_var_array($settings, $definitions, false);

		if ( !empty($settings['custom_toolbar_logo_attachment_id']) ) {
			//Optimization: Store the image URL so that we don't have to make another DB query later.
			$attachment = wp_get_attachment_image_src(intval($settings['custom_toolbar_logo_attachment_id']), 'full');
			if ( !empty($attachment) ) {
				$settings['custom_toolbar_logo_attachment_url'] = $attachment[0];
			} else {
				//That's not a valid attachment.
				$settings['custom_toolbar_logo_attachment_id'] = 0;
				$settings['custom_toolbar_logo_attachment_url'] = null;
			}
		}

		if ( isset($settings['admin_footer_text']) ) {
			$settings['admin_footer_text'] = trim($settings['admin_footer_text']);
		}

		$this->settings = $settings;
		$this->saveSettings();

		wp_redirect($this->getTabUrl(array('updated' => 1)));
		exit;
	}

	private function getOption($name, $default = null) {
		if ( $this->settings === null ) {
			$this->loadSettings();
		}

		if ( array_key_exists($name, $this->settings) ) {
			return $this->settings[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Optimization: Only register admin-related hooks in the admin area, not on every page load.
	 */
	public function registerAdminHooks() {
		$this->loadSettings();
		if ( empty($this->settings) ) {
			return; //Nothing to do.
		}

		//Customize the admin title.
		if ( !empty($this->settings['admin_page_title_template']) ) {
			add_filter('admin_title', array($this, 'filterAdminTitle'), 10, 2);
		}

		if ( !empty($this->settings['is_admin_footer_hidden']) ) {
			add_action('admin_print_styles', array($this, 'hideAdminFooter'));
		}

		//Replace the footer text. Alternatively, we could use the "in_admin_footer" action.
		if ( !empty($this->settings['admin_footer_text']) ) {
			add_filter('admin_footer_text', array($this, 'filterFooterText'));
		}

		//Remove the WordPress version from the footer.
		if ( !empty($this->settings['is_footer_version_hidden']) ) {
			add_filter('update_footer', '__return_empty_string', 990);
		}

		//Hide core update notifications.
		$visibility = $this->getOption('core_update_notification_visibility', 'default');
		if ( ($visibility === 'hidden') || ($visibility === 'update_core') && !current_user_can('update_core') ) {
			remove_action('admin_notices', 'update_nag', 3);
		}

		//Remove the WordPress version from the "At a Glance" widget (previously, it was called "Right Now").
		if ( !empty($this->settings['is_right_now_version_hidden']) ) {
			add_action('admin_print_styles-index.php', array($this, 'hideRightNowVersion'));
		}
	}

	/**
	 * @param WP_Admin_Bar|null $wpAdminBar
	 */
	public function customizeWordPressToolbar($wpAdminBar = null) {
		if ( !$wpAdminBar ) {
			return;
		}

		$settings = $this->loadSettings();
		if ( ameUtils::get($settings, 'is_toolbar_wp_logo_hidden', false) ) {
			//Also remove all logo submenus.
			$itemsToRemove = array('about', 'wporg', 'documentation', 'support-forums', 'feedback', 'wp-logo-external');
			foreach ($itemsToRemove as $id) {
				$wpAdminBar->remove_node($id);
			}
			//Remove the logo itself.
			$wpAdminBar->remove_node('wp-logo');
		}

		$logoUrl = ameUtils::get($settings, 'custom_toolbar_logo_external_url', null);
		if ( empty($logoUrl) ) {
			$logoUrl = ameUtils::get($settings, 'custom_toolbar_logo_attachment_url', null);
		}
		if ( empty($logoUrl) ) {
			return;
		}

		//Simply adding a new item wouldn't work because WordPress automatically hides
		//custom items when the viewport is too narrow (responsive layout). We have to
		//replace the default logo node.
		$escapedUrl = esc_attr($logoUrl);
		$image = '<img src="' . $escapedUrl . '" style="display: inline-block; max-height: 100%;'
			. 'padding: 0; margin: 0; vertical-align: top; position: relative;'
			. 'top: 50%; -ms-transform: translateY(-50%); transform: translateY(-50%);">';

		$wpAdminBar->add_node(array(
			'id'    => 'wp-logo',
			'title' => $image,
			'href'  => ameUtils::get($settings, 'custom_toolbar_logo_link', null),
			'meta'  => array(
				'title' => '',
				'class' => '',
			),
		));
	}

	public function adjustToolbarLogoStyles() {
		echo '<style type="text/css">
		@media screen and ( max-width: 782px ) {
			#wpadminbar #wp-admin-bar-wp-logo a,
			 #wpadminbar #wp-admin-bar-wp-logo .ab-empty-item
			 {width: 52px; text-align: center;}
		}
		</style>';
	}

	/**
	 * Apply custom admin titles.
	 *
	 * @param string $adminTitle The full page title.
	 * @param string $title The original title from the current menu item.
	 * @return string
	 */
	public function filterAdminTitle(/** @noinspection PhpUnusedParameterInspection */
		$adminTitle = '', $title = ''
	) {
		//The template should already be sanitized, but lets do a bit of that again just to be sure.
		$template = strip_tags($this->settings['admin_page_title_template']);

		$adminTitle = str_replace(
			array('%page%', '%site_title%'),
			array($title, get_bloginfo('name')),
			$template
		);
		return $adminTitle;
	}

	/**
	 * Set up the hooks that change the "Howdy, username" greeting in the Toolbar.
	 *
	 * We use the "gettext" filter to replace "Howdy" with a custom greeting.
	 * However, we don't want to keep the hook active for long because filtering
	 * every localized string could hurt performance. Every admin page triggers
	 * this filter hundreds of times.
	 *
	 * To avoid the performance hit, let's add the filter callback just before
	 * WordPress creates the "Howdy..." node and then remove it afterward.
	 */
	public function registerGreetingHooks() {
		//Optimization: Do nothing if there's no custom greeting.
		$customGreeting = $this->getOption('custom_howdy_text', '');
		if ( empty($customGreeting) ) {
			return;
		}

		//In WP 4.9.x the "howdy" node is added by the wp_admin_bar_my_account_item function.
		$accountMenuHookPriority = has_action('admin_bar_menu', 'wp_admin_bar_my_account_item');
		if ( $accountMenuHookPriority !== false ) {
			$addPriority = max($accountMenuHookPriority - 1, 0);
			$removePriority = $accountMenuHookPriority + 1;
		} else {
			//Fallback.
			$addPriority = 0;
			$removePriority = 90;
		}

		add_action('admin_bar_menu', array($this, 'addGreetingFilter'), $addPriority);
		add_action('admin_bar_menu', array($this, 'removeGreetingFilter'), $removePriority);
	}

	public function addGreetingFilter() {
		add_filter('gettext', array($this, 'changeGreeting'), 10, 3);
	}

	public function removeGreetingFilter() {
		remove_filter('gettext', array($this, 'changeGreeting'), 10);
	}

	/**
	 * @param string $translation
	 * @param string $text
	 * @param string $domain
	 * @return string
	 */
	public function changeGreeting($translation, $text = '', $domain = 'default') {
		if ( ($text === 'Howdy, %s') && ($domain === 'default') ) {
			$customGreeting = $this->getOption('custom_howdy_text', '');
			if ( strpos($customGreeting, '%s') === false ) {
				$translation = str_replace('Howdy', $customGreeting, $text);
			} else {
				$translation = $customGreeting;
			}
		}
		return $translation;
	}

	public function hideAdminFooter() {
		echo '<style type="text/css">#wpfooter {display: none !important;}</style>';
	}

	public function filterFooterText(/** @noinspection PhpUnusedParameterInspection */
		$text = '') {
		return do_shortcode($this->settings['admin_footer_text']);
	}

	public function hideRightNowVersion() {
		echo '<style type="text/css">#wp-version-message {display: none;}</style>';
	}

	public function filterFromName($name) {
		$customName = $this->getOption('wp_mail_from_name', '');
		if ( !empty($customName) ) {
			return $customName;
		}
		return $name;
	}

	public function filterFromEmail($email) {
		$customEmail = $this->getOption('wp_mail_from_email', '');
		if ( !empty($customEmail) ) {
			return $customEmail;
		}
		return $email;
	}

	public function exportSettings() {
		$result = parent::exportSettings();

		//Importing/exporting attachments is complicated so let's not do that now.
		$skippedSettings = array('custom_toolbar_logo_attachment_id', 'custom_toolbar_logo_attachment_url');
		foreach ($skippedSettings as $key) {
			unset($result[$key]);
		}
		return $result;
	}

	public function getExportOptionLabel() {
		return 'Branding settings';
	}
}