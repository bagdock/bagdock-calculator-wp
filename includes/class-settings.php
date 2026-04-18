<?php
/**
 * Settings page at Settings > Bagdock Calculator.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Settings {

	public const PAGE_SLUG      = 'bagdock-calculator';
	public const OPTION_GROUP   = 'bagdock_calculator';
	public const SECTION_GENERAL = 'bagdock_calculator_general';
	public const SECTION_ADVANCED = 'bagdock_calculator_advanced';

	/**
	 * @var Bagdock_Calculator_Settings|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( BAGDOCK_CALCULATOR_FILE ),
			array( $this, 'plugin_action_links' )
		);
	}

	public function add_menu(): void {
		add_options_page(
			__( 'Bagdock Calculator', 'bagdock-calculator' ),
			__( 'Bagdock Calculator', 'bagdock-calculator' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Add a "Settings" link to the Plugins list row. Tiny UX win — the
	 * entrypoint being buried under Settings catches first-time users.
	 *
	 * @param array<int,string> $links
	 * @return array<int,string>
	 */
	public function plugin_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'bagdock-calculator' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function register_settings(): void {
		$fields = array(
			Bagdock_Calculator_Options::KEY_EMBED_KEY => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_embed_key' ),
				'default'           => '',
				'section'           => self::SECTION_GENERAL,
				'label'             => __( 'Embed key', 'bagdock-calculator' ),
				'render'            => 'render_field_embed_key',
			),
			Bagdock_Calculator_Options::KEY_STOREFRONT_URL => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url_field' ),
				'default'           => '',
				'section'           => self::SECTION_GENERAL,
				'label'             => __( 'Storefront URL', 'bagdock-calculator' ),
				'render'            => 'render_field_storefront',
			),
			Bagdock_Calculator_Options::KEY_DEFAULT_FACILITY => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_facility' ),
				'default'           => '',
				'section'           => self::SECTION_GENERAL,
				'label'             => __( 'Default facility ID', 'bagdock-calculator' ),
				'render'            => 'render_field_facility',
			),
			Bagdock_Calculator_Options::KEY_REGION => array(
				'type'              => 'string',
				'sanitize_callback' => array( 'Bagdock_Calculator_Options', 'sanitize_region' ),
				'default'           => '',
				'section'           => self::SECTION_GENERAL,
				'label'             => __( 'Region override', 'bagdock-calculator' ),
				'render'            => 'render_field_region',
			),
			Bagdock_Calculator_Options::KEY_PRESET => array(
				'type'              => 'string',
				'sanitize_callback' => array( 'Bagdock_Calculator_Options', 'sanitize_preset' ),
				'default'           => 'home-goods',
				'section'           => self::SECTION_GENERAL,
				'label'             => __( 'Default preset', 'bagdock-calculator' ),
				'render'            => 'render_field_preset',
			),
			Bagdock_Calculator_Options::KEY_CDN_BUNDLE => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url_field' ),
				'default'           => BAGDOCK_CALCULATOR_DEFAULT_CDN,
				'section'           => self::SECTION_ADVANCED,
				'label'             => __( 'CDN bundle URL', 'bagdock-calculator' ),
				'render'            => 'render_field_cdn_bundle',
			),
			Bagdock_Calculator_Options::KEY_CDN_STYLESHEET => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url_field' ),
				'default'           => BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET,
				'section'           => self::SECTION_ADVANCED,
				'label'             => __( 'CDN stylesheet URL', 'bagdock-calculator' ),
				'render'            => 'render_field_cdn_stylesheet',
			),
		);

		add_settings_section(
			self::SECTION_GENERAL,
			__( 'General', 'bagdock-calculator' ),
			function () {
				echo '<p>' . esc_html__( 'Paste your Bagdock embed key and choose defaults that apply everywhere the shortcode or block renders. Any attribute can be overridden per placement.', 'bagdock-calculator' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_section(
			self::SECTION_ADVANCED,
			__( 'Advanced', 'bagdock-calculator' ),
			function () {
				echo '<p>' . esc_html__( 'Override the CDN URLs only if you are pinning a specific SDK version or self-hosting the bundle for air-gapped sites.', 'bagdock-calculator' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		foreach ( $fields as $key => $field ) {
			register_setting(
				self::OPTION_GROUP,
				$key,
				array(
					'type'              => $field['type'],
					'sanitize_callback' => $field['sanitize_callback'],
					'default'           => $field['default'],
				)
			);

			add_settings_field(
				$key,
				$field['label'],
				array( $this, $field['render'] ),
				self::PAGE_SLUG,
				$field['section'],
				array( 'label_for' => $key )
			);
		}
	}

	public function sanitize_embed_key( string $input ): string {
		$trimmed = trim( $input );
		if ( '' !== $trimmed && ! Bagdock_Calculator_Options::is_valid_embed_key( $trimmed ) ) {
			add_settings_error(
				Bagdock_Calculator_Options::KEY_EMBED_KEY,
				'invalid_embed_key',
				__( 'Embed key must look like ek_live_… or ek_test_… with the calculator:read scope.', 'bagdock-calculator' )
			);
			// Preserve the current value rather than clearing it — losing
			// a valid key because of a typo in an unrelated field would be
			// the worst possible UX here.
			return (string) get_option( Bagdock_Calculator_Options::KEY_EMBED_KEY, '' );
		}
		return $trimmed;
	}

	public function sanitize_facility( string $input ): string {
		$trimmed = trim( $input );
		if ( '' !== $trimmed && ! Bagdock_Calculator_Options::is_valid_facility_id( $trimmed ) ) {
			add_settings_error(
				Bagdock_Calculator_Options::KEY_DEFAULT_FACILITY,
				'invalid_facility_id',
				__( 'Facility ID must start with fac_ and be at least 20 characters long.', 'bagdock-calculator' )
			);
			return (string) get_option( Bagdock_Calculator_Options::KEY_DEFAULT_FACILITY, '' );
		}
		return $trimmed;
	}

	public function sanitize_url_field( string $input ): string {
		return Bagdock_Calculator_Options::sanitize_url( $input );
	}

	public function render_field_embed_key(): void {
		$value = (string) get_option( Bagdock_Calculator_Options::KEY_EMBED_KEY, '' );
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" autocomplete="off" spellcheck="false" />',
			esc_attr( Bagdock_Calculator_Options::KEY_EMBED_KEY ),
			esc_attr( $value )
		);
		echo '<p class="description">';
		esc_html_e( 'Create an embed key with the calculator:read scope from your Bagdock operator account.', 'bagdock-calculator' );
		echo '</p>';
	}

	public function render_field_storefront(): void {
		$value = (string) get_option( Bagdock_Calculator_Options::KEY_STOREFRONT_URL, '' );
		printf(
			'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="https://example.com/book" />',
			esc_attr( Bagdock_Calculator_Options::KEY_STOREFRONT_URL ),
			esc_attr( $value )
		);
		echo '<p class="description">' . esc_html__( 'Where visitors land after tapping "Apply". The recommended size is appended as ?size=…', 'bagdock-calculator' ) . '</p>';
	}

	public function render_field_facility(): void {
		$value = (string) get_option( Bagdock_Calculator_Options::KEY_DEFAULT_FACILITY, '' );
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="fac_…" autocomplete="off" spellcheck="false" />',
			esc_attr( Bagdock_Calculator_Options::KEY_DEFAULT_FACILITY ),
			esc_attr( $value )
		);
		echo '<p class="description">' . esc_html__( 'Optional. Set this if the site sells a single facility; the calculator will use that facility\'s custom rooms, items, and overrides. Leave empty for operator-wide defaults. Per-page shortcodes can override.', 'bagdock-calculator' ) . '</p>';
	}

	public function render_field_region(): void {
		$value   = (string) get_option( Bagdock_Calculator_Options::KEY_REGION, '' );
		$options = array(
			''      => __( 'Auto-detect from embed key', 'bagdock-calculator' ),
			'uk_ie' => __( 'UK & Ireland', 'bagdock-calculator' ),
			'eu'    => __( 'Europe', 'bagdock-calculator' ),
			'usa'   => __( 'United States', 'bagdock-calculator' ),
		);
		printf( '<select id="%1$s" name="%1$s">', esc_attr( Bagdock_Calculator_Options::KEY_REGION ) );
		foreach ( $options as $opt_value => $opt_label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $opt_label )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Forces the sizing ceiling (units-used metric). Leave on auto-detect unless you know better.', 'bagdock-calculator' ) . '</p>';
	}

	public function render_field_preset(): void {
		$value   = (string) get_option( Bagdock_Calculator_Options::KEY_PRESET, 'home-goods' );
		$options = array(
			'home-goods' => __( 'Home goods', 'bagdock-calculator' ),
			'vehicle'    => __( 'Vehicle', 'bagdock-calculator' ),
			'business'   => __( 'Business', 'bagdock-calculator' ),
			'wine'       => __( 'Wine', 'bagdock-calculator' ),
		);
		printf( '<select id="%1$s" name="%1$s">', esc_attr( Bagdock_Calculator_Options::KEY_PRESET ) );
		foreach ( $options as $opt_value => $opt_label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $opt_label )
			);
		}
		echo '</select>';
	}

	public function render_field_cdn_bundle(): void {
		$value = (string) get_option( Bagdock_Calculator_Options::KEY_CDN_BUNDLE, BAGDOCK_CALCULATOR_DEFAULT_CDN );
		printf(
			'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text code" />',
			esc_attr( Bagdock_Calculator_Options::KEY_CDN_BUNDLE ),
			esc_attr( $value )
		);
		echo '<p class="description">' . sprintf(
			/* translators: %s: default CDN URL */
			esc_html__( 'Default: %s', 'bagdock-calculator' ),
			'<code>' . esc_html( BAGDOCK_CALCULATOR_DEFAULT_CDN ) . '</code>'
		) . '</p>';
	}

	public function render_field_cdn_stylesheet(): void {
		$value = (string) get_option( Bagdock_Calculator_Options::KEY_CDN_STYLESHEET, BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET );
		printf(
			'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text code" />',
			esc_attr( Bagdock_Calculator_Options::KEY_CDN_STYLESHEET ),
			esc_attr( $value )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage this plugin.', 'bagdock-calculator' ) );
		}
		?>
		<div class="wrap bagdock-calculator-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Paste the embed key from your Bagdock operator account to brand the calculator with your theme, pricing, and facility catalogue.', 'bagdock-calculator' ); ?>
			</p>

			<form method="post" action="options.php" novalidate="novalidate">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Embedding', 'bagdock-calculator' ); ?></h2>
			<p><?php esc_html_e( 'Once your embed key is saved, drop any of these snippets into a page, post, sidebar, or footer:', 'bagdock-calculator' ); ?></p>

			<h3><?php esc_html_e( 'Shortcode', 'bagdock-calculator' ); ?></h3>
			<pre class="bagdock-calculator-snippet"><code>[bagdock_calculator]</code></pre>
			<p class="description"><?php esc_html_e( 'All attributes are optional. Override per placement when needed:', 'bagdock-calculator' ); ?></p>
			<pre class="bagdock-calculator-snippet"><code>[bagdock_calculator facility_id="fac_01H…" preset="vehicle" button_label="Find my unit"]</code></pre>

			<h3><?php esc_html_e( 'Gutenberg block', 'bagdock-calculator' ); ?></h3>
			<p><?php esc_html_e( 'Search for "Bagdock Calculator" in the block inserter.', 'bagdock-calculator' ); ?></p>

			<h3><?php esc_html_e( 'Elementor widget', 'bagdock-calculator' ); ?></h3>
			<p><?php esc_html_e( 'Drag the "Bagdock Calculator" widget into any section. Configuration appears in the left-hand panel.', 'bagdock-calculator' ); ?></p>
		</div>
		<?php
	}
}
