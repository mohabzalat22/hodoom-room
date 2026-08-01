<?php

/**
 * Front Page Options
 *
 * @package Aster Storefront
 */

$wp_customize->add_panel(
	'aster_storefront_front_page_options',
	array(
		'title'    => esc_html__( 'Front Page Options', 'aster-storefront' ),
		'priority' => 20,
	)
);

// Banner Section.
require get_template_directory() . '/theme-library/customizer/front-page-options/banner.php';

// Tranding Product Section.
require get_template_directory() . '/theme-library/customizer/front-page-options/trending-product.php';