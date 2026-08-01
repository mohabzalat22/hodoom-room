<?php

function aster_storefront_sanitize_select( $aster_storefront_input, $aster_storefront_setting ) {
	$aster_storefront_input = sanitize_key( $aster_storefront_input );
	$aster_storefront_choices = $aster_storefront_setting->manager->get_control( $aster_storefront_setting->id )->choices;
	return ( array_key_exists( $aster_storefront_input, $aster_storefront_choices ) ? $aster_storefront_input : $aster_storefront_setting->default );
}

function aster_storefront_sanitize_switch( $aster_storefront_input ) {
	if ( true === $aster_storefront_input ) {
		return true;
	} else {
		return false;
	}
}

function aster_storefront_sanitize_google_fonts( $aster_storefront_input, $aster_storefront_setting ) {
	$aster_storefront_choices = $aster_storefront_setting->manager->get_control( $aster_storefront_setting->id )->choices;
	return ( array_key_exists( $aster_storefront_input, $aster_storefront_choices ) ? $aster_storefront_input : $aster_storefront_setting->default );
}


/**
 * Sanitize HTML input.
 *
 * @param string $aster_storefront_input HTML input to sanitize.
 * @return string Sanitized HTML.
 */
function aster_storefront_sanitize_html( $aster_storefront_input ) {
    return wp_kses_post( $aster_storefront_input );
}

/**
 * Sanitize URL input.
 *
 * @param string $aster_storefront_input URL input to sanitize.
 * @return string Sanitized URL.
 */
function aster_storefront_sanitize_url( $aster_storefront_input ) {
    return esc_url_raw( $aster_storefront_input );
}

// Sanitize Scroll Top Position
function aster_storefront_sanitize_scroll_top_position( $aster_storefront_input ) {
    $aster_storefront_valid_positions = array( 'bottom-right', 'bottom-left', 'bottom-center' );
    if ( in_array( $aster_storefront_input, $aster_storefront_valid_positions ) ) {
        return $aster_storefront_input;
    } else {
        return 'bottom-right'; // Default to bottom-right if invalid value
    }
}

function aster_storefront_sanitize_choices( $aster_storefront_input, $aster_storefront_setting ) {
	global $wp_customize; 
	$aster_storefront_control = $wp_customize->get_control( $aster_storefront_setting->id ); 
	if ( array_key_exists( $aster_storefront_input, $aster_storefront_control->choices ) ) {
		return $aster_storefront_input;
	} else {
		return $aster_storefront_setting->default;
	}
}

function aster_storefront_sanitize_range_value( $aster_storefront_number, $aster_storefront_setting ) {

	// Ensure input is an absolute integer.
	$aster_storefront_number = absint( $aster_storefront_number );

	// Get the input attributes associated with the setting.
	$aster_storefront_atts = $aster_storefront_setting->manager->get_control( $aster_storefront_setting->id )->input_attrs;

	// Get minimum number in the range.
	$aster_storefront_min = ( isset( $aster_storefront_atts['min'] ) ? $aster_storefront_atts['min'] : $aster_storefront_number );

	// Get maximum number in the range.
	$aster_storefront_max = ( isset( $aster_storefront_atts['max'] ) ? $aster_storefront_atts['max'] : $aster_storefront_number );

	// Get step.
	$aster_storefront_step = ( isset( $aster_storefront_atts['step'] ) ? $aster_storefront_atts['step'] : 1 );

	// If the number is within the valid range, return it; otherwise, return the default.
	return ( $aster_storefront_min <= $aster_storefront_number && $aster_storefront_number <= $aster_storefront_max && is_int( $aster_storefront_number / $aster_storefront_step ) ? $aster_storefront_number : $aster_storefront_setting->default );
}