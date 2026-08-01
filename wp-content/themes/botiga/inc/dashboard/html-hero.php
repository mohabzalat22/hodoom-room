<?php

/**
 *
 * Hero
 * @package Dashboard
 *
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

global $pagenow;

$screen = get_current_screen(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$user   = wp_get_current_user(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$is_products_filter_page = isset( $_GET['tab'] ) && 'products-filter' === $_GET['tab'] ? true : false;
$is_templates_builder_page = isset( $_GET['tab'] ) && 'templates-builder' === $_GET['tab'] ? true : false;

if ( $is_products_filter_page || $is_templates_builder_page ) {
	return;
}

?>

<div class="botiga-dashboard-hero">
	<div class="botiga-dashboard-hero-content">

		<div class="botiga-dashboard-hero-hello">
			<?php esc_html_e('Hello, ', 'botiga'); ?>
			<?php echo esc_html($user->display_name); ?>
			<?php esc_html_e('👋🏻', 'botiga'); ?>
		</div>

		<div class="botiga-dashboard-hero-title">
			<?php echo wp_kses_post($this->settings['hero_title']); ?>
			<?php if ($this->settings['has_pro']) { ?>
				<sup class="botiga-dashboard-hero-badge botiga-dashboard-hero-badge-pro">pro</sup>
			<?php } else { ?>
				<sup class="botiga-dashboard-hero-badge botiga-dashboard-hero-badge-free">free</sup>
			<?php } ?>
		</div>

		<div class="botiga-dashboard-hero-desc">
			<?php echo wp_kses_post($this->settings['hero_desc']); ?>
		</div>

		<div class="botiga-dashboard-hero-actions">
			<?php if ( in_array( $this->get_plugin_status( $this->settings['starter_plugin_path'] ), array( 'inactive', 'not_installed' ), true ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'atss-onboarding-wizard' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-primary botiga-dashboard-plugin-ajax-button botiga-ajax-success-redirect" data-type="install" data-path="<?php echo esc_attr( $this->settings['starter_plugin_path'] ); ?>" data-slug="<?php echo esc_attr( $this->settings['starter_plugin_slug'] ); ?>">
					<?php esc_html_e( 'Let’s Get Started', 'botiga' ); ?>
				</a>
			<?php elseif ( 'active' === $this->get_plugin_status( $this->settings['starter_plugin_path'] ) && empty( get_option( 'atss_current_starter' ) ) && empty( get_option( 'atss_wizard_state' ) ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'atss-onboarding-wizard' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-primary botiga-dashboard-hero-button">
					<?php esc_html_e( 'Let’s Get Started', 'botiga' ); ?>
				</a>
			<?php elseif ( 'active' === $this->get_plugin_status( $this->settings['starter_plugin_path'] ) && ! empty( get_option( 'atss_wizard_state' ) ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'atss-onboarding-wizard' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-primary botiga-dashboard-hero-button">
					<?php esc_html_e( 'Resume wizard', 'botiga' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button button-primary" target="_blank">
					<?php echo esc_html__( 'Start Customizing', 'botiga' ); ?>
				</a>
				
				<?php if ( $this->settings['menu_slug'] !== ( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'page', $this->settings['menu_slug'], admin_url( 'admin.php' ) ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Theme Dashboard', 'botiga' ); ?>
				</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( 'active' !== $this->get_plugin_status( $this->settings['starter_plugin_path'] ) ) : ?>
		<div class="botiga-dashboard-hero-notion">
			<?php esc_html_e( 'Clicking "Let’s Get Started" button will install and activate the Botiga "aThemes Starter Sites" plugin.', 'botiga' ); ?>
		</div>
		<?php endif; ?>

	</div>

	<div class="botiga-dashboard-hero-image">
		<img src="<?php echo esc_url($this->settings['hero_image']); ?>">
	</div>

</div>