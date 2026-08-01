<?php
/**
 * Aster Storefront functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package aster_storefront
 */

$aster_storefront_theme_data = wp_get_theme();
if( ! defined( 'ASTER_STOREFRONT_THEME_VERSION' ) ) define ( 'ASTER_STOREFRONT_THEME_VERSION', $aster_storefront_theme_data->get( 'Version' ) );
if( ! defined( 'ASTER_STOREFRONT_THEME_NAME' ) ) define( 'ASTER_STOREFRONT_THEME_NAME', $aster_storefront_theme_data->get( 'Name' ) );
if( ! defined( 'ASTER_STOREFRONT_THEME_TEXTDOMAIN' ) ) define( 'ASTER_STOREFRONT_THEME_TEXTDOMAIN', $aster_storefront_theme_data->get( 'TextDomain' ) );

/**
 * Include wptt webfont loader.
 */
require_once get_theme_file_path( 'theme-library/function-files/wptt-webfont-loader.php' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/theme-library/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/theme-library/function-files/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/theme-library/function-files/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/theme-library/customizer.php';

/**
 * Google Fonts
 */
require get_template_directory() . '/theme-library/function-files/google-fonts.php';

/**
 * Dynamic CSS
 */
require get_template_directory() . '/theme-library/dynamic-css.php';

/**
 * Breadcrumb
 */
require get_template_directory() . '/theme-library/function-files/class-breadcrumb-trail.php';

/**
 * Getting Started
*/
require get_template_directory() . '/theme-library/getting-started/getting-started.php';

/**
 * Customizer Settings Functions
*/
require get_template_directory() . '/theme-library/function-files/customizer-settings-functions.php';

function aster_storefront_links_setup() {
	if ( ! defined( 'ASTER_STOREFRONT_PREMIUM_PAGE' ) ) {
	define('ASTER_STOREFRONT_PREMIUM_PAGE',__('https://asterthemes.com/products/storefront-wordpress-theme','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_THEME_PAGE' ) ) {
		define('ASTER_STOREFRONT_THEME_PAGE',__('https://asterthemes.com/products/aster-storefront','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_SUPPORT' ) ) {
	define('ASTER_STOREFRONT_SUPPORT',__('https://wordpress.org/support/theme/aster-storefront/','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_REVIEW' ) ) {
	define('ASTER_STOREFRONT_REVIEW',__('https://wordpress.org/support/theme/aster-storefront/reviews/','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_PRO_DEMO' ) ) {
	define('ASTER_STOREFRONT_PRO_DEMO',__('https://demo.asterthemes.com/aster-storefront/','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_THEME_DOCUMENTATION' ) ) {
	define('ASTER_STOREFRONT_THEME_DOCUMENTATION',__('https://demo.asterthemes.com/docs/aster-storefront-free/','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_PREMIUM_DOCUMENTATION' ) ) {
	define('ASTER_STOREFRONT_PREMIUM_DOCUMENTATION',__('https://demo.asterthemes.com/docs/storefront-wordpress-theme-pro/','aster-storefront'));
	}
	if ( ! defined( 'ASTER_STOREFRONT_BUNDLE_PAGE' ) ) {
		define('ASTER_STOREFRONT_BUNDLE_PAGE',__('https://asterthemes.com/products/wp-theme-bundle','aster-storefront'));
	}
}
add_action('after_setup_theme', 'aster_storefront_links_setup');

if ( ! defined( 'ASTER_STOREFRONT_VERSION' ) ) {
	define( 'ASTER_STOREFRONT_VERSION', '1.0.0' );
}

if ( ! function_exists( 'aster_storefront_setup' ) ) :
	
	function aster_storefront_setup() {
		
		load_theme_textdomain( 'aster-storefront', get_template_directory() . '/languages' );

		add_theme_support( 'woocommerce' );

		add_theme_support( 'automatic-feed-links' );
		
		add_theme_support( 'title-tag' );

		add_theme_support( 'post-thumbnails' );

		register_nav_menus(
			array(
				'primary' => esc_html__( 'Primary', 'aster-storefront' ),
			)
		);

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'woocommerce',
			)
		);

		add_theme_support( 'post-formats', array(
			'image',
			'video',
			'gallery',
			'audio', 
		) );

		add_theme_support(
			'custom-background',
			apply_filters(
				'aster_storefront_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		add_theme_support( 'customize-selective-refresh-widgets' );

		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		add_theme_support( 'align-wide' );

		add_theme_support( 'responsive-embeds' );

		/*  Demo Import */
		require get_parent_theme_file_path( '/theme-wizard/config.php' );
	}
endif;
add_action( 'after_setup_theme', 'aster_storefront_setup' );

function aster_storefront_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'aster_storefront_content_width', 640 );
}
add_action( 'after_setup_theme', 'aster_storefront_content_width', 0 );

function aster_storefront_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'aster-storefront' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'aster-storefront' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title"><span>',
			'after_title'   => '</span></h2>',
		)
	);

	$aster_storefront_footer_widget_column = get_theme_mod('aster_storefront_footer_widget_column','4');
	for ($i=1; $i<=$aster_storefront_footer_widget_column; $i++) {
		register_sidebar( array(
			'name' => __( 'Footer  ', 'aster-storefront' )  . $i,
			'id' => 'aster-storefront-footer-widget-' . $i,
			'description' => __( 'The Footer Widget Area', 'aster-storefront' )  . $i,
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<div class="widget-header"><h4 class="widget-title">',
			'after_title' => '</h4></div>',
		) );
	}	
}
add_action( 'widgets_init', 'aster_storefront_widgets_init' );

//Change number of products per page 
add_filter( 'loop_shop_per_page', 'aster_storefront_products_per_page' );
function aster_storefront_products_per_page( $cols ) {
  	return  get_theme_mod( 'aster_storefront_products_per_page',9);
}

// Change number or products per row 
add_filter('loop_shop_columns', 'aster_storefront_loop_columns');
	if (!function_exists('aster_storefront_loop_columns')) {
	function aster_storefront_loop_columns() {
		return get_theme_mod( 'aster_storefront_products_per_row', 3 );
	}
}

/**
 * Enqueue scripts and styles.
 */
function aster_storefront_scripts() {
	// Append .min if SCRIPT_DEBUG is false.
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Slick style.
	wp_enqueue_style( 'aster-storefront-slick-style', get_template_directory_uri() . '/resource/css/slick' . $min . '.css', array(), '1.8.1' );

	// Fontawesome style.
	wp_enqueue_style( 'aster-storefront-fontawesome-style', get_template_directory_uri() . '/resource/css/fontawesome' . $min . '.css', array(), '5.15.4' );

	// Google fonts.
	wp_enqueue_style( 'aster-storefront-google-fonts', wptt_get_webfont_url( aster_storefront_get_fonts_url() ), array(), null );

	// Main style.
	wp_enqueue_style( 'aster-storefront-style', get_template_directory_uri() . '/style.css', array(), ASTER_STOREFRONT_VERSION );

	// RTL style.
	wp_style_add_data('aster-storefront-style', 'rtl', 'replace');

	if (get_theme_mod('aster_storefront_enable_animation', true) == true){
		// Animate CSS
		wp_enqueue_style( 'animate-style', get_template_directory_uri() . '/resource/css/animate.css' );
		// Wow script.
		wp_enqueue_script( 'wow-jquery', get_template_directory_uri() . '/resource/js/wow.js', array('jquery'),'' ,true );
	}

	// Navigation script.
	wp_enqueue_script( 'aster-storefront-navigation-script', get_template_directory_uri() . '/resource/js/navigation' . $min . '.js', array(), ASTER_STOREFRONT_VERSION, true );

	// Slick script.
	wp_enqueue_script( 'aster-storefront-slick-script', get_template_directory_uri() . '/resource/js/slick' . $min . '.js', array( 'jquery' ), '1.8.1', true );

	// Custom script.
	wp_enqueue_script( 'aster-storefront-custom-script', get_template_directory_uri() . '/resource/js/custom' . $min . '.js', array( 'jquery' ), ASTER_STOREFRONT_VERSION, true );


	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'aster_storefront_scripts' );

// Enqueue Customizer live preview script
function aster_storefront_customizer_live_preview() {
    wp_enqueue_script(
        'aster-storefront-customizer',
        get_template_directory_uri() . '/js/customizer.js',
        array('jquery', 'customize-preview'),
        '',
        true
    );
}
add_action('customize_preview_init', 'aster_storefront_customizer_live_preview');

add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );

// Featured Image Dimension
function aster_storefront_blog_post_featured_image_dimension(){
	if(get_theme_mod('aster_storefront_blog_post_featured_image_dimension') == 'custom' ) {
		return true;
	}
	return false;
}