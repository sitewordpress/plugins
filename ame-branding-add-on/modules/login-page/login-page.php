<?php

class ameLoginPageCustomizer extends amePersistentProModule {
	protected $optionName = 'ws_ame_login_page_settings';

	protected $tabTitle = 'Login';
	protected $tabSlug = 'login-page';

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		if ( !$this->isEnabledForRequest() ) {
			return;
		}

		add_action('admin_menu_editor-header', array($this, 'handleFormSubmission'), 10, 2);
		add_action('login_init', array($this, 'registerLoginHooks'));
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_enqueue_media();
		wp_enqueue_script('ame-branding-image-selector');

		$codeEditorSettings = null;
		if ( function_exists('wp_enqueue_code_editor') ) {
			$codeEditorSettings = wp_enqueue_code_editor(array('type' => 'text/html'));
		}

		wp_enqueue_auto_versioned_script(
			'ws-ame-login-page-settings-js',
			plugins_url('modules/login-page/login-page.js', AME_BRANDING_ADD_ON_FILE),
			array('jquery', 'wp-color-picker', 'jquery-qtip', 'ame-lodash', 'ame-branding-image-selector')
		);

		wp_localize_script(
			'ws-ame-login-page-settings-js',
			'wsAmeLoginPageData',
			array(
				'defaultCodeEditorSettings' => $codeEditorSettings,
			)
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();

		wp_enqueue_style('wp-color-picker');

		wp_enqueue_auto_versioned_style(
			'ws-ame-login-page-settings',
			plugins_url('modules/login-page/login-page.css', AME_BRANDING_ADD_ON_FILE)
		);
	}

