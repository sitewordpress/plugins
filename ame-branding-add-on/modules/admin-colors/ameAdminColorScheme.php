<?php

class ameAdminColorScheme {
	private $colors = array();
	private $hash = null;

	public function __construct($colors = array()) {
		reset($colors);
		$firstIndex = key($colors);
		if ( is_int($firstIndex) ) {
			$colors = self::colorListToAssocArray($colors);
		}
		$this->colors = array_intersect_key($colors, self::getAvailableOptions());
	}

	public function getColors() {
		return $this->colors;
	}

	public static function getAvailableOptions() {
		return array(
			'base-color'         => 'Base',
			'text-color'         => 'Text',
			'highlight-color'    => 'Highlight',
			'icon-color'         => 'Icon',
			'notification-color' => 'Notification',

			//General UI
			'body-background'    => 'Page background',

			'link'       => 'Link',
			'link-focus' => 'Link hover',

			'button-color'    => 'Button',
			'form-checked'    => 'Check mark (✓)',

			//Admin menu & Toolbar
			'menu-text'       => 'Menu text',
			'menu-icon'       => 'Menu icon',
			'menu-background' => 'Menu background',

			'menu-highlight-text'       => 'Menu highlight text',
			'menu-highlight-icon'       => 'Menu highlight icon',
			'menu-highlight-background' => 'Menu highlight background',

			'menu-current-text'       => 'Menu current text',
			'menu-current-icon'       => 'Menu current icon',
			'menu-current-background' => 'Menu current background',

			'menu-submenu-text'           => 'Submenu text',
			'menu-submenu-background'     => 'Submenu background',
			'menu-submenu-background-alt' => 'Submenu background (alt.)',

			'menu-submenu-focus-text'   => 'Submenu highlight text',
			'menu-submenu-current-text' => 'Submenu current text',

			'menu-bubble-text'               => 'Bubble text',
			'menu-bubble-background'         => 'Bubble background',
			'menu-bubble-current-text'       => 'Bubble current text',
			'menu-bubble-current-background' => 'Bubble current background',

			'menu-collapse-text' => 'Menu collapse button text',
			/*
			 //These three variables appear to be unused, at least in WP 4.9.4.
			'menu-collapse-icon'       => 'Menu collapse button icon',
			'menu-collapse-focus-text' => 'Menu collapse highlight text',
			'menu-collapse-focus-icon' => 'Menu collapse highlight icon',
			//*/

			'adminbar-avatar-frame'     => 'Toolbar avatar border',
			'adminbar-input-background' => 'Toolbar search box background',
		);
	}

	/**
	 * @return array
	 */
	public static function getColorListOrder() {
		return array_keys(self::getAvailableOptions());
	}

	/**
	 * @param array $colorList
	 * @return array
	 */
	private static function colorListToAssocArray($colorList) {
		$colors = array();
		foreach (self::getColorListOrder() as $index => $name) {
			if ( isset($colorList[$index]) && self::isValidHexColor('#' . $colorList[$index]) ) {
				$colors[$name] = '#' . $colorList[$index];
			}
		}
		return $colors;
	}

	/**
	 * Check if the input is a CSS color in hexadecimal format, e.g. #001122.
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function isValidHexColor($value) {
		return is_string($value) && preg_match('@^#[a-f0-9]{3,6}$@', $value);
	}

	public function compileToCss() {
		$scss = new Leafo\ScssPhp\Compiler();
		$scss->setVariables($this->colors);
		$scss->addImportPath($this->getAdminDir() . '/css/colors');
		return $scss->compile('@import "_admin.scss";');
	}

	/**
	 * Compile only the part of the admin color scheme which pertains to the Admin Bar/Toolbar.
	 *
	 * @return string
	 */
	public function compileAdminBarStylesToCss() {
		$adminScssPath = $this->getAdminDir() . '/css/colors/_admin.scss';
		if ( !is_file($adminScssPath) ) {
			return '/* Error: _admin.scss not found. */';
		}

		//Find the Admin Bar section in the SCSS file.
		$sheet = file_get_contents($adminScssPath);
		if ( !preg_match('@^\s??/\*\s+?Admin Bar\s+?\*/\s{0,20}?$@mi', $sheet, $matches, PREG_OFFSET_CAPTURE) ) {
			return '/* Error: "Admin Bar" section not found in the stylesheet. */';
		}
		$startPosition = $matches[0][1];
		$firstHeadingLength = strlen($matches[0][0]);

		//To find the end of admin bar styles, look for the first section that doesn't mention "admin bar".
		$endPosition = null;
		if ( preg_match_all(
			'@^\s??/\*\s+?[^{}*\r\n#]+?\s+?\*/\s{0,20}?$@m',
			$sheet,
			$headings,
			PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
			$startPosition + $firstHeadingLength
		) ) {
			foreach ($headings as $heading) {
				if ( stripos($heading[0][0], 'admin bar') === false ) {
					$endPosition = $heading[0][1];
					break;
				}
			}
		}
		if ( $endPosition === null ) {
			return '/* Error: Could not find the end of the "Admin Bar" section in the stylesheet. */';
		}

		$adminBarStyles = substr($sheet, $startPosition, $endPosition - $startPosition);

		$scss = new Leafo\ScssPhp\Compiler();
		$scss->setVariables($this->colors);
		$scss->addImportPath($this->getAdminDir() . '/css/colors');

		$code = '@import "_variables.scss";' . "\n";
		$code .= $adminBarStyles;

		return $scss->compile($code);
	}

	private function getAdminDir() {
		return ABSPATH . '/wp-admin';
	}

	public function getDemoColors() {
		return array(
			ameUtils::get($this->colors, 'base-color', '#23282d'),
			ameUtils::get($this->colors, 'icon-color', '#23282d'),
			ameUtils::get($this->colors, 'notification-color', '#d54e21'),
			ameUtils::get($this->colors, 'highlight-color', '#0073aa'),
		);
	}

	public function getSvgIconColors() {
		$icons = array(
			'base'  => ameUtils::get($this->colors, 'icon-color', '#23282d'),
			'focus' => ameUtils::get($this->colors, 'menu-highlight-icon', '#fff'),
		);
		$icons['current'] = $icons['focus'];
		return $icons;
	}

	public function getHash() {
		if ( !isset($this->hash) ) {
			$this->hash = substr(md5(build_query($this->colors)), 0, 16);
		}
		return $this->hash;
	}

	public function toArray() {
		return array(
			'colors' => $this->colors,
			'hash'   => $this->getHash(),
		);
	}

	public static function fromArray($properties) {
		$scheme = new self(ameUtils::get($properties, 'colors', array()));
		$scheme->hash = ameUtils::get($properties, 'hash', null);
		return $scheme;
	}
}