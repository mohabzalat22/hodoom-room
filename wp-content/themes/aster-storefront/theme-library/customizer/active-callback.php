<?php

/**
 * Active Callbacks
 *
 * @package aster_storefront
 */

// Theme Options.
function aster_storefront_is_pagination_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_enable_pagination' )->value() );
}
function aster_storefront_is_breadcrumb_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_enable_breadcrumb' )->value() );
}
function aster_storefront_is_layout_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_website_layout' )->value() );
}
function aster_storefront_is_pagetitle_bcakground_image_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_page_header_style' )->value() );
}
function aster_storefront_is_preloader_style( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_enable_preloader' )->value() );
}

// Header Options.
function aster_storefront_is_topbar_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_Setting( 'aster_storefront_enable_topbar' )->value() );
}

// Banner Slider Section.
function aster_storefront_is_banner_slider_section_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_enable_banner_section' )->value() );
}
function aster_storefront_is_banner_slider_section_and_content_type_post_enabled( $aster_storefront_control ) {
	$content_type = $aster_storefront_control->manager->get_setting( 'aster_storefront_banner_slider_content_type' )->value();
	return ( aster_storefront_is_banner_slider_section_enabled( $aster_storefront_control ) && ( 'post' === $content_type ) );
}
function aster_storefront_is_banner_slider_section_and_content_type_page_enabled( $aster_storefront_control ) {
	$content_type = $aster_storefront_control->manager->get_setting( 'aster_storefront_banner_slider_content_type' )->value();
	return ( aster_storefront_is_banner_slider_section_enabled( $aster_storefront_control ) && ( 'page' === $content_type ) );
}

// Service section.
function aster_storefront_is_service_section_enabled( $aster_storefront_control ) {
	return ( $aster_storefront_control->manager->get_setting( 'aster_storefront_enable_service_section' )->value() );
}
function aster_storefront_is_service_section_and_content_type_post_enabled( $aster_storefront_control ) {
	$content_type = $aster_storefront_control->manager->get_setting( 'aster_storefront_service_content_type' )->value();
	return ( aster_storefront_is_service_section_enabled( $aster_storefront_control ) && ( 'post' === $content_type ) );
}
function aster_storefront_is_service_section_and_content_type_page_enabled( $aster_storefront_control ) {
	$content_type = $aster_storefront_control->manager->get_setting( 'aster_storefront_service_content_type' )->value();
	return ( aster_storefront_is_service_section_enabled( $aster_storefront_control ) && ( 'page' === $content_type ) );
}