	public function handleFormSubmission($action, $post = array()) {
		if ( $action === 'ame_save_login_page_settings' ) {
			check_admin_referer($action);

			//Respect the unfiltered_html capability. Usually the administrator can enter arbitrary HTML,
			//but it's possible to turn that off - even for Super Admins.
			$htmlSanitization = null;
			if ( !current_user_can('unfiltered_html') ) {
				$htmlSanitization = array(
					'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				);
			}

			//Validate settings.
			$definitions = array(
				'logo_image_attachment_id' => array(
					'filter'  => FILTER_VALIDATE_INT,
					'options' => array('default' => 0, 'min_range' => 0),
				),
				'logo_image_external_url'  => array(
					'filter'  => FILTER_VALIDATE_URL,
					'options' => array('default' => ''),
				),
				'logo_image_width' => array(
					'filter'  => FILTER_VALIDATE_INT,
					'options' => array('default' => 0, 'min_range' => 0),
				),
				'logo_image_height' => array(
					'filter'  => FILTER_VALIDATE_INT,
					'options' => array('default' => 0, 'min_range' => 0),
				),

				'logo_link_url'            => array(
					'filter'  => FILTER_VALIDATE_URL,
					'options' => array('default' => ''),
				),
				'logo_title_text'          => array(
					'filter' => FILTER_SANITIZE_STRING,
				),

				'page_title'               => array(
					'filter' => FILTER_SANITIZE_STRING,
					'options' => array('default' => ''),
				),

				'page_background_color'               => array(
					'filter'  => FILTER_VALIDATE_REGEXP,
					'options' => array('default' => '', 'regexp' => '/^#?[a-z0-9\-]{1,40}$/'),
				),
				'page_background_image_attachment_id' => array(
					'filter'  => FILTER_VALIDATE_INT,
					'options' => array('default' => 0, 'min_range' => 0),
				),
				'page_background_image_external_url'  => array(
					'filter'  => FILTER_VALIDATE_URL,
					'options' => array('default' => ''),
				),
				'page_background_repeat'              => array(
					'filter'  => FILTER_SANITIZE_STRING,
					'options' => array('default' => ''),
				),
				'page_background_position'            => array(
					'filter'  => FILTER_SANITIZE_STRING,
					'options' => array('default' => ''),
				),
				'page_background_size'                => array(
					'filter'  => FILTER_SANITIZE_STRING,
					'options' => array('default' => ''),
				),

				'login_form_background_color' => array(
					'filter'  => FILTER_VALIDATE_REGEXP,
					'options' => array('default' => '', 'regexp' => '/^#?[a-z0-9\-]{1,40}$/'),
				),
				'custom_login_message'        => $htmlSanitization,

				'register_link_enabled'      => array(
					'filter'  => FILTER_VALIDATE_BOOLEAN,
					'options' => array('default' => true),
				),
				'back_to_link_enabled'       => array(
					'filter'  => FILTER_VALIDATE_BOOLEAN,
					'options' => array('default' => true),
				),
				'lost_password_link_enabled' => array(
					'filter'  => FILTER_VALIDATE_BOOLEAN,
					'options' => array('default' => true),
				),

				'custom_css' => $htmlSanitization,
				'custom_js'  => $htmlSanitization,

				'login_alias' => array(
					'filter'  => FILTER_VALIDATE_REGEXP,
					'options' => array('default' => '', 'regexp' => '/^[a-z0-9_\-]{1,60}$/'),
				),
			);
			$settings = array_intersect_key($post, $definitions);
			$settings = filter_var_array($settings, $definitions, true);

			//Additional validation.
			if ( !in_array($settings['page_background_size'], array('auto', 'cover', 'contain')) ) {
				$settings['page_background_size'] = 'auto';
			}
			if ( !in_array($settings['page_background_repeat'], array('repeat', 'repeat-x', 'repeat-y', 'no-repeat')) ) {
				$settings['page_background_repeat'] = 'repeat';
			}

			if ( isset($settings['page_title']) ) {
				$settings['page_title'] = trim($settings['page_title']);
			}

			//Validate images and cache their URLs.
			$imageOptions = array(
				'logo_image_attachment_id' => array('logo_image_external_url', 'logo_image_width', 'logo_image_height'),
				'page_background_image_attachment_id' => array('page_background_image_external_url'),
			);
			foreach ($imageOptions as $option => $alternativeOptions) {
				if ( empty($settings[$option]) ) {
					continue;
				}
				$imageAttachmentKey = preg_replace('/_id$/', '_details', $option);

				$attachment = wp_get_attachment_image_src($settings[$option], 'full');
				if ( !empty($attachment) && is_array($attachment) ) {
					$settings[$imageAttachmentKey] = array_slice($attachment, 0, 3);
					//Attachments and external URLs are mutually exclusive alternatives.
					foreach ($alternativeOptions as $unusedOption) {
						unset($settings[$unusedOption]);
					}
				} else {
					$settings[$option] = 0;
					$settings[$imageAttachmentKey] = null;
				}
			}

			if ( empty($settings['logo_image_external_url']) ) {
				unset($settings['logo_image_width'], $settings['logo_image_height']);
			}

			$this->settings = $settings;
			$this->saveSettings();

			wp_redirect($this->getTabUrl(array('updated' => 1)));
			exit;
		}
	}

	public function registerLoginHooks() {
		$this->loadSettings();

		$titleFilter = 'login_headertext';
		$wpVersion = isset($GLOBALS['wp_version']) ? $GLOBALS['wp_version'] : '5.2.1';
		if ( version_compare($wpVersion, '5.2', '<') ) {
			//This filter was deprecated in WP 5.2.
			$titleFilter = 'login_headertitle';
		}

		add_filter('login_headerurl', array($this, 'filterLogoLinkUrl'), 200);
		add_filter($titleFilter, array($this, 'filterLogoTitleText'));
		add_filter('login_message', array($this, 'filterFormMessage'));
		add_filter('register', array($this, 'filterRegistrationLink'));
		add_filter('login_title', array($this, 'filterPageTitle'), 10, 1);

		add_action('login_head', array($this, 'printLoginStyles'), 15);

		add_action('login_footer', array($this, 'printCustomJs'));
	}

	public function filterLogoLinkUrl($url) {
		$customUrl = ameUtils::get($this->settings, 'logo_link_url', '');
		if ( ($customUrl !== '') && isset($customUrl) ) {
			return $customUrl;
		}
		return $url;
	}

	public function filterLogoTitleText($title) {
		$customTitle = ameUtils::get($this->settings, 'logo_title_text', '');
		if ( ($customTitle !== '') && isset($customTitle) ) {
			return $customTitle;
		}
		return $title;
	}

	public function filterFormMessage($message) {
		global $action;
		if ( isset($action) && ($action !== 'login') ) {
			return $message;
		}

		$customMessage = trim(ameUtils::get($this->settings, 'custom_login_message', ''));
		if ( !empty($customMessage) && empty($message) ) {
			$message = $customMessage;
		}
		return $message;
	}

	public function filterRegistrationLink($linkHtml) {
		if ( !ameUtils::get($this->settings, 'register_link_enabled', true) ) {
			return '';
		}
		return $linkHtml;
	}

	public function filterPageTitle($fullTitle) {
		$customTitle = ameUtils::get($this->settings, 'page_title', null);
		if ( !is_string($customTitle) || ($customTitle === '') ) {
			return $fullTitle;
		}
		return strip_tags($customTitle);
	}

	public function printLoginStyles() {
		$styles = array();

		//Logo.
		// a) External URL.
		$logoUrl = ameUtils::get($this->settings, 'logo_image_external_url');
		$logoWidth = ameUtils::get($this->settings, 'logo_image_width');
		$logoHeight = ameUtils::get($this->settings, 'logo_image_height');
		// b) Image attachment.
		if ( empty($logoUrl) ) {
			$logoId = ameUtils::get($this->settings, 'logo_image_attachment_id', 0);
			$attachment = ameUtils::get($this->settings, 'logo_image_attachment_details');

			if ( !empty($logoId) ) {
				if ( empty($attachment) ) {
					$attachment = wp_get_attachment_image_src($logoId, 'full');
				}
				if ( !empty($attachment) ) {
					$logoUrl = $attachment[0];
					$logoWidth = $attachment[1];
					$logoHeight = $attachment[2];
				}
			}
		}
		if ( !empty($logoUrl) ) {
			$styles[] = sprintf(
				'.login h1 a {
					background-image: none, url("%1$s");
					width: %2$dpx;
					height: %3$dpx;
					background-size: auto;
					background-repeat: no-repeat;
				}',
				$this->escapeUrlForCss($logoUrl),
				$logoWidth,
				$logoHeight
			);
		}

		//Page background color.
		$backgroundColor = ameUtils::get($this->settings, 'page_background_color', '');
		if ( !empty($backgroundColor) ) {
			$styles[] = sprintf(
				'body { background-color: %1$s; }', htmlspecialchars($backgroundColor)
			);
		}

		//Page background image.
		$backgroundUrl = ameUtils::get($this->settings, 'page_background_image_external_url');
		if ( empty($backgroundUrl) ) {
			$backgroundId = ameUtils::get($this->settings, 'page_background_image_attachment_id', 0);
			$attachment = ameUtils::get($this->settings, 'page_background_image_attachment_details');

			if ( !empty($backgroundId) ) {
				if ( empty($attachment) ) {
					$attachment = wp_get_attachment_image_src($backgroundId, 'full');
				}
				if ( !empty($attachment) && !empty($attachment[0]) ) {
					$backgroundUrl = $attachment[0];
				}
			}
		}
		if ( !empty($backgroundUrl) ) {
			$styles[] = sprintf(
				'body {
					background-image: url("%1$s");
					background-repeat: %2$s;
					background-position: %3$s;
					background-size: %4$s;
				}',
				$this->escapeUrlForCss($backgroundUrl),
				ameUtils::get($this->settings, 'page_background_repeat', 'repeat'),
				ameUtils::get($this->settings, 'page_background_position', 'left top'),
				ameUtils::get($this->settings, 'page_background_size', 'auto')
			);
		}

