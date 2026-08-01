<?php

/**
 * Theme Options
 *
 * @package aster_storefront
 */

$wp_customize->add_panel(
	'aster_storefront_theme_options',
	array(
		'title'    => esc_html__( 'Theme Options', 'aster-storefront' ),
		'priority' => 10,
	)
);

// theme Options.
require get_template_directory() . '/theme-library/customizer/theme-options/theme-options.php';

// Typography Settings.
require get_template_directory() . '/theme-library/customizer/theme-options/typography.php';

// Page Title.
require get_template_directory() . '/theme-library/customizer/theme-options/page-title.php';

// Excerpt.
require get_template_directory() . '/theme-library/customizer/theme-options/excerpt.php';

// Sidebar Position.
require get_template_directory() . '/theme-library/customizer/theme-options/sidebar-position.php';

// Post Options.
require get_template_directory() . '/theme-library/customizer/theme-options/post-options.php';

// Single Post Options.
require get_template_directory() . '/theme-library/customizer/theme-options/single-post-options.php';

// Related Post Options.
require get_template_directory() . '/theme-library/customizer/theme-options/related-post-options.php';

// Footer Options.
require get_template_directory() . '/theme-library/customizer/theme-options/footer-options.php';

// Footer Social Icons.
require get_template_directory() . '/theme-library/customizer/theme-options/footer-social.php';

// 404 page option.
require get_template_directory() . '/theme-library/customizer/theme-options/404page-customize-setting.php';

// WooCommerce setting.
require get_template_directory() . '/theme-library/customizer/theme-options/woocommerce-setting.php';