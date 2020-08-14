<?php
require_once 'ameAdminColorScheme.php';

class ameBrandingColors extends amePersistentProModule {
	const PREVIEW_ACTION = 'ame_branding_preview_colors';

	const CUSTOM_SCHEME_ID = 'ame-branding-colors';
	const CUSTOM_SCHEME_NAME = 'Custom (AME)';
	const CUSTOM_SCHEME_ACTION = 'ame_branding_output_custom_colors';
	const CSS_CACHE_OPTION = 'ws_ame_admin_color_scheme_css';

	const ADMIN_BAR_CSS_ACTION = 'ame_branding_output_admin_bar_css';

	protected $tabSlug = 'colors';
	protected $tabTitle = 'Colors';

	protected $settingsFormAction = 'ame-save-branding-colors';
	protected $optionName = 'ws_ame_admin_colors';
	protected $defaultSettings = array(
		'is_color_override_enabled' => true,
	);

	private $cachedColorScheme = null;

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		add_action('admin_init', array($this, 'applyColorScheme'));

		add_action('wp_ajax_' . self::CUSTOM_SCHEME_ACTION, array($this, 'outputCustomColorScheme'));
		add_action('wp_ajax_' . self::PREVIEW_ACTION, array($this, 'outputPreviewColorScheme'));
		add_action('wp_ajax_' . self::ADMIN_BAR_CSS_ACTION, array($this, 'outputAdminBarCss'));