		//Form background color.
		$formBackgroundColor = ameUtils::get($this->settings, 'login_form_background_color', '');
		if ( !empty($formBackgroundColor) ) {
			$styles[] = sprintf(
				'.login form { background-color: %1$s; }',
				htmlspecialchars($formBackgroundColor)
			);
		}

		//Hide links.
		$registerLinkEnabled = ameUtils::get($this->settings, 'register_link_enabled', true);
		$lostPasswordLinkEnabled = ameUtils::get($this->settings, 'lost_password_link_enabled', true);
		$backToLinkEnabled = ameUtils::get($this->settings, 'back_to_link_enabled', true);

		//If both nav. links are hidden, hide the entire #nav element.
		//Different views have different nav links so we only do this for the "login" action.
		if ( !$registerLinkEnabled && !$lostPasswordLinkEnabled ) {
			$styles[] = 'body.login-action-login #nav {display: none;}';
		}

		//Hide the "lost your password?" link. This is complicated by the fact that the link
		//doesn't have an ID or a class.
		if ( !$lostPasswordLinkEnabled ) {
			$parsedUrl = parse_url(wp_lostpassword_url());
			$styles[] = sprintf(
				'#nav a[href*="%s"] {display: none;}',
				esc_url($parsedUrl['path'] . (empty($parsedUrl['query']) ? '' : ('?' . $parsedUrl['query'])))
			);
		}
		//TODO: In some views, this will leave the " | " link separator visible.

		//Hide the "back to site" link.
		if ( !$backToLinkEnabled ) {
			$styles[] = '#backtoblog {display: none;}';
		}

		//Add user CSS.
		$customCss = trim(ameUtils::get($this->settings, 'custom_css', ''));
		if ( !empty($customCss) ) {
			$styles[] = $customCss;
		}

		if ( empty($styles) ) {
			return;
		}

		printf('<style type="text/css" id="ame-login-page-styles">%s</style>', implode("\n", $styles));
	}

	protected function escapeUrlForCss($url) {
		//This is not quite right, but it should work for most URLs.
		$url = esc_url_raw($url);
		return $url;
	}

	public function printCustomJs() {
		$customJs = ameUtils::get($this->settings, 'custom_js', '');
		if ( empty($customJs) ) {
			return;
		}

		echo '<script type="text/javascript">';
		echo $customJs;
		echo '</script>';
	}

	public function getExportOptionLabel() {
		return 'Login page settings';
	}

	public function exportSettings() {
		$result = parent::exportSettings();

		//Importing/exporting attachments is complicated so let's not do that now.
		$skippedOptions = array(
			'logo_image_attachment_id',
			'logo_image_attachment_details',
			'page_background_image_attachment_id',
			'page_background_image_attachment_details',
		);
		foreach ($skippedOptions as $option) {
			unset($result[$option]);
		}

		return $result;
	}
}