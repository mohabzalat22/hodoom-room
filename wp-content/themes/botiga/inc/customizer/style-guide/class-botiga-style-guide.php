<?php
/**
 * Botiga Style Guide.
 *
 * Used inside the Customizer.
 *
 * @package Botiga
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Botiga_Style_Guide' ) ) :
	/**
	 * Botiga Style Guide.
	 *
	 * @since 2.4.7
	 */
	class Botiga_Style_Guide {

		/**
		 * Color config.
		 *
		 * @since 2.4.7
		 *
		 * @var array
		 */
		private $color_config = array();

		/**
		 * Typography config.
		 *
		 * @since 2.4.7
		 *
		 * @var array
		 */
		private $typography_config = array();

		/**
		 * Constructor.
		 *
		 * @since 2.4.7
		 */
		public function __construct() {
			add_action( 'customize_register', array( $this, 'initialize_config' ), 1 );
			add_action( 'customize_register', array( $this, 'register_toggle_section' ), 5 );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
		}

		/**
		 * Initialize the Style Guide configuration.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		public function initialize_config() {
			$this->color_config      = $this->get_color_config();
			$this->typography_config = $this->get_typography_config();
		}

		/**
		 * Register the Style Guide toggle section.
		 *
		 * @since 2.4.7
		 *
		 * @param WP_Customize_Manager $wp_customize Customizer manager.
		 *
		 * @return void
		 */
		public function register_toggle_section( $wp_customize ) {
			require_once get_template_directory() . '/inc/customizer/style-guide/control/section-style-guide.php';

			$wp_customize->register_section_type( 'Botiga_Customize_Style_Guide_Toggle' );

			$wp_customize->add_section(
				new Botiga_Customize_Style_Guide_Toggle(
					$wp_customize,
					'botiga_style_guide_toggle',
					array(
						'title'    => esc_html__( 'Open Style Guide', 'botiga' ),
						'priority' => -998,
					)
				)
			);
		}

		/**
		 * Enqueue the Style Guide Customizer control script.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		public function enqueue_control_scripts() {
			wp_enqueue_script(
				'botiga-style-guide-customize-controls',
				get_template_directory_uri() . '/inc/customizer/style-guide/control/customize-controls.js',
				array( 'customize-controls' ),
				BOTIGA_VERSION,
				true
			);
		}

		/**
		 * Enqueue Style Guide assets.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		public function enqueue() {
			wp_enqueue_style(
				'botiga-style-guide',
				get_template_directory_uri() . '/inc/customizer/style-guide/css/style-guide.css',
				array(),
				BOTIGA_VERSION
			);

			wp_enqueue_script(
				'botiga-style-guide',
				get_template_directory_uri() . '/inc/customizer/style-guide/js/style-guide.js',
				array( 'jquery', 'customize-controls' ),
				BOTIGA_VERSION,
				true
			);

			wp_localize_script(
				'botiga-style-guide',
				'botigaStyleGuide',
				array(
					'palettes' => function_exists( 'botiga_global_color_palettes' ) ? botiga_global_color_palettes() : array(),
				)
			);

			wp_add_inline_style( 'botiga-style-guide', $this->get_inline_css() );
		}

		/**
		 * Get inline CSS.
		 *
		 * @since 2.4.7
		 *
		 * @return string
		 */
		private function get_inline_css() {
			$css = '';

			$css .= $this->get_button_css();
			$css .= $this->get_typography_css();

			return $css;
		}

		/**
		 * Get color config.
		 *
		 * @since 2.4.7
		 *
		 * @return array
		 */
		private function get_color_config() {
			$config = array();

			$palette_colors        = $this->get_palette_colors();
			$config['palette']     = array();
			$config['global']      = array();
			$config['headings']    = array();
			$config['form_fields'] = array();

			foreach ( $palette_colors as $index => $color ) {
				$config['palette'][] = array(
					'label'   => sprintf(
						/* translators: %d: color number. */
						esc_html__( 'Color %d', 'botiga' ),
						$index + 1
					),
					'setting' => 'custom_color' . ( $index + 1 ),
					'value'   => $color,
				);
			}

			$config['global'] = array(
				array(
					'label'   => esc_html__( 'Background', 'botiga' ),
					'setting' => 'background_color',
					'control' => 'background_color',
					'value'   => get_theme_mod( 'background_color', '#ffffff' ),
				),
				array(
					'label'   => esc_html__( 'Body text', 'botiga' ),
					'setting' => 'color_body_text',
					'control' => 'color_body_text',
					'value'   => get_theme_mod( 'color_body_text', '#212121' ),
				),
				array(
					'label'   => esc_html__( 'Cards background', 'botiga' ),
					'setting' => 'content_cards_background',
					'control' => 'content_cards_background',
					'value'   => get_theme_mod( 'content_cards_background', '#f2f2f2' ),
				),
				array(
					'label'   => esc_html__( 'Link', 'botiga' ),
					'setting' => 'color_link_default',
					'control' => 'color_link_default',
					'value'   => get_theme_mod( 'color_link_default', '#212121' ),
				),
				array(
					'label'   => esc_html__( 'Link hover', 'botiga' ),
					'setting' => 'color_link_hover',
					'control' => 'color_link_hover',
					'value'   => get_theme_mod( 'color_link_hover', '#757575' ),
				),
			);

			for ( $index = 1; $index <= 6; $index++ ) {
				$config['headings'][] = array(
					'label'   => sprintf(
						/* translators: %d: heading level. */
						esc_html__( 'Heading %d', 'botiga' ),
						$index
					),
					'setting' => 'color_heading_' . $index,
					'control' => 'color_heading_' . $index,
					'value'   => get_theme_mod( 'color_heading_' . $index, '#212121' ),
				);
			}

			$config['form_fields'] = array(
				array(
					'label'   => esc_html__( 'Text', 'botiga' ),
					'setting' => 'color_forms_text',
					'control' => 'color_forms_text',
					'value'   => get_theme_mod( 'color_forms_text', '#212121' ),
				),
				array(
					'label'   => esc_html__( 'Background', 'botiga' ),
					'setting' => 'color_forms_background',
					'control' => 'color_forms_background',
					'value'   => get_theme_mod( 'color_forms_background', '#ffffff' ),
				),
				array(
					'label'   => esc_html__( 'Border', 'botiga' ),
					'setting' => 'color_forms_borders',
					'control' => 'color_forms_borders',
					'value'   => get_theme_mod( 'color_forms_borders', '#212121' ),
				),
				array(
					'label'   => esc_html__( 'Divider', 'botiga' ),
					'setting' => 'color_forms_dividers',
					'control' => 'color_forms_dividers',
					'value'   => get_theme_mod( 'color_forms_dividers', '#dddddd' ),
				),
				array(
					'label'   => esc_html__( 'Placeholder', 'botiga' ),
					'setting' => 'color_forms_placeholder',
					'control' => 'color_forms_placeholder',
					'value'   => get_theme_mod( 'color_forms_placeholder', '#848484' ),
				),
			);

			return $config;
		}

		/**
		 * Get active palette colors.
		 * 
		 * @since 2.4.7
		 *
		 * @return array
		 */
		private function get_palette_colors() {
			$palettes = function_exists( 'botiga_global_color_palettes' ) ? botiga_global_color_palettes() : array();

			if ( get_theme_mod( 'custom_palette_toggle', 0 ) ) {
				$colors = array();

				for ( $index = 1; $index <= 8; $index++ ) {
					$colors[] = get_theme_mod( 'custom_color' . $index, '#212121' );
				}

				return $colors;
			}

			$selected_palette = get_theme_mod( 'color_palettes', 'palette1' );

			if ( empty( $palettes[ $selected_palette ] ) || ! is_array( $palettes[ $selected_palette ] ) ) {
				return array_fill( 0, 8, '#212121' );
			}

			return $palettes[ $selected_palette ];
		}

		/**
		 * Get typography config.
		 *
		 * @since 2.4.7
		 *
		 * @return array
		 */
		private function get_typography_config() {
			$config = array(
				'headings' => array(),
				'body'     => array(),
			);

			$headings_font = $this->get_font_data(
				'botiga_headings_font',
				'botiga_headings_adobe_font',
				'botiga_headings_custom_font',
				'botiga_headings_custom_font_weight',
				700
			);

			$body_font = $this->get_font_data(
				'botiga_body_font',
				'botiga_body_adobe_font',
				'botiga_body_custom_font',
				'botiga_body_custom_font_weight',
				400
			);

			for ( $index = 1; $index <= 6; $index++ ) {
				$config['headings'][ 'h' . $index ] = array(
					'family' => $headings_font['family'],
					'weight' => $headings_font['weight'],
					'size'   => get_theme_mod( 'h' . $index . '_font_size_desktop', $this->get_heading_size_default( $index ) ),
				);
			}

			$config['body'] = array(
				'family' => $body_font['family'],
				'weight' => $body_font['weight'],
				'size'   => get_theme_mod( 'body_font_size_desktop', 16 ),
			);

			return $config;
		}

		/**
		 * Get font data.
		 *
		 * @since 2.4.7
		 *
		 * @param string $google_setting Google font setting.
		 * @param string $adobe_setting Adobe font setting.
		 * @param string $custom_setting Custom font setting.
		 * @param string $custom_weight_setting Custom font weight setting.
		 * @param int    $default_weight Default font weight.
		 *
		 * @return array
		 */
		private function get_font_data( $google_setting, $adobe_setting, $custom_setting, $custom_weight_setting, $default_weight ) {
			$fonts_library = get_theme_mod( 'fonts_library', 'google' );

			if ( 'adobe' === $fonts_library ) {
				return $this->get_adobe_font_data( $adobe_setting, $default_weight );
			}

			if ( 'custom' === $fonts_library ) {
				return $this->get_custom_font_data( $custom_setting, $custom_weight_setting, $default_weight );
			}

			return $this->get_google_font_data( $google_setting, $default_weight );
		}

		/**
		 * Get Google font data.
		 *
		 * @since 2.4.7
		 *
		 * @param string $setting Setting ID.
		 * @param int    $default_weight Default font weight.
		 *
		 * @return array
		 */
		private function get_google_font_data( $setting, $default_weight ) {
			$default = wp_json_encode(
				array(
					'font'          => 'System default',
					'regularweight' => $default_weight,
					'category'      => 'sans-serif',
				)
			);

			$value = json_decode( get_theme_mod( $setting, $default ), true );

			if ( empty( $value ) || ! is_array( $value ) ) {
				return array(
					'family' => 'System default',
					'weight' => $default_weight,
				);
			}

			return array(
				'family' => ! empty( $value['font'] ) ? $value['font'] : 'System default',
				'weight' => ! empty( $value['regularweight'] ) ? $value['regularweight'] : $default_weight,
			);
		}

		/**
		 * Get Adobe font data.
		 *
		 * @since 2.4.7
		 *
		 * @param string $setting Setting ID.
		 * @param int    $default_weight Default font weight.
		 *
		 * @return array
		 */
		private function get_adobe_font_data( $setting, $default_weight ) {
			$value = get_theme_mod( $setting, 'system-default|n4' );

			if ( empty( $value ) ) {
				return array(
					'family' => 'System default',
					'weight' => $default_weight,
				);
			}

			$parts = explode( '|', $value );

			return array(
				'family' => ! empty( $parts[0] ) && 'system-default' !== $parts[0] ? $parts[0] : 'System default',
				'weight' => ! empty( $parts[1] ) ? str_replace( 'n', '', $parts[1] ) . '00' : $default_weight,
			);
		}

		/**
		 * Get custom font data.
		 *
		 * @since 2.4.7
		 *
		 * @param string $font_setting Font setting ID.
		 * @param string $weight_setting Weight setting ID.
		 * @param int    $default_weight Default font weight.
		 *
		 * @return array
		 */
		private function get_custom_font_data( $font_setting, $weight_setting, $default_weight ) {
			$family = get_theme_mod( $font_setting, '' );
			$weight = get_theme_mod( $weight_setting, $default_weight );

			if ( empty( $family ) ) {
				return array(
					'family' => 'System default',
					'weight' => $default_weight,
				);
			}

			return array(
				'family' => $family,
				'weight' => $weight,
			);
		}

		/**
		 * Get heading size default.
		 *
		 * @since 2.4.7
		 *
		 * @param int $level Heading level.
		 *
		 * @return int
		 */
		private function get_heading_size_default( $level ) {
			$defaults = array(
				1 => 64,
				2 => 48,
				3 => 32,
				4 => 24,
				5 => 18,
				6 => 16,
			);

			return ! empty( $defaults[ $level ] ) ? $defaults[ $level ] : 16;
		}

		/**
		 * Get typography CSS.
		 *
		 * @since 2.4.7
		 *
		 * @return string
		 */
		private function get_typography_css() {
			$css = '';

			$headings_font = $this->typography_config['headings']['h1'];
			$body_font     = $this->typography_config['body'];

			if ( 'System default' !== $headings_font['family'] ) {
				$css .= '.botiga-style-guide .botiga-style-guide-heading{font-family:"' . esc_attr( $headings_font['family'] ) . '";font-weight:' . esc_attr( $headings_font['weight'] ) . ';}';
			}

			if ( 'System default' !== $body_font['family'] ) {
				$css .= '.botiga-style-guide .botiga-style-guide-body-text{font-family:"' . esc_attr( $body_font['family'] ) . '";font-weight:' . esc_attr( $body_font['weight'] ) . ';}';
			}

			for ( $index = 1; $index <= 6; $index++ ) {
				$size = get_theme_mod( 'h' . $index . '_font_size_desktop', $this->get_heading_size_default( $index ) );
				$css .= '.botiga-style-guide h' . $index . '.botiga-style-guide-heading{font-size:' . absint( $size ) . 'px;}';
			}

			$css .= '.botiga-style-guide .botiga-style-guide-body-text{font-size:' . absint( get_theme_mod( 'body_font_size_desktop', 16 ) ) . 'px;}';

			return $css;
		}

		/**
		 * Get button CSS.
		 *
		 * @since 2.4.7
		 *
		 * @return string
		 */
		private function get_button_css() {
			$css = '';

			$css .= '.botiga-style-guide .botiga-style-guide-button{';
			$css .= 'background-color:' . esc_attr( get_theme_mod( 'button_background_color', '#212121' ) ) . ';';
			$css .= 'color:' . esc_attr( get_theme_mod( 'button_color', '#ffffff' ) ) . ';';
			$css .= 'border-color:' . esc_attr( get_theme_mod( 'button_border_color', '#212121' ) ) . ';';
			$css .= 'border-width:' . absint( get_theme_mod( 'button_border_width', 0 ) ) . 'px;';
			$css .= 'border-radius:' . absint( get_theme_mod( 'button_border_radius', 0 ) ) . 'px;';
			$css .= 'font-size:' . absint( get_theme_mod( 'button_font_size_desktop', 14 ) ) . 'px;';
			$css .= 'padding:' . absint( get_theme_mod( 'button_top_bottom_padding_desktop', 13 ) ) . 'px ' . absint( get_theme_mod( 'button_left_right_padding_desktop', 24 ) ) . 'px;';
			$css .= 'text-transform:' . esc_attr( get_theme_mod( 'button_text_transform', 'uppercase' ) ) . ';';
			$css .= '}';

			return $css;
		}

		/**
		 * Render template.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		public function template() {
			?>
			<script type="text/template" id="tmpl-botiga-style-guide">
				<div class="botiga-style-guide wp-full-overlay expanded">
					<div class="botiga-style-guide-header">
						<div>
							<h1><?php esc_html_e( 'Style Guide', 'botiga' ); ?></h1>
							<p><?php esc_html_e( 'An interactive visual guide of the theme’s base styles.', 'botiga' ); ?></p>
						</div>
						<button type="button" class="botiga-style-guide-close" aria-label="<?php esc_attr_e( 'Close style guide', 'botiga' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>

					<div class="botiga-style-guide-body">
						<nav class="botiga-style-guide-navigation" aria-label="<?php esc_attr_e( 'Style Guide navigation', 'botiga' ); ?>">
							<a href="#section-site-identity" class="botiga-style-guide-nav-link"><?php esc_html_e( 'Site Identity', 'botiga' ); ?></a>
							<a href="#section-colors" class="botiga-style-guide-nav-link"><?php esc_html_e( 'Colors', 'botiga' ); ?></a>
							<a href="#section-buttons" class="botiga-style-guide-nav-link"><?php esc_html_e( 'Buttons', 'botiga' ); ?></a>
							<a href="#section-typography" class="botiga-style-guide-nav-link"><?php esc_html_e( 'Typography', 'botiga' ); ?></a>
						</nav>

						<div class="botiga-style-guide-content">
							<?php $this->site_identity_section(); ?>
							<?php $this->colors_section(); ?>
							<?php $this->buttons_section(); ?>
							<?php $this->typography_section(); ?>
						</div>
					</div>
				</div>
			</script>
			<?php
		}

		/**
		 * Render site identity section.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		private function site_identity_section() {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo           = $custom_logo_id ? wp_get_attachment_image( $custom_logo_id, 'medium', false, array( 'class' => 'botiga-style-guide-logo' ) ) : '';
			$site_icon_url  = get_site_icon_url( 64 );
			?>
			<section id="section-site-identity" class="botiga-style-guide-section">
				<div class="botiga-style-guide-section-header">
					<h2><?php esc_html_e( 'Site Identity', 'botiga' ); ?></h2>
				</div>

				<div class="botiga-style-guide-section-content">
					<div class="botiga-style-guide-site-identity-grid">
						<a href="#" data-customizer-control="custom_logo" class="botiga-style-guide-card botiga-style-guide-site-identity-card botiga-style-guide-customizer-link">
							<h3><?php esc_html_e( 'Site Title & Logo', 'botiga' ); ?></h3>

							<div class="botiga-style-guide-identity">
								<div class="botiga-style-guide-identity-logo">
									<?php if ( $logo ) : ?>
										<?php echo wp_kses_post( $logo ); ?>
									<?php else : ?>
										<strong><?php bloginfo( 'name' ); ?></strong>
									<?php endif; ?>
								</div>

								<div>
									<div class="botiga-style-guide-site-title"><?php bloginfo( 'name' ); ?></div>
									<div class="botiga-style-guide-site-description"><?php bloginfo( 'description' ); ?></div>
								</div>
							</div>

							<?php $this->render_edit_overlay(); ?>
						</a>

						<a href="#" data-customizer-control="site_icon" class="botiga-style-guide-card botiga-style-guide-site-identity-card botiga-style-guide-customizer-link">
							<h3><?php esc_html_e( 'Site Icon', 'botiga' ); ?></h3>

							<div class="botiga-style-guide-browser-tab-preview">
								<span class="botiga-style-guide-browser-tab-line" aria-hidden="true"></span>

								<div class="botiga-style-guide-browser-tab">
									<?php if ( $site_icon_url ) : ?>
										<img src="<?php echo esc_url( $site_icon_url ); ?>" alt="<?php esc_attr_e( 'Site icon', 'botiga' ); ?>" class="botiga-style-guide-site-icon">
									<?php else : ?>
										<span class="botiga-style-guide-site-icon-placeholder" aria-hidden="true">
											<span class="dashicons dashicons-wordpress-alt"></span>
										</span>
									<?php endif; ?>

									<span class="botiga-style-guide-site-icon-title"><?php bloginfo( 'name' ); ?></span>
								</div>
							</div>

							<?php $this->render_edit_overlay(); ?>
						</a>
					</div>
				</div>
			</section>
			<?php
		}

		/**
		 * Render colors section.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		private function colors_section() {
			?>
			<section id="section-colors" class="botiga-style-guide-section">
				<div class="botiga-style-guide-section-header">
					<h2><?php esc_html_e( 'Colors', 'botiga' ); ?></h2>
				</div>

				<div class="botiga-style-guide-section-content">
					<div class="botiga-style-guide-color-groups">
						<?php $this->render_color_group( esc_html__( 'Brand', 'botiga' ), $this->color_config['palette'], 'color_palettes', 'botiga-style-guide-color-group--brand' ); ?>
						<?php $this->render_color_group( esc_html__( 'General', 'botiga' ), $this->color_config['global'], 'background_color' ); ?>
						<?php $this->render_color_group( esc_html__( 'Headings', 'botiga' ), $this->color_config['headings'], 'color_heading_1' ); ?>
						<?php $this->render_color_group( esc_html__( 'Form Fields', 'botiga' ), $this->color_config['form_fields'], 'color_forms_text' ); ?>
					</div>
				</div>
			</section>
			<?php
		}

		/**
		 * Render color group.
		 *
		 * @since 2.4.7
		 *
		 * @param string $title Group title.
		 * @param array  $colors Colors.
		 * @param string $control_id Focus control ID.
		 * @param string $class_name Additional class name.
		 *
		 * @return void
		 */
		private function render_color_group( $title, $colors, $control_id, $class_name = '' ) {
			if ( empty( $colors ) ) {
				return;
			}

			$classes = 'botiga-style-guide-color-group';

			if ( $class_name ) {
				$classes .= ' ' . sanitize_html_class( $class_name );
			}
			?>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<h3><?php echo esc_html( $title ); ?></h3>

				<div class="botiga-style-guide-colors">
					<?php foreach ( $colors as $color ) : ?>
						<?php $color_control_id = ! empty( $color['control'] ) ? $color['control'] : $control_id; ?>
						<a href="#" data-customizer-section="colors" data-customizer-control="<?php echo esc_attr( $color_control_id ); ?>" class="botiga-style-guide-color botiga-style-guide-customizer-link">
							<span class="botiga-style-guide-color-swatch" data-color-setting="<?php echo esc_attr( $color['setting'] ); ?>" style="background-color: <?php echo esc_attr( $color['value'] ); ?>"></span>
							<span class="botiga-style-guide-color-label"><?php echo esc_html( $color['label'] ); ?></span>
							<span class="botiga-style-guide-color-value"><?php echo esc_html( $color['value'] ); ?></span>
							<?php $this->render_edit_overlay(); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Render buttons section.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		private function buttons_section() {
			?>
			<section id="section-buttons" class="botiga-style-guide-section">
				<div class="botiga-style-guide-section-header">
					<h2><?php esc_html_e( 'Buttons', 'botiga' ); ?></h2>
				</div>

				<div class="botiga-style-guide-section-content">
					<a href="#" data-customizer-section="botiga_section_buttons" class="botiga-style-guide-button-card botiga-style-guide-customizer-link">
						<span class="botiga-style-guide-button"><?php esc_html_e( 'Shop now', 'botiga' ); ?></span>
						<?php $this->render_edit_overlay(); ?>
					</a>
				</div>
			</section>
			<?php
		}

		/**
		 * Render typography section.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		private function typography_section() {
			$heading_samples = array(
				1 => esc_html__( 'Summer arrivals', 'botiga' ),
				2 => esc_html__( 'New this week', 'botiga' ),
				3 => esc_html__( 'Handpicked essentials', 'botiga' ),
				4 => esc_html__( 'Free shipping over $50', 'botiga' ),
				5 => esc_html__( 'Limited edition', 'botiga' ),
				6 => esc_html__( 'Customer favorites', 'botiga' ),
			);
			?>
			<section id="section-typography" class="botiga-style-guide-section">
				<div class="botiga-style-guide-section-header">
					<h2><?php esc_html_e( 'Typography', 'botiga' ); ?></h2>
				</div>

				<div class="botiga-style-guide-section-content">
					<div class="botiga-style-guide-typography">
						<div class="botiga-style-guide-typography-column">
							<h3><?php esc_html_e( 'Headings', 'botiga' ); ?></h3>

							<?php for ( $index = 1; $index <= 6; $index++ ) : ?>
								<?php $tag = 'h' . $index; ?>
								<a href="#" data-customizer-section="botiga_section_typography_headings" class="botiga-style-guide-typography-item botiga-style-guide-customizer-link">
									<?php $this->render_typography_data( 'headings', $tag ); ?>
									<<?php echo esc_attr( $tag ); ?> class="botiga-style-guide-heading">
										<?php echo esc_html( $heading_samples[ $index ] ); ?>
									</<?php echo esc_attr( $tag ); ?>>
									<?php $this->render_edit_overlay(); ?>
								</a>
							<?php endfor; ?>
						</div>

						<div class="botiga-style-guide-typography-column">
							<h3><?php esc_html_e( 'Body Text', 'botiga' ); ?></h3>

							<a href="#" data-customizer-section="botiga_section_typography_body" class="botiga-style-guide-typography-item botiga-style-guide-customizer-link">
								<?php $this->render_typography_data( 'body' ); ?>

								<div class="botiga-style-guide-body-text">
									<p><?php esc_html_e( 'Here’s how your body text will look on your website. You can customize the typography to match your store’s personality, whether you’re going for a clean, modern feel or something warmer and more traditional. The right type sets the tone before a single product is seen.', 'botiga' ); ?></p>

									<p><?php esc_html_e( 'Explore different font families, sizes, weights, and styles to find the combination that fits your brand. As you adjust each setting, you’ll see your storefront take shape into something that feels distinctly yours.', 'botiga' ); ?></p>
								</div>

								<?php $this->render_edit_overlay(); ?>
							</a>
						</div>
					</div>
				</div>
			</section>
			<?php
		}

		/**
		 * Render typography data.
		 *
		 * @since 2.4.7
		 *
		 * @param string $type Typography type.
		 * @param string $tag Heading tag.
		 *
		 * @return void
		 */
		private function render_typography_data( $type, $tag = '' ) {
			if ( 'headings' === $type ) {
				$item = $this->typography_config['headings'][ $tag ];
				?>
				<div class="botiga-style-guide-light-text botiga-style-guide-headings-typography-data" data-heading="<?php echo esc_attr( $tag ); ?>">
					<span class="botiga-style-guide-typography-family"><?php echo esc_html( $item['family'] ); ?></span> /
					<span class="botiga-style-guide-typography-weight"><?php echo esc_html( $item['weight'] ); ?></span> /
					<span class="botiga-style-guide-typography-size"><?php echo esc_html( $item['size'] . 'px' ); ?></span>
				</div>
				<?php
				return;
			}

			$item = $this->typography_config['body'];
			?>
			<div class="botiga-style-guide-light-text botiga-style-guide-body-typography-data">
				<span class="botiga-style-guide-typography-family"><?php echo esc_html( $item['family'] ); ?></span> /
				<span class="botiga-style-guide-typography-weight"><?php echo esc_html( $item['weight'] ); ?></span> /
				<span class="botiga-style-guide-typography-size"><?php echo esc_html( $item['size'] . 'px' ); ?></span>
			</div>
			<?php
		}

		/**
		 * Render edit overlay.
		 *
		 * @since 2.4.7
		 *
		 * @return void
		 */
		private function render_edit_overlay() {
			?>
			<span class="botiga-style-guide-edit-overlay">
				<span class="dashicons dashicons-edit"></span>
			</span>
			<?php
		}
	}

	new Botiga_Style_Guide();
endif;