		add_action('wp_enqueue_scripts', array($this, 'enqueueAdminBarStyle'));
	}

	public function handleSettingsForm($post = array()) {
		$this->cachedColorScheme = null;
		$this->settings = array();

		$validColors = array();

		if ( isset($post['colors']) && is_array($post['colors']) ) {
			$inputs = array_intersect_key($post['colors'], ameAdminColorScheme::getAvailableOptions());
			$validColors = array_filter($inputs, 'ameAdminColorScheme::isValidHexColor');
		}

		if ( !empty($validColors) ) {
			$scheme = new ameAdminColorScheme($validColors);
			$this->setScopedOption(
				self::CSS_CACHE_OPTION,
				array(
					'color-scheme' => $scheme->compileToCss(),
					'admin-bar'    => $scheme->compileAdminBarStylesToCss(),
				),
				'no'
			);
			$this->settings['scheme'] = $scheme->toArray();
		} else {
			unset($this->settings['scheme']);
		}

		$booleanSettings = array(
			'is_live_preview_enabled',
			'is_color_override_enabled',
			'are_advanced_options_visible',
		);
		foreach ($booleanSettings as $setting) {
			$this->settings[$setting] = !empty($post[$setting]);
		}

		$this->saveSettings();
		wp_redirect($this->getTabUrl(array('updated' => 1)));
		exit;
	}

	protected function getTemplateVariables($templateName) {
		$variables = parent::getTemplateVariables($templateName);
		$scheme = $this->getCustomColorScheme();
		if ( $scheme ) {
			$variables['settings']['colors'] = $scheme->getColors();
		}
		return $variables;
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_enqueue_auto_versioned_script(
			'ame-branding-color-settings',
			plugins_url('modules/admin-colors/admin-colors.js', AME_BRANDING_ADD_ON_FILE),
			array('jquery', 'ame-lodash')
		);

		wp_localize_script(
			'ame-branding-color-settings',
			'wsAmeBrandingColorData',
			array(
				'previewBaseUrl'       => wp_nonce_url(
					add_query_arg('action', self::PREVIEW_ACTION, self_admin_url('admin-ajax.php')),
					self::PREVIEW_ACTION
				),
				'colorOrderForPreview' => array_flip(ameAdminColorScheme::getColorListOrder()),
			)
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();

		wp_enqueue_auto_versioned_style(
			'admin-branding-color-settings-css',
			plugins_url('modules/admin-colors/admin-colors.css', AME_BRANDING_ADD_ON_FILE)
		);
	}

	public function applyColorScheme() {
		$scheme = $this->getCustomColorScheme();
		if ( $scheme === null ) {
			return;
		}

		//Register the color scheme.
		wp_admin_css_color(
			self::CUSTOM_SCHEME_ID,
			self::CUSTOM_SCHEME_NAME,
			add_query_arg(
				array(
					'action' => self::CUSTOM_SCHEME_ACTION,
					'hash'   => $scheme->getHash(),
				),
				self_admin_url('admin-ajax.php')
			),
			$scheme->getDemoColors(),
			$scheme->getSvgIconColors()
		);

		if ( ameUtils::get($this->settings, 'is_color_override_enabled', false) ) {
			//Remove the "Admin Color Scheme" setting from the "Profile" page.
			remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
			//Force everyone to use the custom color scheme.
			add_filter('get_user_option_admin_color', array($this, 'overrideUserColorScheme'));
		}
	}

	private function getCustomColorScheme() {
		if ( isset($this->cachedColorScheme) ) {
			return $this->cachedColorScheme;
		}
		$this->loadSettings();
		if ( !isset($this->settings['scheme']) ) {
			return null;
		}

		$this->cachedColorScheme = ameAdminColorScheme::fromArray($this->settings['scheme']);
		return $this->cachedColorScheme;
	}

	public function outputCustomColorScheme() {
		$cache = $this->getScopedOption(self::CSS_CACHE_OPTION, null);
		if ( is_array($cache) && isset($cache['color-scheme']) ) {
			$css = $cache['color-scheme'];
		} else if ( is_string($cache) ) {
			//For backwards compatibility. Older versions stored only the color scheme CSS, not admin bar CSS.
			$css = $cache;
		} else {
			echo '/* Error: There is no custom color scheme. */';
			exit;
		}

		$this->sendCssHeaders('+1 year');
		echo $css;
		exit;
	}

	public function overrideUserColorScheme() {
		return self::CUSTOM_SCHEME_ID;
	}

	public function outputPreviewColorScheme() {
		check_ajax_referer(self::PREVIEW_ACTION);
		if ( !$this->menuEditor->current_user_can_edit_menu() ) {
			exit('Error: You don\'t have permission to change admin color settings.');
		}

		if ( !isset($_GET['colors']) || !is_string($_GET['colors']) ) {
			exit('Error: No colors specified');
		}

		$this->sendCssHeaders('+1 hour');

		$scheme = new ameAdminColorScheme(explode('.', $_GET['colors']));
		echo $scheme->compileToCss();
		exit;
	}

	public function enqueueAdminBarStyle() {
		//Only logged-in users can see the admin bar.
		if ( !is_user_logged_in() ) {
			return;
		}

		//Should we use the custom color scheme for this user?
		$this->loadSettings();
		$isEnabled = ameUtils::get($this->settings, 'is_color_override_enabled', false)
			|| (get_user_option('admin_color') === self::CUSTOM_SCHEME_ID);
		if ( !$isEnabled ) {
			return;
		}

		$scheme = $this->getCustomColorScheme();
		if ( $scheme === null ) {
			return;
		}

		wp_enqueue_style(
			self::CUSTOM_SCHEME_ID . '-admin-bar',
			add_query_arg(
				array(
					'action' => self::ADMIN_BAR_CSS_ACTION,
					'hash'   => $scheme->getHash(),
				),
				self_admin_url('admin-ajax.php')
			)
		);
	}

	public function outputAdminBarCss() {
		$cache = $this->getScopedOption(self::CSS_CACHE_OPTION, null);
		if ( !is_array($cache) || !isset($cache['admin-bar']) ) {
			echo '/* Error: There is no cached CSS for the Admin Bar. */';
			exit;
		}

		$this->sendCssHeaders('+1 year');
		echo $cache['admin-bar'];
		exit;
	}

	private function sendCssHeaders($cacheExpires = '+1 year') {
		header('Content-Type: text/css');
		header('X-Content-Type-Options: nosniff');

		if ( $cacheExpires ) {
			//Enable browser caching.
			header('Cache-Control: public');
			header('Pragma: cache');
			header('Expires: ' . gmdate('D, d M Y H:i:s T', strtotime($cacheExpires)), true);
		}
	}
}