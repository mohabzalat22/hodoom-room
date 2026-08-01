<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! aster_storefront_has_page_header() ) {
    return;
}

$aster_storefront_classes = array( 'page-header' );
$aster_storefront_style = aster_storefront_page_header_style();

if ( $aster_storefront_style ) {
    $aster_storefront_classes[] = $aster_storefront_style . '-page-header';
}

$aster_storefront_visibility = get_theme_mod( 'aster_storefront_page_header_visibility', 'all-devices' );

if ( 'hide-all-devices' === $aster_storefront_visibility ) {
    // Don't show the header at all
    return;
}

if ( 'hide-tablet' === $aster_storefront_visibility ) {
    $aster_storefront_classes[] = 'hide-on-tablet';
} elseif ( 'hide-mobile' === $aster_storefront_visibility ) {
    $aster_storefront_classes[] = 'hide-on-mobile';
} elseif ( 'hide-tablet-mobile' === $aster_storefront_visibility ) {
    $aster_storefront_classes[] = 'hide-on-tablet-mobile';
}

$aster_storefront_PAGE_TITLE_background_color = get_theme_mod('aster_storefront_page_title_background_color_setting', '');

// Get the toggle switch value
$aster_storefront_background_image_enabled = get_theme_mod('aster_storefront_page_header_style', true);

// Add background image to the header if enabled
$aster_storefront_background_image = get_theme_mod( 'aster_storefront_page_header_background_image', '' );
$aster_storefront_background_height = get_theme_mod( 'aster_storefront_page_header_image_height', '200' );
$aster_storefront_inline_style = '';

if ( $aster_storefront_background_image_enabled && ! empty( $aster_storefront_background_image ) ) {
    $aster_storefront_inline_style .= 'background-image: url(' . esc_url( $aster_storefront_background_image ) . '); ';
    $aster_storefront_inline_style .= 'height: ' . esc_attr( $aster_storefront_background_height ) . 'px; ';
    $aster_storefront_inline_style .= 'background-size: cover; ';
    $aster_storefront_inline_style .= 'background-position: center center; ';

    // Add the unique class if the background image is set
    $aster_storefront_classes[] = 'has-background-image';
}

$aster_storefront_classes = implode( ' ', $aster_storefront_classes );
$aster_storefront_heading = get_theme_mod( 'aster_storefront_page_header_heading_tag', 'h1' );
$aster_storefront_heading = apply_filters( 'aster_storefront_page_header_heading', $aster_storefront_heading );

?>

<?php do_action( 'aster_storefront_before_page_header' ); ?>

<header class="<?php echo esc_attr( $aster_storefront_classes ); ?>" style="<?php echo esc_attr( $aster_storefront_inline_style ); ?> background-color: <?php echo esc_attr($aster_storefront_PAGE_TITLE_background_color); ?>;">

    <?php do_action( 'aster_storefront_before_page_header_inner' ); ?>

    <div class="asterthemes-wrapper page-header-inner">

        <?php if ( aster_storefront_has_page_header() ) : ?>

            <<?php echo esc_attr( $aster_storefront_heading ); ?> class="page-header-title">
                <?php echo wp_kses_post( aster_storefront_get_page_title() ); ?>
            </<?php echo esc_attr( $aster_storefront_heading ); ?>>

        <?php endif; ?>

        <?php if ( function_exists( 'aster_storefront_breadcrumb' ) ) : ?>
            <?php aster_storefront_breadcrumb(); ?>
        <?php endif; ?>

    </div><!-- .page-header-inner -->

    <?php do_action( 'aster_storefront_after_page_header_inner' ); ?>

</header><!-- .page-header -->

<?php do_action( 'aster_storefront_after_page_header' ); ?>