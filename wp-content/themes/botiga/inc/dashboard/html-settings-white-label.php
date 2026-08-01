<?php

/**
 * Settings - White Label
 *
 * Visible to every user (Lite included), positioned after Version Control.
 *  - Agency-tier sites get functional controls (toggle + fields) wired to the
 *    aThemes White Label plugin's own option.
 *  - Every other tier gets a locked toggle that opens an upgrade modal.
 *
 * The controls read/write the plugin's exact option and keys
 * ("athemes_white_label_settings": awl_agency_name, awl_agency_url,
 * awl_theme_name, awl_theme_description, awl_theme_screenshot,
 * activate_white_label) so values stay shared with the plugin in both
 * directions — nothing here reimplements the white-labeling behaviour.
 *
 * Eligibility and saving are provided by Botiga Pro
 * (botiga_white_label_is_available() and the AJAX save handler); this template
 * falls back to a locked state when Pro isn't present.
 *
 * @package Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$botiga_wl_available = function_exists( 'botiga_white_label_is_available' ) ? botiga_white_label_is_available() : false;

if ( function_exists( 'botiga_white_label_get_settings' ) ) {
	$botiga_wl_settings = botiga_white_label_get_settings();
} else {
	$botiga_wl_settings = get_option( 'athemes_white_label_settings', array() );
}
$botiga_wl_settings = is_array( $botiga_wl_settings ) ? $botiga_wl_settings : array();

$botiga_wl_plugin    = function_exists( 'botiga_white_label_plugin_available' ) ? botiga_white_label_plugin_available() : false;
$botiga_wl_is_active = $botiga_wl_available && ! empty( $botiga_wl_settings['activate_white_label'] );

// Fields, in the order defined by the issue. Keys match the plugin exactly.
$botiga_wl_fields = array(
	'awl_agency_name'       => array(
		'label' => esc_html__( 'Your Agency Name', 'botiga' ),
		'type'  => 'text',
	),
	'awl_agency_url'        => array(
		'label' => esc_html__( 'Your Agency URL', 'botiga' ),
		'type'  => 'url',
	),
	'awl_theme_name'        => array(
		'label' => esc_html__( 'Theme Name', 'botiga' ),
		'type'  => 'text',
	),
	'awl_theme_description' => array(
		'label' => esc_html__( 'Theme Description', 'botiga' ),
		'type'  => 'textarea',
	),
	'awl_theme_screenshot'  => array(
		'label' => esc_html__( 'Theme Screenshot URL', 'botiga' ),
		'type'  => 'url',
	),
);

$botiga_wl_upgrade_url = function_exists( 'botiga_upgrade_link' )
	? botiga_upgrade_link( 'theme_dashboard', 'Settings > White Label Upsell', 'agency' )
	: 'https://athemes.com/botiga-upgrade/';

$botiga_wl_license_url = add_query_arg(
	array(
		'page'        => 'botiga-dashboard',
		'tab'         => 'settings',
		'current_tab' => 'general',
	),
	admin_url( 'admin.php' )
);

// Direct URL back to this White Label sub-tab — useful once White Label hides
// the Botiga admin menu.
$botiga_wl_settings_url = add_query_arg(
	array(
		'page'        => 'botiga-dashboard',
		'tab'         => 'settings',
		'current_tab' => 'white-label',
	),
	admin_url( 'admin.php' )
);

?>

<div class="botiga-dashboard-card botiga-dashboard-white-label<?php echo $botiga_wl_available ? '' : ' botiga-dashboard-white-label-locked'; ?>">
	<div class="botiga-dashboard-card-body">

		<div class="botiga-dashboard-white-label-header">
			<h2 class="bt-m-0 bt-mb-10px">
				<?php echo esc_html__( 'White Label', 'botiga' ); ?>
			</h2>
		</div>

		<?php if ( $botiga_wl_available && ! $botiga_wl_plugin ) : ?>
			<div class="botiga-dashboard-white-label-notice">
				<?php echo esc_html__( 'These settings are applied by Botiga Pro. Keep Botiga Pro active to apply them across your site.', 'botiga' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $botiga_wl_available ) : ?>
			<div class="botiga-dashboard-white-label-notice" id="botiga-wl-bookmark"<?php echo $botiga_wl_is_active ? '' : ' style="display:none;"'; ?>>
				<strong><?php echo esc_html__( 'How do I change the White Label settings after activating it?', 'botiga' ); ?></strong><br>
				<?php echo esc_html__( 'If you need to alter the settings for the White Label after you have already activated it, simply append this to your website’s address:', 'botiga' ); ?><br>
				<a href="<?php echo esc_url( $botiga_wl_settings_url ); ?>"><?php echo esc_html( $botiga_wl_settings_url ); ?></a>
			</div>
		<?php endif; ?>

		<div class="botiga-dashboard-white-label-fields">

			<div class="botiga-dashboard-white-label-toggle">
				<?php if ( $botiga_wl_available ) : ?>
					<label class="botiga-dashboard-white-label-switch" for="botiga_wl_activate">
						<input type="checkbox" id="botiga_wl_activate" name="activate_white_label" value="1" <?php checked( $botiga_wl_is_active ); ?>>
						<span class="botiga-dashboard-white-label-switch-track"></span>
					</label>
				<?php else : ?>
					<label class="botiga-dashboard-white-label-switch" data-wl-locked="1" role="button" tabindex="0" aria-haspopup="dialog">
						<input type="checkbox" disabled>
						<span class="botiga-dashboard-white-label-switch-track"></span>
					</label>
				<?php endif; ?>
				<div class="botiga-dashboard-white-label-toggle-text">
					<strong><?php echo esc_html__( 'Enable White Label', 'botiga' ); ?></strong>
					<span class="bt-text-color-grey"><?php echo esc_html__( 'Replace all Botiga branding and links with your own.', 'botiga' ); ?></span>
				</div>
			</div>

			<?php
			foreach ( $botiga_wl_fields as $botiga_wl_key => $botiga_wl_field ) :
				$botiga_wl_raw      = isset( $botiga_wl_settings[ $botiga_wl_key ] ) ? $botiga_wl_settings[ $botiga_wl_key ] : '';
				$botiga_wl_disabled = ( $botiga_wl_available && $botiga_wl_is_active ) ? '' : 'disabled';
				?>
				<div class="botiga-dashboard-white-label-option">
					<label for="botiga_wl_<?php echo esc_attr( $botiga_wl_key ); ?>"><?php echo esc_html( $botiga_wl_field['label'] ); ?></label>
					<?php if ( 'textarea' === $botiga_wl_field['type'] ) : ?>
						<textarea
							id="botiga_wl_<?php echo esc_attr( $botiga_wl_key ); ?>"
							name="<?php echo esc_attr( $botiga_wl_key ); ?>"
							class="botiga-dashboard-white-label-field"
							rows="3"
							<?php echo esc_attr( $botiga_wl_disabled ); ?>
						><?php echo esc_textarea( $botiga_wl_raw ); ?></textarea>
					<?php else : ?>
						<?php $botiga_wl_value = ( 'url' === $botiga_wl_field['type'] ) ? esc_url( $botiga_wl_raw ) : esc_attr( $botiga_wl_raw ); ?>
						<input
							type="<?php echo esc_attr( $botiga_wl_field['type'] ); ?>"
							id="botiga_wl_<?php echo esc_attr( $botiga_wl_key ); ?>"
							name="<?php echo esc_attr( $botiga_wl_key ); ?>"
							class="botiga-dashboard-white-label-field"
							value="<?php echo $botiga_wl_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>"
							<?php echo esc_attr( $botiga_wl_disabled ); ?>
						>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php
			/**
			 * Allow Botiga Pro (or other extensions) to render additional White
			 * Label settings fields — e.g. the per-plugin "hide" toggles for the
			 * Botiga Pro plugin row. If Pro is inactive, nothing is hooked here and
			 * only the theme's own fields show.
			 *
			 * @param array $botiga_wl_settings Current saved settings.
			 * @param bool  $botiga_wl_disabled Whether fields should render disabled
			 *                                  (White Label not available or not on).
			 */
			do_action( 'botiga_white_label_settings_fields', $botiga_wl_settings, ! ( $botiga_wl_available && $botiga_wl_is_active ) );
			?>

			<?php if ( $botiga_wl_available ) : ?>
				<div class="botiga-dashboard-white-label-actions">
					<button
						type="button"
						id="botiga-white-label-save"
						class="button button-primary"
						data-default-label="<?php echo esc_attr__( 'Save Changes', 'botiga' ); ?>"
					>
						<?php echo esc_html__( 'Save Changes', 'botiga' ); ?>
					</button>
					<span class="botiga-dashboard-white-label-feedback" role="status" aria-live="polite"></span>
				</div>
			<?php endif; ?>

		</div>

	</div>
