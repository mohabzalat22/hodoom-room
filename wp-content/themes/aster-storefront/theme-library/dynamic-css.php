<?php

/**
 * Dynamic CSS
 */
function aster_storefront_dynamic_css() {
	$aster_storefront_primary_color = get_theme_mod( 'aster_storefront_primary_color', '#ff0000' );

	$aster_storefront_site_title_font       = get_theme_mod( 'aster_storefront_site_title_font', 'Raleway' );
	$aster_storefront_site_description_font = get_theme_mod( 'aster_storefront_site_description_font', 'Raleway' );
	$aster_storefront_header_font           = get_theme_mod( 'aster_storefront_header_font', 'Playfair Display' );
	$aster_storefront_body_font             = get_theme_mod( 'aster_storefront_body_font', 'Jost');

	$aster_storefront_custom_css  = '';
	$aster_storefront_custom_css .= '
    /* Color */
    :root {
        --primary-color: ' . esc_attr( $aster_storefront_primary_color ) . ';
        --header-text-color: ' . esc_attr( '#' . get_header_textcolor() ) . ';
    }
    ';

	$aster_storefront_custom_css .= '
    /* Typograhpy */
    :root {
        --font-heading: "' . esc_attr( $aster_storefront_header_font ) . '", serif;
        --font-main: -apple-system, BlinkMacSystemFont,"' . esc_attr( $aster_storefront_body_font ) . '", "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }

    body,
	button, input, select, optgroup, textarea, p {
        font-family: "' . esc_attr( $aster_storefront_body_font ) . '", serif;
	}

	.site-identity p.site-title, h1.site-title a, h1.site-title, p.site-title a, .site-branding h1.site-title a {
        font-family: "' . esc_attr( $aster_storefront_site_title_font ) . '", serif;
	}
    
	p.site-description {
        font-family: "' . esc_attr( $aster_storefront_site_description_font ) . '", serif !important;
	}
    ';

    // slider and service hideshow css
    $aster_storefront_show_slider = get_theme_mod( 'aster_storefront_enable_banner_section', false);
    $aster_storefront_show_service = get_theme_mod( 'aster_storefront_enable_service_section', false);
        if($aster_storefront_show_slider == false && $aster_storefront_show_service == false){
            $aster_storefront_custom_css .='.home header.site-header .header-main-wrapper .bottom-header-outer-wrapper .bottom-header-part{';
            $aster_storefront_custom_css .='position:static;
        }
        padding: 10px;
        border-bottom: 1px solid #777777;';
            $aster_storefront_custom_css .='}';
    }

   if($aster_storefront_show_slider == false){
        $aster_storefront_custom_css .='.home header.site-header .header-main-wrapper .bottom-header-outer-wrapper .bottom-header-part{';
            $aster_storefront_custom_css .='position:static;background: transparent; padding: 10px; border-bottom: 1px solid #777777;';
        $aster_storefront_custom_css .='}';
        $aster_storefront_custom_css .='#aster_storefront_service_section{';
            $aster_storefront_custom_css .='margin:5px;';
        $aster_storefront_custom_css .='}';
    }

	wp_add_inline_style( 'aster-storefront-style', $aster_storefront_custom_css );
}
add_action( 'wp_enqueue_scripts', 'aster_storefront_dynamic_css', 99 );