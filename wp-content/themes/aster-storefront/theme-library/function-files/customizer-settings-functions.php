<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package aster_storefront
 */

function aster_storefront_customize_css() {
    ?>
    <style type="text/css">
        :root {
            --primary-color: <?php echo esc_html( get_theme_mod( 'aster_storefront_primary_color', '#ff0000' ) ); ?>;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'aster_storefront_customize_css' );

function add_custom_script_in_footer() {
    if ( get_theme_mod( 'aster_storefront_enable_sticky_header', false ) ) {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $(window).on('scroll', function() {
                    var scroll = $(window).scrollTop();
                    if (scroll > 0) {
                        $('.navigation-part.hello').addClass('is-sticky');
                    } else {
                        $('.navigation-part.hello').removeClass('is-sticky');
                    }
                });
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'add_custom_script_in_footer' );

function aster_storefront_enqueue_selected_fonts() {
    $aster_storefront_fonts_url = aster_storefront_get_fonts_url();
    if (!empty($aster_storefront_fonts_url)) {
        wp_enqueue_style('aster-storefront-google-fonts', $aster_storefront_fonts_url, array(), null);
    }
}
add_action('wp_enqueue_scripts', 'aster_storefront_enqueue_selected_fonts');

function aster_storefront_layout_customizer_css() {
    $aster_storefront_margin = get_theme_mod('aster_storefront_layout_width_margin', 50);
    ?>
    <style type="text/css">
        body.site-boxed--layout #page  {
            margin: 0 <?php echo esc_attr($aster_storefront_margin); ?>px;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_layout_customizer_css');

function aster_storefront_blog_layout_customizer_css() {
    // Retrieve the blog layout option
    $aster_storefront_blog_layout_option = get_theme_mod('aster_storefront_blog_layout_option_setting', 'Left');

    // Initialize custom CSS variable
    $aster_storefront_custom_css = '';

    // Generate custom CSS based on the layout option
    if ($aster_storefront_blog_layout_option === 'Default') {
        $aster_storefront_custom_css .= '.mag-post-detail { text-align: center; }';
    } elseif ($aster_storefront_blog_layout_option === 'Left') {
        $aster_storefront_custom_css .= '.mag-post-detail { text-align: left; }';
    } elseif ($aster_storefront_blog_layout_option === 'Right') {
        $aster_storefront_custom_css .= '.mag-post-detail { text-align: right; }';
    }

    // Output the combined CSS
    ?>
    <style type="text/css">
        <?php echo wp_kses($aster_storefront_custom_css, array( 'style' => array(), 'text-align' => array() )); ?>
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_blog_layout_customizer_css');

function aster_storefront_sidebar_width_customizer_css() {
    $aster_storefront_sidebar_width = get_theme_mod('aster_storefront_sidebar_width', '30');
    ?>
    <style type="text/css">
        .right-sidebar .asterthemes-wrapper .asterthemes-page {
            grid-template-columns: auto <?php echo esc_attr($aster_storefront_sidebar_width); ?>%;
        }
        .left-sidebar .asterthemes-wrapper .asterthemes-page {
            grid-template-columns: <?php echo esc_attr($aster_storefront_sidebar_width); ?>% auto;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_sidebar_width_customizer_css');

if ( ! function_exists( 'aster_storefront_get_page_title' ) ) {
    function aster_storefront_get_page_title() {
        $aster_storefront_title = '';

        if (is_404()) {
            $aster_storefront_title = esc_html__('Page Not Found', 'aster-storefront');
        } elseif (is_search()) {
            $aster_storefront_title = esc_html__('Search Results for: ', 'aster-storefront') . esc_html(get_search_query());
        } elseif (is_home() && !is_front_page()) {
            $aster_storefront_title = esc_html__('Blogs', 'aster-storefront');
        } elseif (function_exists('is_shop') && is_shop()) {
            $aster_storefront_title = esc_html__('Shop', 'aster-storefront');
        } elseif (is_page()) {
            $aster_storefront_title = get_the_title();
        } elseif (is_single()) {
            $aster_storefront_title = get_the_title();
        } elseif (is_archive()) {
            $aster_storefront_title = get_the_archive_title();
        } else {
            $aster_storefront_title = get_the_archive_title();
        }

        return apply_filters('aster_storefront_page_title', $aster_storefront_title);
    }
}

if ( ! function_exists( 'aster_storefront_has_page_header' ) ) {
    function aster_storefront_has_page_header() {
        // Default to true (display header)
        $aster_storefront_return = true;

        // Custom conditions for disabling the header
        if ('hide-all-devices' === get_theme_mod('aster_storefront_page_header_visibility', 'all-devices')) {
            $aster_storefront_return = false;
        }

        // Apply filters and return
        return apply_filters('aster_storefront_display_page_header', $aster_storefront_return);
    }
}

if ( ! function_exists( 'aster_storefront_page_header_style' ) ) {
    function aster_storefront_page_header_style() {
        $aster_storefront_style = get_theme_mod('aster_storefront_page_header_style', 'default');
        return apply_filters('aster_storefront_page_header_style', $aster_storefront_style);
    }
}

function aster_storefront_page_title_customizer_css() {
    $aster_storefront_layout_option = get_theme_mod('aster_storefront_page_header_layout', 'left');
    ?>
    <style type="text/css">
        .asterthemes-wrapper.page-header-inner {
            <?php if ($aster_storefront_layout_option === 'flex') : ?>
                display: flex;
                justify-content: space-between;
                align-items: center;
            <?php else : ?>
                text-align: <?php echo esc_attr($aster_storefront_layout_option); ?>;
            <?php endif; ?>
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_page_title_customizer_css');

function aster_storefront_pagetitle_height_css() {
    $aster_storefront_height = get_theme_mod('aster_storefront_pagetitle_height', 50);
    ?>
    <style type="text/css">
        header.page-header {
            padding: <?php echo esc_attr($aster_storefront_height); ?>px 0;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_pagetitle_height_css');

function aster_storefront_site_logo_width() {
    $aster_storefront_site_logo_width = get_theme_mod('aster_storefront_site_logo_width', 200);
    ?>
    <style type="text/css">
        .site-logo img,.site-logo a,.site-logo, .site-branding  {
            max-width: <?php echo esc_attr($aster_storefront_site_logo_width); ?>px;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_site_logo_width');

function aster_storefront_menu_font_size_css() {
    $aster_storefront_menu_font_size = get_theme_mod('aster_storefront_menu_font_size', 15);
    ?>
    <style type="text/css">
        .main-navigation a {
            font-size: <?php echo esc_attr($aster_storefront_menu_font_size); ?>px;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_menu_font_size_css');

function aster_storefront_sidebar_widget_font_size_css() {
    $aster_storefront_sidebar_widget_font_size = get_theme_mod('aster_storefront_sidebar_widget_font_size', 24);
    ?>
    <style type="text/css">
        h2.wp-block-heading,aside#secondary .widgettitle,aside#secondary .widget-title{
            font-size: <?php echo esc_attr($aster_storefront_sidebar_widget_font_size); ?>px;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_sidebar_widget_font_size_css');

// Woocommerce Related Products Settings
function aster_storefront_related_product_css() {
    $aster_storefront_related_product_show_hide = get_theme_mod('aster_storefront_related_product_show_hide', true);

    if ( $aster_storefront_related_product_show_hide != true) {
        ?>
        <style type="text/css">
            .related.products {
                display: none;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'aster_storefront_related_product_css');

// Woocommerce Product Sale Position 
function aster_storefront_product_sale_position_customizer_css() {
    $aster_storefront_layout_option = get_theme_mod('aster_storefront_product_sale_position', 'left');
    ?>
    <style type="text/css">
        .woocommerce ul.products li.product .onsale {
            <?php if ($aster_storefront_layout_option === 'left') : ?>
                right: auto;
                left: 0px;
            <?php else : ?>
                left: auto;
                right: 0px;
            <?php endif; ?>
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_product_sale_position_customizer_css'); 

//Copyright Alignment
function aster_storefront_footer_copyright_alignment_css() {
    $aster_storefront_footer_bottom_align = get_theme_mod( 'aster_storefront_footer_bottom_align', 'center' );   
    ?>
    <style type="text/css">
        .site-footer .site-footer-bottom .site-footer-bottom-wrapper {
            justify-content: <?php echo esc_attr( $aster_storefront_footer_bottom_align ); ?> 
        }

        /* Mobile Specific */
        @media screen and (max-width: 575px) {
            .site-footer .site-footer-bottom .site-footer-bottom-wrapper {
                justify-content: center;
                text-align:center;
            }
        }
    </style>
    <?php
}
add_action( 'wp_head', 'aster_storefront_footer_copyright_alignment_css' );


// Preloader Background Color Setting
function aster_storefront_preloader_background_colors_css() {
    $aster_storefront_preloader_background_color_setting = get_theme_mod('aster_storefront_preloader_background_color_setting', '');
        // Only output CSS if a color is set
        if (empty($aster_storefront_preloader_background_color_setting)) {
            return;
        }
    ?>
    <style type="text/css">
        #loader {
            background-color: <?php echo esc_attr($aster_storefront_preloader_background_color_setting); ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_preloader_background_colors_css');

// Preloader Background Image Setting
function aster_storefront_preloader_background_image_css() {
    $aster_storefront_preloader_background_image_setting = get_theme_mod('aster_storefront_preloader_background_image_setting', '');
        // Only output CSS if the background image is set
        if (empty($aster_storefront_preloader_background_image_setting)) {
            return;
        }
    ?>
    <style type="text/css">
        #loader {
            background-image: url('<?php echo esc_url($aster_storefront_preloader_background_image_setting); ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_preloader_background_image_css');

//Footer Heading Alignment
function aster_storefront_footer_heading_alignment_css() {
    $aster_storefront_footer_header_align = get_theme_mod( 'aster_storefront_footer_header_align', 'left' );   
    ?>
    <style type="text/css">
        .site-footer h4, footer#colophon h2.wp-block-heading, footer#colophon .widgettitle, footer#colophon .widget-title {
            text-align: <?php echo esc_attr( $aster_storefront_footer_header_align ); ?> 
        }
    </style>
    <?php
}
add_action( 'wp_head', 'aster_storefront_footer_heading_alignment_css' );

//First Capital Letter
function aster_storefront_show_first_caps() {

	$aster_storefront_first_caps = get_theme_mod('aster_storefront_show_first_caps', false);

	if ( ! $aster_storefront_first_caps ) {
		return; 
	}

	$aster_storefront_css = "
	.mag-post-single .mag-post-detail .mag-post-excerpt p:first-of-type::first-letter {
        font-size: 55px;
        font-weight: 700;
        line-height: 1;
        display: inline-block;
        vertical-align: top;
        margin-right: 6px;
	}
    .mag-post-single .mag-post-detail .mag-post-excerpt p {
        line-height: 5;
	}
	";

	wp_add_inline_style( 'aster-storefront-style', $aster_storefront_css );
}
add_action( 'wp_enqueue_scripts', 'aster_storefront_show_first_caps', 20);

// Topbar Padding Dynamic CSS
function aster_storefront_topbar_padding_css(){

    $padding = get_theme_mod('aster_storefront_topbar_padding', 20);

    $custom_css = "
    .bottom-header-part {
        padding-top: {$padding}px !important;
        padding-bottom: {$padding}px !important;
    }";

    echo '<style>'.$custom_css.'</style>';
}
add_action('wp_head','aster_storefront_topbar_padding_css');