</div>

<?php if ( ! $botiga_wl_available ) : ?>
	<div id="botiga-wl-modal" class="botiga-wl-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="botiga-wl-modal-title">
		<div class="botiga-wl-modal-overlay"></div>
		<div class="botiga-wl-modal-dialog">
			<button type="button" class="botiga-wl-modal-close" data-wl-close aria-label="<?php echo esc_attr__( 'Close', 'botiga' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" aria-hidden="true">
					<path d="m7.397 5.686 3.85-3.814a.604.604 0 0 0 0-.82L10.356.16a.604.604 0 0 0-.82 0L5.722 4.01 1.872.16a.604.604 0 0 0-.82 0l-.892.892a.604.604 0 0 0 0 .82l3.85 3.814-3.85 3.85a.604.604 0 0 0 0 .82l.892.891a.604.604 0 0 0 .82 0l3.85-3.85 3.814 3.85a.604.604 0 0 0 .82 0l.891-.891a.604.604 0 0 0 0-.82l-3.85-3.85Z" fill="#A7AAAD"/>
				</svg>
			</button>
			<div class="botiga-wl-modal-body">
				<span class="botiga-wl-modal-icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="27" height="33" fill="none">
						<path d="M7.496 14.993v-4.498a6.009 6.009 0 0 1 5.998-5.997 6.009 6.009 0 0 1 5.997 5.997v4.498H7.496Zm19.491 2.249a2.25 2.25 0 0 0-2.249-2.249h-.75v-4.498C23.989 4.732 19.258 0 13.495 0S2.999 4.732 2.999 10.495v4.498h-.75A2.25 2.25 0 0 0 0 17.242v13.494a2.25 2.25 0 0 0 2.249 2.248h22.49a2.25 2.25 0 0 0 2.248-2.248V17.242Z" fill="#A7AAAD"/>
					</svg>
				</span>
				<h3 id="botiga-wl-modal-title" class="botiga-wl-modal-title">
					<?php echo esc_html__( 'This White Label is in Agency Plan', 'botiga' ); ?>
				</h3>
				<p class="botiga-wl-modal-text">
					<?php
					printf(
						/* translators: %s: the emphasised plan name, "AGENCY PLAN". */
						esc_html__( 'We\'re sorry, this White Label is not available on your plan. Please upgrade to the %s plan to unlock this option.', 'botiga' ),
						'<strong>' . esc_html__( 'AGENCY PLAN', 'botiga' ) . '</strong>'
					);
					?>
				</p>
				<a href="<?php echo esc_url( $botiga_wl_upgrade_url ); ?>" class="button button-primary botiga-wl-modal-cta" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html__( 'Upgrade to Agency', 'botiga' ); ?>
				</a>
			</div>

			<?php
			/*
			 * NOTE: the discount figure below is carried over from the design
			 * mockup. Confirm the promotion for Botiga before shipping, or
			 * remove this .botiga-wl-modal-bonus block if there is none.
			 */
			?>
			<div class="botiga-wl-modal-bonus">
				<span class="botiga-wl-modal-check" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none">
						<path d="M10 20C4.492 20 0 15.508 0 10S4.492 0 10 0s10 4.492 10 10-4.492 10-10 10Zm4.61-14.297c-.43-.312-1.016-.234-1.29.195l-4.687 6.407-2.031-2.032a.968.968 0 0 0-1.329 0c-.351.391-.351.977 0 1.329l2.813 2.812c.195.195.469.313.742.274.274 0 .508-.157.664-.391l5.313-7.305a.9.9 0 0 0-.196-1.289Z" fill="#00A32A"/>
					</svg>
				</span>
				<?php
				printf(
					/* translators: 1: the "Bonus:" label, 2: the discount amount. */
					esc_html__( '%1$s Botiga users get %2$s regular price, automatically applied at checkout.', 'botiga' ),
					'<strong>' . esc_html__( 'Bonus:', 'botiga' ) . '</strong>',
					'<span class="botiga-wl-modal-discount">' . esc_html__( '20% off', 'botiga' ) . '</span>'
				);
				?>
			</div>

			<div class="botiga-wl-modal-footer">
				<?php
				printf(
					/* translators: 1: opening link tag, 2: closing link tag. */
					esc_html__( 'Already purchased? %1$sActivate your license%2$s to unlock this feature.', 'botiga' ),
					'<a href="' . esc_url( $botiga_wl_license_url ) . '">',
					'</a>'
				);
				?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php
// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
