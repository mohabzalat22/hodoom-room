<?php

/**
 * Settings - Wizard
 *
 * @package Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ATSS_Onboarding_Wizard' ) ) {
	return;
}

if ( ! get_option( 'atss_wizard_state' ) && ! get_option( 'atss_current_starter' ) ) {
	return;
}

?>

<!-- Site Wizard Section -->
<div class="botiga-dashboard-card-section bt-mt-20px" style="border-top:1px solid #d9d9d9;padding-top: 25px;">
	<div class="botiga-dashboard-module-card">
		<div class="botiga-dashboard-module-card-header bt-align-items-center">
			<div class="botiga-dashboard-module-card-header-info">
				<h2 class="bt-m-0 bt-mb-10px"><?php echo esc_html__( 'Site Wizard', 'botiga' ); ?></h2>
				<p class="bt-text-color-grey"><?php esc_html_e( 'Relaunch the site wizard to set up a fresh template for your site, allowing you to easily apply new color and font presets, along with additional features.', 'botiga' ); ?></p>
			</div>
			<div class="botiga-dashboard-module-card-header-actions bt-pt-0">
				<div class="botiga-dashboard-box-link">
				    <a href="<?php echo esc_url( add_query_arg( 'page', 'atss-onboarding-wizard', admin_url( 'admin.php' ) ) ); ?>" class="botiga-dashboard-link botiga-dashboard-link-primary button button-primary" style="text-decoration: none;">
						<?php echo esc_html__( 'Relaunch Wizard', 'botiga' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Site Wizard Section -->